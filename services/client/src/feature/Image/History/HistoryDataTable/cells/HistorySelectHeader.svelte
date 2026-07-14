<script lang="ts">
  import { Checkbox } from '@slink/ui/components/checkbox';

  import type { SelectionState } from '@slink/lib/state/SelectionState.svelte';

  interface Props {
    selectionState?: SelectionState;
    allIds: string[];
  }

  let { selectionState, allIds }: Props = $props();

  const allSelected = $derived(
    allIds.length > 0 && allIds.every((id) => selectionState?.isSelected(id)),
  );

  const someSelected = $derived(
    !allSelected && allIds.some((id) => selectionState?.isSelected(id)),
  );

  const handleChange = () => {
    if (allSelected) {
      selectionState?.deselectAll();
    } else {
      selectionState?.selectAll(allIds);
    }
  };
</script>

<Checkbox
  checked={allSelected}
  indeterminate={someSelected}
  onCheckedChange={handleChange}
/>
