<script lang="ts">
  import { ghostChipVariants, ghostChipsVariants } from './GhostChips.theme';

  const widths = [
    'md',
    'lg',
    'sm',
    'md',
    'sm',
    'lg',
    'md',
    'sm',
    'md',
    'lg',
  ] as const;
</script>

<div class={ghostChipsVariants()}>
  {#each widths as width, index (index)}
    <div class={ghostChipVariants({ width })}></div>
  {/each}
</div>
