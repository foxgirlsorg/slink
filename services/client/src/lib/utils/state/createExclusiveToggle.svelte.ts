export function createExclusiveToggle<K extends string>(...keys: K[]) {
  let _active = $state<K | null>(null);
  let _suspended = $state<K | null>(null);

  const group: Record<string, unknown> = {};

  for (const key of keys) {
    Object.defineProperty(group, key, {
      get: () => _active === key,
      set: (v: boolean) => {
        if (v) {
          _active = key;
        } else if (_active === key) {
          _active = null;
        }
      },
      enumerable: true,
      configurable: true,
    });
  }

  group.close = () => {
    _active = null;
  };
  group.suspend = () => {
    _suspended = _active;
    _active = null;
  };
  group.restore = () => {
    _active = _suspended;
    _suspended = null;
  };

  return group as { [P in K]: boolean } & {
    close: () => void;
    suspend: () => void;
    restore: () => void;
  };
}
