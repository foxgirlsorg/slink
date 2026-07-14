<script lang="ts">
  import { ImageCollectionList } from '@slink/feature/Collection';
  import { ImageTagList } from '@slink/feature/Tag';

  import type { ImageListingItem } from '@slink/api/Response';

  interface Props {
    item: ImageListingItem;
    maxVisible: number;
  }

  let { item, maxVisible }: Props = $props();
</script>

{#if (item.tags?.length ?? 0) > 0 || (item.collections?.length ?? 0) > 0}
  <div class="flex flex-wrap items-center gap-2">
    {#if item.tags && item.tags.length > 0}
      <ImageTagList
        imageId={item.id}
        variant="neon"
        showImageCount={false}
        removable={false}
        initialTags={item.tags}
        {maxVisible}
      />
    {/if}
    {#if item.collections && item.collections.length > 0}
      <ImageCollectionList collections={item.collections} {maxVisible} />
    {/if}
  </div>
{/if}
