<script lang="ts">
  import {
    ghostFolderBarVariants,
    ghostFolderCardVariants,
    ghostFolderIconVariants,
    ghostFoldersVariants,
  } from './GhostFolders.theme';

  interface Props {
    count?: number;
  }

  let { count = 3 }: Props = $props();
</script>

<div class={ghostFoldersVariants()}>
  {#each Array(count) as _, index (index)}
    <div class={ghostFolderCardVariants()}>
      <div class={ghostFolderIconVariants()}></div>
      <div class={ghostFolderBarVariants({ width: 'long' })}></div>
      <div class={ghostFolderBarVariants({ width: 'short' })}></div>
    </div>
  {/each}
</div>
