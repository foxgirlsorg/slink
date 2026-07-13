<script lang="ts">
  import type { Snippet } from 'svelte';

  import Icon from '@iconify/svelte';

  import { type PickerColor, pickerCreateFooterTheme } from './picker.theme';

  interface Props {
    children?: Snippet;
    onclick?: () => void;
    color?: PickerColor;
    highlighted?: boolean;
  }

  let {
    children,
    onclick,
    color = 'blue',
    highlighted = false,
  }: Props = $props();
</script>

{#snippet defaultLabel()}
  New item
{/snippet}

<div
  class="px-1.5 py-1.5 border-t border-gray-100/60 dark:border-gray-800/30 shrink-0"
>
  <button
    type="button"
    {onclick}
    class={pickerCreateFooterTheme({ color, highlighted })}
  >
    <Icon icon="ph:plus" class="w-3.5 h-3.5" />
    <span>{@render (children ?? defaultLabel)()}</span>
  </button>
</div>
