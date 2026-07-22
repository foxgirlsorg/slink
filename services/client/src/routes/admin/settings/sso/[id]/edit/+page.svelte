<script lang="ts">
  import { SettingsPageLayout } from '@slink/feature/Settings';
  import {
    OAuthProviderForm,
    OAuthProviderFormState,
  } from '@slink/feature/Settings/OAuthSettings';
  import { BackLink } from '@slink/ui/components/back-link';

  import { goto, invalidate } from '$app/navigation';

  import { page } from '$app/state';

  import type { PageData } from './$types';

  interface Props {
    data: PageData;
  }

  let { data }: Props = $props();

  const globalSettings = $derived(page.data.globalSettings);
  const siteName = $derived(globalSettings?.customization?.siteName || 'Slink');

  const formState = new OAuthProviderFormState();
  formState.initialize(data.provider);
</script>

<svelte:head>
  <title>Edit SSO Provider | {siteName}</title>
</svelte:head>

<SettingsPageLayout
  title="Edit Provider"
  description="Update the SSO provider configuration"
  isInitialized={true}
>
  {#snippet navigation()}
    <BackLink href="/admin/settings/sso" class="mb-4">Back to SSO</BackLink>
  {/snippet}
  <OAuthProviderForm
    {formState}
    globalUserSettings={data.adminSettings.user}
    onCancel={() => goto('/admin/settings/sso')}
    onSuccess={async () => {
      await invalidate('app:sso-providers');
      goto('/admin/settings/sso');
    }}
  />
</SettingsPageLayout>
