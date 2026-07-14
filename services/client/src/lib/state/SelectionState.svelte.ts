export interface SelectionLabels {
  select: string;
  deselect: string;
}

const defaultLabels: SelectionLabels = {
  select: 'Select item',
  deselect: 'Deselect item',
};

export class SelectionState {
  private _selectedIds: Set<string> = $state(new Set());
  private _isSelectionMode: boolean = $state(false);
  private _labels: SelectionLabels;

  constructor(labels?: SelectionLabels) {
    this._labels = labels ?? defaultLabels;
  }

  get selectedIds(): string[] {
    return Array.from(this._selectedIds);
  }

  get selectedCount(): number {
    return this._selectedIds.size;
  }

  get isSelectionMode(): boolean {
    return this._isSelectionMode;
  }

  get hasSelection(): boolean {
    return this._selectedIds.size > 0;
  }

  enterSelectionMode(): void {
    this._isSelectionMode = true;
  }

  exitSelectionMode(): void {
    this._selectedIds = new Set();
    this._isSelectionMode = false;
  }

  select(id: string): void {
    if (!this._isSelectionMode) {
      this._isSelectionMode = true;
    }
    this.toggle(id);
  }

  toggle(id: string): void {
    const newSet = new Set(this._selectedIds);
    if (newSet.has(id)) {
      newSet.delete(id);
    } else {
      newSet.add(id);
    }
    this._selectedIds = newSet;

    if (newSet.size === 0) {
      this._isSelectionMode = false;
    }
  }

  isSelected(id: string): boolean {
    return this._selectedIds.has(id);
  }

  ariaLabelFor(id: string): string {
    if (this.isSelected(id)) {
      return this._labels.deselect;
    }
    return this._labels.select;
  }

  selectAll(ids: string[]): void {
    this._selectedIds = new Set(ids);
  }

  deselectAll(): void {
    this._selectedIds = new Set();
  }

  reset(): void {
    this._selectedIds = new Set();
    this._isSelectionMode = false;
  }

  removeIds(ids: string[]): void {
    const newSet = new Set(this._selectedIds);
    ids.forEach((id) => newSet.delete(id));
    this._selectedIds = newSet;
  }
}

export const createSelectionState = (
  labels?: SelectionLabels,
): SelectionState => {
  return new SelectionState(labels);
};
