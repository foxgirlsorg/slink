<script lang="ts">
  import { Checkbox } from '@slink/ui/components/checkbox';

  import type { SelectionState } from '@slink/lib/state/SelectionState.svelte';

  interface Props {
    itemId: string;
    selectionState?: SelectionState;
  }

  let { itemId, selectionState }: Props = $props();

  const checked = $derived(selectionState?.isSelected(itemId) ?? false);

  const handleChange = () => {
    selectionState?.toggle(itemId);
  };
</script>

<span role="presentation" onclick={(e) => e.stopPropagation()}>
  <Checkbox {checked} onCheckedChange={handleChange} />
</span>
