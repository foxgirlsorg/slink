import { cva } from 'class-variance-authority';

export const ghostFoldersVariants = cva(
  'mx-auto grid w-full max-w-md grid-cols-2 gap-3 sm:max-w-lg sm:grid-cols-3 sm:gap-4',
);

export const ghostFolderCardVariants = cva(
  'rounded-xl border border-slate-200/80 bg-slate-100 p-4 dark:border-slate-700/50 dark:bg-slate-800/50',
);

export const ghostFolderIconVariants = cva(
  'mb-4 h-8 w-10 rounded-md bg-slate-200/90 dark:bg-slate-700/60',
);

export const ghostFolderBarVariants = cva(
  'h-2 rounded-full bg-slate-200/90 dark:bg-slate-700/60',
  {
    variants: {
      width: {
        long: 'mb-2 w-2/3',
        short: 'w-2/5',
      },
    },
    defaultVariants: {
      width: 'long',
    },
  },
);
