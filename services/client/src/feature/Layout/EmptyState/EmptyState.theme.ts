import { cva } from 'class-variance-authority';

export const containerVariants = cva('w-full', {
  variants: {
    kind: {
      'first-use': 'relative',
      'no-results':
        'flex items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5 dark:border-slate-700/60 dark:bg-slate-900/60',
    },
  },
});

export const previewVariants = cva(
  'pointer-events-none absolute inset-x-0 top-0 max-h-56 overflow-hidden [mask-image:linear-gradient(to_bottom,black,transparent_85%)] sm:max-h-64',
);

export const contentVariants = cva(
  'relative flex flex-col items-center px-4 pb-10 text-center',
  {
    variants: {
      withPreview: {
        true: 'pt-36 sm:pt-44',
        false: 'pt-12',
      },
    },
    defaultVariants: {
      withPreview: false,
    },
  },
);

export const titleVariants = cva(
  'font-semibold text-slate-900 dark:text-slate-100',
  {
    variants: {
      kind: {
        'first-use': 'text-lg',
        'no-results': 'text-sm',
      },
    },
  },
);

export const descriptionVariants = cva('text-slate-600 dark:text-slate-400', {
  variants: {
    kind: {
      'first-use': 'mt-2 max-w-md text-sm leading-relaxed',
      'no-results': 'mt-0.5 text-xs',
    },
  },
});

export const actionVariants = cva('', {
  variants: {
    kind: {
      'first-use': 'mt-6',
      'no-results': 'shrink-0',
    },
  },
});

export const hintVariants = cva(
  'mt-[18px] inline-flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400',
);

export const hintIconVariants = cva(
  'flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
);

export const iconVariants = cva(
  'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
  {
    variants: {
      tone: {
        default:
          'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
        danger: 'bg-red-50 text-red-500 dark:bg-red-950/50 dark:text-red-400',
      },
    },
    defaultVariants: {
      tone: 'default',
    },
  },
);
