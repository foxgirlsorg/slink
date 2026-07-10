<script lang="ts">
  import { StopPropagation } from '@slink/feature/Action';
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    ImageActionBar,
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { calculateHistoryCardWeight } from '@slink/feature/Image/utils/calculateHistoryCardWeight';
  import { Masonry } from '@slink/feature/Layout';
  import { ImageTagList } from '@slink/feature/Tag';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import { fade, fly } from 'svelte/transition';

  import type { Tag } from '@slink/api/Resources/TagResource';
  import type { CollectionReference } from '@slink/api/Response/Collection/CollectionResponse';

  import { cn } from '@slink/utils/ui';
  import { PreviewUrl } from '@slink/utils/url';

  import {
    actionBarVisibilityVariants,
    checkboxVariants,
    createActionBarImage,
    historyActionBarButtons,
    historyCardVariants,
  } from './HistoryView.theme';
  import type { HistoryViewProps } from './HistoryView.types';
  import ImageMetadata from './ImageMetadata.svelte';
  import { useHistoryItemActions } from './useHistoryItemActions.svelte';

  let { items = [], selectionState, on }: HistoryViewProps = $props();

  const isSelectionMode = $derived(selectionState?.isSelectionMode ?? false);

  const { handleSelect, handleDelete, getItemState } = useHistoryItemActions({
    getSelectionState: () => selectionState,
    onDelete: (id) => on?.delete(id),
    onCollectionChange: (imageId, collections) =>
      on?.collectionChange(imageId, collections),
    onSelectionChange: (id) => on?.selectionChange?.(id),
  });

  const actionHandlers = {
    imageDelete: handleDelete,
    collectionChange: (imageId: string, collections: CollectionReference[]) =>
      on?.collectionChange(imageId, collections),
    tagChange: (imageId: string, tags: Tag[]) => on?.tagChange?.(imageId, tags),
  };
</script>

<Masonry
  {items}
  class="gap-4"
  columns={{
    xs: 1,
    sm: 2,
    md: 2,
    lg: 3,
    xl: 4,
  }}
  getItemWeight={calculateHistoryCardWeight}
>
  {#snippet itemTemplate(item)}
    <article
      in:fly={{ y: 20, duration: 300, delay: Math.random() * 100 }}
      out:fade={{ duration: 200 }}
      class={cn(
        historyCardVariants({ selected: getItemState(item).isSelected }),
        'relative',
      )}
    >
      {#if isSelectionMode}
        <button
          type="button"
          class="absolute inset-0 z-10 cursor-pointer"
          onclick={(e) => handleSelect(e, item)}
          aria-label={getItemState(item).selectionAriaLabel}
        ></button>
      {/if}
      <div class="relative @container">
        <button
          type="button"
          onclick={(e) => handleSelect(e, item)}
          class={checkboxVariants({ selectionMode: isSelectionMode })}
          aria-label={getItemState(item).selectionAriaLabel}
        >
          <OverlayCheckbox selected={getItemState(item).isSelected} />
        </button>
        <a
          href={isSelectionMode ? undefined : `/info/${item.id}`}
          class="block overflow-hidden"
        >
          <ImagePlaceholder
            uniqueId={item.id}
            src={PreviewUrl.image(item.attributes.fileName, {
              width: 400,
              format: 'webp',
            })}
            alt={item.attributes.description || item.attributes.fileName}
            metadata={item.metadata}
            showMetadata={false}
            showOpenInNewTab={false}
            rounded={false}
            class="transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
          />
        </a>

        <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
          <VisibilityBadge
            isPublic={item.attributes.isPublic}
            variant="overlay"
            compact
          />
          <ViewCountBadge count={item.attributes.views} variant="overlay" />
        </div>

        <StopPropagation>
          <div
            class={actionBarVisibilityVariants({
              selectionMode: isSelectionMode ?? false,
            })}
          >
            <ImageActionBar
              image={createActionBarImage(item)}
              buttons={historyActionBarButtons}
              on={actionHandlers}
              compact
              responsive
            />
          </div>
        </StopPropagation>
      </div>

      <div class="p-3 flex flex-col gap-2">
        <Link
          href={`/info/${item.id}`}
          class="font-mono text-xs font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block"
          title={item.attributes.fileName}
        >
          {item.attributes.fileName}
        </Link>

        <ImageMetadata {item} gap="sm" />

        {#if (item.tags?.length ?? 0) > 0 || (item.collections?.length ?? 0) > 0}
          <div class="flex flex-wrap items-center gap-2">
            {#if item.tags && item.tags.length > 0}
              <ImageTagList
                imageId={item.id}
                variant="neon"
                showImageCount={false}
                removable={false}
                initialTags={item.tags}
                maxVisible={3}
              />
            {/if}
            {#if item.collections && item.collections.length > 0}
              <ImageCollectionList
                collections={item.collections}
                maxVisible={3}
              />
            {/if}
          </div>
        {/if}
      </div>
    </article>
  {/snippet}
</Masonry>
