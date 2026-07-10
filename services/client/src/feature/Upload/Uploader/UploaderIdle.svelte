<script lang="ts">
  import { cardTitleTheme } from '@slink/ui/components/card';
  import { useDropzone } from '@slink/ui/components/dropzone';

  import Icon from '@iconify/svelte';

  const dropzone = useDropzone();

  const allowMultiple = $derived(dropzone.multiple);
</script>

<div class="relative z-10 flex flex-col items-center">
  <div class="mb-5">
    <div
      class="relative w-16 h-16 rounded-2xl bg-linear-to-br from-indigo-500/20 to-indigo-500/5 dark:from-violet-500/35 dark:to-violet-500/10 flex items-center justify-center transition-transform duration-150 ease-out"
      class:scale-105={dropzone.isDragOver}
    >
      <Icon
        icon="lucide:upload"
        class="h-[26px] w-[26px] text-indigo-600 dark:text-purple-400"
      />
      <div
        class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center ring-[3px] ring-card-primary"
      >
        <Icon icon="ph:plus-bold" class="h-3 w-3 text-white" />
      </div>
    </div>
  </div>

  <div class="space-y-1.5">
    <h2 class={cardTitleTheme()}>
      {#if allowMultiple}Drop your images here{:else}Drop your image here{/if}
    </h2>
    <p
      class="text-slate-500 dark:text-slate-400 text-base sm:text-lg font-light max-w-xs sm:max-w-md mx-auto"
    >
      or click to browse from your device
    </p>
  </div>
</div>
