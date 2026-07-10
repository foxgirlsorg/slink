<script lang="ts" generics="T">
  import type { Snippet } from 'svelte';
  import { tv } from 'tailwind-variants';

  import { flip } from 'svelte/animate';

  interface SortableHandleProps {
    draggable: true;
    ondragstart: (event: DragEvent) => void;
    ondragend: () => void;
  }

  interface Props {
    items: T[];
    key: (item: T) => string;
    onReorder: (id: string, index: number) => void;
    class?: string;
    row: Snippet<[{ item: T; dragging: boolean; handle: SortableHandleProps }]>;
  }

  let { items, key, onReorder, class: className, row }: Props = $props();

  const sortableList = tv({
    slots: {
      base: '',
      item: '',
    },
    variants: {
      dragging: {
        true: {
          item: 'opacity-50',
        },
      },
    },
  });

  const { base, item: itemClass } = sortableList();

  let dragId: string | null = $state(null);
  let hoverIndex: number | null = $state(null);

  const displayItems = $derived.by(() => {
    if (!dragId || hoverIndex === null) return items;

    const fromIndex = items.findIndex((item) => key(item) === dragId);
    if (fromIndex === -1) return items;

    const preview = [...items];
    const [dragged] = preview.splice(fromIndex, 1);
    preview.splice(hoverIndex, 0, dragged);
    return preview;
  });

  function resetDrag() {
    dragId = null;
    hoverIndex = null;
  }

  function handleDragStart(event: DragEvent, id: string) {
    if (!event.dataTransfer) return;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', id);
    dragId = id;
  }

  function handleDragOver(event: DragEvent, index: number, id: string) {
    if (!dragId) return;
    event.preventDefault();
    if (event.dataTransfer) {
      event.dataTransfer.dropEffect = 'move';
    }
    if (id === dragId || hoverIndex === index) return;
    if (!crossedMidpoint(event, index)) return;
    hoverIndex = index;
  }

  function crossedMidpoint(event: DragEvent, index: number): boolean {
    const target = event.currentTarget;
    if (!(target instanceof HTMLElement)) return true;
    const rect = target.getBoundingClientRect();
    const midpoint = rect.top + rect.height / 2;
    const currentIndex =
      hoverIndex ?? items.findIndex((item) => key(item) === dragId);
    if (index > currentIndex) return event.clientY > midpoint;
    return event.clientY < midpoint;
  }

  function handleDrop(event: DragEvent) {
    event.preventDefault();
    if (dragId && hoverIndex !== null) {
      onReorder(dragId, hoverIndex);
    }
    resetDrag();
  }

  function handleFor(id: string): SortableHandleProps {
    return {
      draggable: true,
      ondragstart: (event) => handleDragStart(event, id),
      ondragend: resetDrag,
    };
  }
</script>

<div role="list" class={base({ class: className })}>
  {#each displayItems as entry, index (key(entry))}
    {@const id = key(entry)}
    <div
      animate:flip={{ duration: 200 }}
      role="listitem"
      class={itemClass({ dragging: dragId === id })}
      ondragover={(event) => handleDragOver(event, index, id)}
      ondrop={handleDrop}
    >
      {@render row({
        item: entry,
        dragging: dragId === id,
        handle: handleFor(id),
      })}
    </div>
  {/each}
</div>
