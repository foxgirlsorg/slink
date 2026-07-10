import { cva } from 'class-variance-authority';

export const ghostRowsVariants = cva(
  'mx-auto flex w-full max-w-md flex-col gap-2.5 sm:max-w-lg',
);

export const ghostRowVariants = cva(
  'flex items-center gap-3 rounded-xl border border-slate-200/80 bg-slate-100/60 px-3.5 py-2.5 dark:border-slate-700/50 dark:bg-slate-800/30',
);

export const ghostRowThumbVariants = cva(
  'h-8 w-8 shrink-0 rounded-lg bg-slate-200/80 dark:bg-slate-700/50',
);

export const ghostRowBarVariants = cva(
  'h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/50',
  {
    variants: {
      width: {
        long: 'flex-1',
        short: 'w-1/4 shrink-0',
      },
    },
    defaultVariants: {
      width: 'long',
    },
  },
);
