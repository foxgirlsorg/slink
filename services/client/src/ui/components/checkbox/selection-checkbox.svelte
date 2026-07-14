<script lang="ts">
  import { cva } from 'class-variance-authority';

  import type { SelectionState } from '@slink/lib/state/SelectionState.svelte';

  import { cn } from '@slink/utils/ui';

  import OverlayCheckbox from './overlay-checkbox.svelte';

  const selectionCheckboxVariants = cva(
    'absolute top-2 left-2 z-20 transition-opacity duration-150',
    {
      variants: {
        selectionMode: {
          true: 'opacity-100',
          false:
            'opacity-0 [@media(hover:hover)]:group-hover:opacity-100 [@media(hover:none)]:hidden',
        },
      },
      defaultVariants: {
        selectionMode: false,
      },
    },
  );

  interface Props {
    id: string;
    selectionState?: SelectionState;
    onSelectionChange?: (id: string) => void;
    class?: string;
  }

  let {
    id,
    selectionState,
    onSelectionChange,
    class: className,
  }: Props = $props();

  const isSelectionMode = $derived(selectionState?.isSelectionMode ?? false);
  const isSelected = $derived(selectionState?.isSelected(id) ?? false);
  const ariaLabel = $derived(selectionState?.ariaLabelFor(id) ?? 'Select item');

  const select = (e: MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    if (!selectionState) return;

    selectionState.select(id);
    onSelectionChange?.(id);
  };
</script>

<button
  type="button"
  onclick={select}
  class={cn(
    selectionCheckboxVariants({ selectionMode: isSelectionMode }),
    className,
  )}
  aria-label={ariaLabel}
>
  <OverlayCheckbox selected={isSelected} />
</button>
