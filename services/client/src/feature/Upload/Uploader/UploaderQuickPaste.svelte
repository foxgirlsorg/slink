<script lang="ts">
  import { Shortcut } from '@slink/ui/components';
</script>

<div class="group/shortcut">
  <div
    class="flex items-center gap-4 rounded-full py-[9px] pl-5 pr-2.5 bg-slate-100/70 border border-slate-200/60 dark:bg-slate-400/5 dark:border-slate-400/10 transition-colors duration-200"
  >
    <span
      class="text-[10.5px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-500"
      >Quick paste</span
    >
    <span
      class="w-px h-[18px] bg-slate-300/70 dark:bg-slate-400/15"
      aria-hidden="true"
    ></span>
    <Shortcut
      control={true}
      key="v"
      class="h-auto rounded-[10px] px-3 py-[5px] text-[13px] dark:border-slate-400/25 dark:bg-slate-800/80 dark:text-slate-300"
    />
  </div>
</div>
