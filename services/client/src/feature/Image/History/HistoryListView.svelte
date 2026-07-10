<script lang="ts">
  import { ImageCollectionList } from '@slink/feature/Collection';
  import {
    ImagePlaceholder,
    ViewCountBadge,
    VisibilityBadge,
  } from '@slink/feature/Image';
  import { ImageTagList } from '@slink/feature/Tag';
  import { OverlayCheckbox } from '@slink/ui/components/checkbox';
  import { Link } from '@slink/ui/components/link';

  import { fade, fly } from 'svelte/transition';

  import { cn } from '@slink/utils/ui';
  import { PreviewUrl } from '@slink/utils/url';

  import HistoryItemActions from './HistoryItemActions.svelte';
  import {
    checkboxVariants,
    historyListRowVariants,
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
</script>

<ul class="@container flex flex-col gap-3" role="list">
  {#each items as item, index (item.id)}
    <li
      in:fly={{ y: 20, duration: 300, delay: index * 50 }}
      out:fade={{ duration: 200 }}
    >
      <article
        class={cn(
          historyListRowVariants({
            selected: getItemState(item).isSelected,
            selectionMode: isSelectionMode ?? false,
          }),
          '@xl:min-h-28',
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
        <div
          class="relative block w-full @xl:w-40 @2xl:w-48 @4xl:w-56 shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800/80"
        >
          <button
            type="button"
            onclick={(e) => handleSelect(e, item)}
            class={cn(
              checkboxVariants({ selectionMode: isSelectionMode }),
              'pointer-events-auto',
            )}
            aria-label={getItemState(item).selectionAriaLabel}
          >
            <OverlayCheckbox selected={getItemState(item).isSelected} />
          </button>
          <a
            href={isSelectionMode ? undefined : `/info/${item.id}`}
            class="block aspect-4/3 @xl:aspect-auto w-full h-full"
          >
            <ImagePlaceholder
              src={PreviewUrl.image(item.attributes.fileName, {
                width: 300,
                height: 300,
                crop: true,
                format: 'webp',
              })}
              alt={item.attributes.description || item.attributes.fileName}
              metadata={item.metadata}
              uniqueId={item.id}
              showOpenInNewTab={false}
              showMetadata={false}
              keepAspectRatio={false}
              objectFit="cover"
              rounded={false}
              class="h-full w-full transition-transform duration-300 group-hover:scale-105 motion-reduce:transform-none motion-reduce:transition-none"
            />
          </a>

          <div class="absolute bottom-2 left-2 flex items-center gap-1.5">
            <VisibilityBadge
              isPublic={item.attributes.isPublic}
              variant="overlay"
            />
            <ViewCountBadge count={item.attributes.views} variant="overlay" />
          </div>
        </div>

        <div class="flex flex-col flex-1 gap-2 p-3 @xl:p-4 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <Link
              href={`/info/${item.id}`}
              class="font-mono text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate"
              title={item.attributes.fileName}
            >
              {item.attributes.fileName}
            </Link>

            <HistoryItemActions
              {item}
              layout="list"
              hoverReveal
              selectionMode={isSelectionMode ?? false}
              on={{
                imageDelete: handleDelete,
                collectionChange: (imageId, collections) =>
                  on?.collectionChange(imageId, collections),
                tagChange: (imageId, tags) => on?.tagChange?.(imageId, tags),
              }}
            />
          </div>

          <ImageMetadata {item} gap="md" />

          {#if (item.tags?.length ?? 0) > 0 || (item.collections?.length ?? 0) > 0}
            <div class="flex flex-wrap items-center gap-2">
              {#if item.tags && item.tags.length > 0}
                <ImageTagList
                  imageId={item.id}
                  variant="neon"
                  showImageCount={false}
                  removable={false}
                  initialTags={item.tags}
                  maxVisible={5}
                />
              {/if}

              {#if item.collections && item.collections.length > 0}
                <ImageCollectionList
                  collections={item.collections}
                  maxVisible={5}
                />
              {/if}
            </div>
          {/if}
        </div>
      </article>
    </li>
  {/each}
</ul>
