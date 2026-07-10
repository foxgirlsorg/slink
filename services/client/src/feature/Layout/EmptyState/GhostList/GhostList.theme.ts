import { cva } from 'class-variance-authority';

export const ghostListVariants = cva(
  'mx-auto flex w-full max-w-md flex-col gap-3 sm:max-w-lg',
);

export const ghostListRowVariants = cva(
  'flex items-center gap-4 rounded-xl border border-slate-200/80 bg-slate-100/60 p-3 dark:border-slate-700/50 dark:bg-slate-800/30',
);

export const ghostListThumbVariants = cva(
  'h-11 w-11 shrink-0 rounded-lg bg-slate-200/80 dark:bg-slate-700/50',
);

export const ghostListBarVariants = cva(
  'h-2 rounded-full bg-slate-200/80 dark:bg-slate-700/50',
  {
    variants: {
      width: {
        long: 'w-3/5',
        short: 'w-1/3',
      },
    },
    defaultVariants: {
      width: 'long',
    },
  },
);

export const ghostListPillVariants = cva(
  'h-6 w-16 shrink-0 rounded-full bg-slate-200/80 dark:bg-slate-700/50',
);
