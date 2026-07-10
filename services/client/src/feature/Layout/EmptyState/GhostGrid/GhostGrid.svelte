<script lang="ts">
  import { ghostGridVariants, ghostTileVariants } from './GhostGrid.theme';

  interface Props {
    count?: number;
  }

  let { count = 8 }: Props = $props();
</script>

<div class={ghostGridVariants()}>
  {#each Array(count) as _, index (index)}
    <div class={ghostTileVariants()}></div>
  {/each}
</div>
