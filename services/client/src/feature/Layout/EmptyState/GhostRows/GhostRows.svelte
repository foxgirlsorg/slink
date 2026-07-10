<script lang="ts">
  import {
    ghostRowBarVariants,
    ghostRowThumbVariants,
    ghostRowVariants,
    ghostRowsVariants,
  } from './GhostRows.theme';

  interface Props {
    rows?: number;
  }

  let { rows = 3 }: Props = $props();
</script>

<div class={ghostRowsVariants()}>
  {#each Array(rows) as _, index (index)}
    <div class={ghostRowVariants()}>
      <div class={ghostRowThumbVariants()}></div>
      <div class={ghostRowBarVariants({ width: 'long' })}></div>
      <div class={ghostRowBarVariants({ width: 'short' })}></div>
    </div>
  {/each}
</div>
