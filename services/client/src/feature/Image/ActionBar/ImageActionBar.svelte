<script lang="ts">
  import {
    CollectionPicker,
    CreateCollectionDialog,
  } from '@slink/feature/Collection';
  import { ImageDeletePopover } from '@slink/feature/Image';
  import { Loader } from '@slink/feature/Layout';
  import { CreateTagDialog, TagPicker } from '@slink/feature/Tag';
  import { ButtonGroup, ButtonGroupItem } from '@slink/ui/components';
  import * as DropdownMenu from '@slink/ui/components/dropdown-menu/index.js';
  import { Overlay } from '@slink/ui/components/popover';
  import { TooltipProvider } from '@slink/ui/components/tooltip';

  import { page } from '$app/state';
  import Icon from '@iconify/svelte';
  import { cubicOut } from 'svelte/easing';
  import { scale } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import type { ShareFormat } from '@slink/lib/settings';

  import { cn } from '@slink/utils/ui';

  import ShareFormatMenu from '../ShareFormat/ShareFormatMenu.svelte';
  import type { ActionButton, ActionLayout } from './ImageActionBar.theme';
  import {
    actionButtonVariants,
    copyControlVariants,
    downloadIconVariants,
    downloadLabelVariants,
    iconSizeVariants,
  } from './ImageActionBar.theme';
  import { createImageActionsState } from './ImageActionsState.svelte';

  interface Props {
    image: {
      id: string;
      fileName: string;
      isPublic: boolean;
      collectionIds?: string[];
      tagIds?: string[];
    };
    buttons?: ActionButton[];
    compact?: boolean;
    layout?: ActionLayout;
    on?: {
      imageDelete?: (imageId: string) => void;
      collectionChange?: (
        imageId: string,
        collections: CollectionReference[],
      ) => void;
      tagChange?: (imageId: string, tags: Tag[]) => void;
    };
  }

  let {
    image = $bindable(),
    buttons = ['download', 'copy', 'collection', 'visibility', 'delete'],
    compact = false,
    layout = 'default',
    on,
  }: Props = $props();

  const isHero = $derived(layout === 'hero');
  const iconClass = $derived(iconSizeVariants({ layout }));

  const { settings } = page.data;
  const selectedFormat = $derived(settings.share.format);

  const actions = createImageActionsState({
    getImage: () => image,
    onImageUpdate: (updated) => (image = updated),
    onImageDelete: (id) => on?.imageDelete?.(id),
    onCollectionChange: (id, ids) => on?.collectionChange?.(id, ids),
    onTagChange: (id, tags) => on?.tagChange?.(id, tags),
  });

  const visibleButtons = $derived(actions.filterVisibleButtons(buttons));

  const copyTooltip = $derived.by(() => {
    if (actions.shareIsLoading) return 'Generating...';
    if (actions.isCopied.active) return 'Copied!';
    return 'Copy link';
  });

  const visibilityTooltip = $derived.by(() => {
    if (image.isPublic) return 'Make private';
    return 'Make public';
  });

  const handleCopyFormatSelect = (format: ShareFormat) => {
    settings.share = { format };
    actions.handleCopy(format);
  };
</script>

