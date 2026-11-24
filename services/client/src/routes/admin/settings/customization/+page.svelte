<script lang="ts">
  import {
    CustomizationSettings,
    SettingsPageLayout,
  } from '@slink/feature/Settings';

  import { page as appPage } from '$app/state';

  import { useSettingsPage } from '@slink/lib/state/SettingsPage.svelte';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const { globalSettings } = appPage.data;
  const siteName = $derived(globalSettings?.customization?.siteName || 'Slink');

  const page = useSettingsPage();
  let defaultSettings = $derived(data?.defaultSettings);
  let customizationLoading = $derived(
    page.isLoading && page.categoryBeingSaved === 'customization',
  );
</script>

<svelte:head>
  <title>Customization Settings | {siteName}</title>
</svelte:head>

<SettingsPageLayout
  title="Customization"
  description="Customize the appearance and branding of your application"
  isInitialized={page.isInitialized}
>
  <CustomizationSettings
    bind:settings={page.settings.customization}
    defaultSettings={defaultSettings?.customization}
    loading={customizationLoading}
    onSave={page.handleSave}
  />
</SettingsPageLayout>
