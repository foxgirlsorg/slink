<script lang="ts">
  import {
    ghostListBarVariants,
    ghostListPillVariants,
    ghostListRowVariants,
    ghostListThumbVariants,
    ghostListVariants,
  } from './GhostList.theme';

  interface Props {
    rows?: number;
  }

  let { rows = 3 }: Props = $props();
</script>

<div class={ghostListVariants()}>
  {#each Array(rows) as _, index (index)}
    <div class={ghostListRowVariants()}>
      <div class={ghostListThumbVariants()}></div>
      <div class="flex flex-1 flex-col gap-2">
        <div class={ghostListBarVariants({ width: 'long' })}></div>
        <div class={ghostListBarVariants({ width: 'short' })}></div>
      </div>
      <div class={ghostListPillVariants()}></div>
    </div>
  {/each}
</div>