{#snippet loaderOrIcon(icon: string, isLoading: boolean, extraClass?: string)}
  {#if isLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else}
    <Icon {icon} class={cn(iconClass, extraClass)} />
  {/if}
{/snippet}

{#snippet copyIconContent()}
  {#if actions.shareIsLoading}
    <div class={cn(iconClass, 'flex items-center justify-center')}>
      <Loader variant="minimal" size="xs" />
    </div>
  {:else if actions.isCopied.active}
    <div in:scale={{ duration: 300, easing: cubicOut }}>
      <Icon
        icon="lucide:check"
        class={cn(iconClass, 'text-green-600 dark:text-green-400')}
      />
    </div>
  {:else}
    <Icon icon="tabler:link" class={iconClass} />
  {/if}
{/snippet}

{#snippet deletePopoverContent()}
  <ImageDeletePopover
    loading={actions.deleteIsLoading}
    close={() => (actions.popover.delete = false)}
    confirm={({ preserveOnDiskAfterDeletion }) =>
      actions.handleDelete(preserveOnDiskAfterDeletion)}
  />
{/snippet}

{#snippet downloadButton()}
  <ButtonGroupItem
    variant="primary"
    size="md"
    class={actionButtonVariants({
      layout,
      variant: compact ? 'default' : 'primary',
    })}
    onclick={actions.handleDownload}
    disabled={actions.downloadIsLoading}
    aria-label="Download image"
    tooltip={compact && !isHero ? 'Download' : undefined}
  >
    {@render loaderOrIcon(
      'lucide:download',
      actions.downloadIsLoading,
      downloadIconVariants({ layout }),
    )}
    {#if isHero || !compact}
      <span class={downloadLabelVariants({ layout })}>Download</span>
    {/if}
  </ButtonGroupItem>
{/snippet}

{#snippet visibilityButton()}
  <ButtonGroupItem
    variant="default"
    size="md"
    class={actionButtonVariants({ layout })}
    onclick={actions.handleVisibilityChange}
    disabled={actions.visibilityIsLoading}
    aria-label={visibilityTooltip}
    aria-pressed={image.isPublic}
    tooltip={visibilityTooltip}
  >
    {@render loaderOrIcon(actions.visibilityIcon, actions.visibilityIsLoading)}
  </ButtonGroupItem>
{/snippet}

{#snippet copyButton()}
  {@const copyControl = copyControlVariants()}
  <div class={copyControl.group()}>
    <ButtonGroupItem
      variant={isHero ? 'default' : 'secondary'}
      size="md"
      class={cn(
        actionButtonVariants({ layout, variant: 'secondary' }),
        copyControl.zone(),
      )}
      onclick={() => actions.handleCopy(selectedFormat)}
      disabled={actions.shareIsLoading || actions.isCopied.active}
      aria-label="Copy image link"
      tooltip={copyTooltip}
    >
      {@render copyIconContent()}
    </ButtonGroupItem>
    <DropdownMenu.Root>
      <DropdownMenu.Trigger
        disabled={actions.shareIsLoading || actions.isCopied.active}
      >
        {#snippet child({ props })}
          <ButtonGroupItem
            {...props}
            variant={isHero ? 'default' : 'secondary'}
            size="md"
            class={cn(
              actionButtonVariants({ layout, variant: 'secondary' }),
              copyControl.zone(),
              copyControl.caret(),
            )}
            disabled={actions.shareIsLoading || actions.isCopied.active}
            aria-label="Copy link options"
          >
            <Icon icon="ph:caret-down" class="h-2.5 w-2.5" />
          </ButtonGroupItem>
        {/snippet}
      </DropdownMenu.Trigger>
      <ShareFormatMenu
        selected={selectedFormat}
        onSelect={handleCopyFormatSelect}
      />
    </DropdownMenu.Root>
  </div>
{/snippet}

{#snippet deleteButton()}
  <Overlay
    bind:open={actions.popover.delete}
    variant="floating"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="destructive"
        size="md"
        class={actionButtonVariants({ layout, variant: 'destructive' })}
        aria-label="Delete image"
        disabled={actions.deleteIsLoading}
        tooltip="Delete image"
        disableTooltip={actions.popover.delete}
      >
        <Icon icon="lucide:trash-2" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    {@render deletePopoverContent()}
  </Overlay>
{/snippet}

{#snippet collectionButton()}
  <Overlay
    bind:open={actions.popover.collection}
    variant="floating"
    size="none"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Add to collection"
        tooltip="Add to collection"
        disableTooltip={actions.popover.collection}
      >
        <Icon icon="lucide:folder" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <CollectionPicker
      pickerState={actions.collectionPickerState}
      createModalState={actions.createCollectionModalState}
      variant="popover"
      onToggle={actions.handleCollectionToggle}
      onBeforeCreate={actions.popover.suspend}
      onAfterClose={actions.popover.restore}
    />
  </Overlay>
{/snippet}

{#snippet tagButton()}
  <Overlay
    bind:open={actions.popover.tag}
    variant="floating"
    size="none"
    contentProps={{ align: 'end' }}
  >
    {#snippet trigger()}
      <ButtonGroupItem
        variant="default"
        size="md"
        class={actionButtonVariants({ layout })}
        aria-label="Manage tags"
        tooltip="Manage tags"
        disableTooltip={actions.popover.tag}
      >
        <Icon icon="lucide:tag" class={iconClass} />
      </ButtonGroupItem>
    {/snippet}
    <TagPicker
      pickerState={actions.tagPickerState}
      createModalState={actions.createTagModalState}
      variant="popover"
      onToggle={actions.handleTagToggle}
      onBeforeCreate={actions.popover.suspend}
      onAfterClose={actions.popover.restore}
    />
  </Overlay>
{/snippet}

{#snippet renderButton(button: ActionButton)}
  {#if button === 'download'}
    {@render downloadButton()}
  {:else if button === 'visibility'}
    {@render visibilityButton()}
  {:else if button === 'copy'}
    {@render copyButton()}
  {:else if button === 'delete'}
    {@render deleteButton()}
  {:else if button === 'collection'}
    {@render collectionButton()}
  {:else if button === 'tag'}
    {@render tagButton()}
  {/if}
{/snippet}

<TooltipProvider delayDuration={300}>
  {#if isHero}
    <div
      class="flex items-center gap-3"
      role="toolbar"
      aria-label="Image actions"
    >
      {#if visibleButtons.includes('download')}
        {@render renderButton('download')}
      {/if}
      <div class="flex items-center gap-1">
        {#each visibleButtons as button (button)}
          {#if button !== 'download' && button !== 'delete'}
            {@render renderButton(button)}
          {/if}
        {/each}
        <div class="w-px h-5 bg-gray-200 dark:bg-gray-700 mx-1"></div>
        {#if visibleButtons.includes('delete')}
          {@render renderButton('delete')}
        {/if}
      </div>
    </div>
  {:else}
    <ButtonGroup
      variant="glass"
      size="md"
      gap="sm"
      padding="sm"
      class="rounded-[10px]"
      role="toolbar"
      aria-label="Image actions"
    >
      {#each visibleButtons as button (button)}
        {@render renderButton(button)}
      {/each}
    </ButtonGroup>
  {/if}
</TooltipProvider>

<CreateCollectionDialog modalState={actions.createCollectionModalState} />
<CreateTagDialog modalState={actions.createTagModalState} />
