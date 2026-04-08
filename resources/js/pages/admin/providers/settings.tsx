import { update as updateProvider } from '@/actions/App/Http/Controllers/Admin/ProviderController';
import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import AdminProviderLayout from '@/layouts/admin-provider-layout';
import { type Provider } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

interface SettingsPageProps {
    provider: Provider;
    can: {
        update: boolean;
    };
}

const adminTabs = [
    { title: 'Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Settings({ provider, can }: SettingsPageProps) {
    const settingsForm = useForm({
        api_token: '',
        is_active: provider.is_active,
    });

    return (
        <AppLayout tabs={adminTabs}>
            <Head title={`${provider.name} - Settings`} />
            <AdminProviderLayout provider={provider}>
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        settingsForm.patch(updateProvider.url(provider.id), {
                            preserveScroll: true,
                            onSuccess: () => settingsForm.reset('api_token'),
                        });
                    }}
                >
                    <SettingsSection title="Settings" description="Configure API credentials and activation status.">
                        <SettingsField
                            label="API token"
                            description="The API token used to sync catalog data from this provider."
                            htmlFor="api_token"
                        >
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="api_token"
                                    type="password"
                                    placeholder={provider.has_api_token ? '••••••••' : 'Enter API token'}
                                    value={settingsForm.data.api_token}
                                    onChange={(event) => settingsForm.setData('api_token', event.target.value)}
                                    disabled={!can.update || settingsForm.processing}
                                />
                                <InputError message={settingsForm.errors.api_token} />
                            </div>
                        </SettingsField>

                        <SettingsField
                            label="Active"
                            description="Enable this provider for use across the platform."
                            htmlFor="is_active"
                        >
                            <Switch
                                id="is_active"
                                checked={settingsForm.data.is_active}
                                onCheckedChange={(checked) => settingsForm.setData('is_active', checked)}
                                disabled={!can.update || settingsForm.processing}
                            />
                        </SettingsField>

                        <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                            <Button
                                type="submit"
                                size="sm"
                                disabled={!can.update || settingsForm.processing || !settingsForm.isDirty}
                            >
                                {settingsForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                Save changes
                            </Button>
                        </div>
                    </SettingsSection>
                </form>
            </AdminProviderLayout>
        </AppLayout>
    );
}
