<script lang="ts">
  import { ProviderIcon } from '@slink/feature/Auth';
  import { Badge } from '@slink/feature/Text';
  import {
    ActionsMenu,
    DropdownSimpleGroup,
    DropdownSimpleItem,
    SortableList,
  } from '@slink/ui/components';
  import { Switch } from '@slink/ui/components/switch';

  import Icon from '@iconify/svelte';

  import type { OAuthProviderDetails } from '@slink/api/Resources/OAuthResource';

  import { OAuthProviderConfig } from '@slink/lib/auth/oauth';

  import OAuthProviderDeleteConfirmation from './OAuthProviderDeleteConfirmation.svelte';
  import { OAuthProviderListState } from './OAuthProviderListState.svelte';

  interface Props {
    providers: OAuthProviderDetails[];
    onEdit: (provider: OAuthProviderDetails) => void;
  }

  let { providers, onEdit }: Props = $props();

  const listState = new OAuthProviderListState(providers);
</script>

{#if listState.providers.length === 0}
  <div
    class="flex flex-col items-center justify-center py-12 text-center rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
  >
    <div
      class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4"
    >
      <Icon icon="ph:key" class="w-6 h-6 text-gray-400 dark:text-gray-500" />
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">
      No SSO providers configured yet
    </p>
  </div>
{:else}
  <SortableList
    items={listState.providers}
    key={(provider) => provider.id}
    onReorder={(id, index) => listState.reorder(id, index)}
    class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 border border-gray-100 dark:border-gray-800 overflow-hidden"
  >
    {#snippet row({ item: provider, handle })}
      {@const preset = OAuthProviderConfig.resolve(provider.slug)}
      <div
        class="flex items-center justify-between gap-4 px-4 py-3.5 hover:bg-gray-100/50 dark:hover:bg-gray-800/30 transition-colors duration-150"
      >
        <div class="flex items-center gap-3 min-w-0">
          {#if listState.providers.length > 1}
            <button
              type="button"
              aria-label="Drag to reorder"
              class="shrink-0 cursor-grab active:cursor-grabbing text-gray-300 dark:text-gray-600 hover:text-gray-400 dark:hover:text-gray-500 transition-colors duration-150"
              {...handle}
            >
              <Icon icon="ph:dots-six-vertical" class="w-4 h-4" />
            </button>
          {/if}
          <div
            class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0"
          >
            <ProviderIcon provider={preset} class="w-5 h-5" />
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span
                class="text-sm font-medium text-gray-900 dark:text-white truncate"
              >
                {provider.name}
              </span>
              <span class="text-xs text-gray-400 dark:text-gray-500 font-mono">
                {provider.slug}
              </span>
              {#if provider.registrationPolicy === 'allowed'}
                <Badge variant="emerald" size="xs" class="shrink-0">
                  Open registration
                </Badge>
              {:else if provider.registrationPolicy === 'blocked'}
                <Badge variant="slate" size="xs" class="shrink-0">
                  Sign-in only
                </Badge>
              {/if}
            </div>
            <p
              class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs"
            >
              {provider.discoveryUrl}
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <Switch
            checked={provider.enabled}
            onCheckedChange={(checked) => listState.toggle(provider, checked)}
          />

          <ActionsMenu tone="ghost" label="Provider actions">
            <DropdownSimpleGroup>
              {#if listState.deleteConfirmId !== provider.id}
                <DropdownSimpleItem on={{ click: () => onEdit(provider) }}>
                  {#snippet icon()}
                    <Icon icon="ph:note-pencil" class="h-4 w-4" />
                  {/snippet}
                  <span>Edit</span>
                </DropdownSimpleItem>
                <DropdownSimpleItem
                  danger={true}
                  on={{ click: () => listState.requestDelete(provider) }}
                  closeOnSelect={false}
                >
                  {#snippet icon()}
                    <Icon icon="heroicons:trash" class="h-4 w-4" />
                  {/snippet}
                  <span>Delete</span>
                </DropdownSimpleItem>
              {:else}
                <OAuthProviderDeleteConfirmation
                  {provider}
                  {preset}
                  loading={listState.isDeleting(provider.id)}
                  onConfirm={() => listState.confirmDelete(provider)}
                  onCancel={() => listState.cancelDelete()}
                />
              {/if}
            </DropdownSimpleGroup>
          </ActionsMenu>
        </div>
      </div>
    {/snippet}
  </SortableList>
{/if}
