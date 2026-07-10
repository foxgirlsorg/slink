import { cva } from 'class-variance-authority';

export const ghostChipsVariants = cva(
  'mx-auto flex w-full max-w-md flex-wrap justify-center gap-2.5 sm:max-w-lg',
);

export const ghostChipVariants = cva(
  'h-7 rounded-full border border-slate-200/80 bg-slate-100 dark:border-slate-700/50 dark:bg-slate-800/50',
  {
    variants: {
      width: {
        sm: 'w-14',
        md: 'w-20',
        lg: 'w-28',
      },
    },
    defaultVariants: {
      width: 'md',
    },
  },
);
