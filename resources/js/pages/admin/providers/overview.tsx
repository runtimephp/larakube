import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import AdminProviderLayout, { ProviderLogo } from '@/layouts/admin-provider-layout';
import { Head } from '@inertiajs/react';

interface Provider {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    has_api_token: boolean;
    created_at: string;
}

interface OverviewPageProps {
    provider: Provider;
    regionsCount: number;
}

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Overview({ provider, regionsCount }: OverviewPageProps) {
    return (
        <AppLayout tabs={adminTabs}>
            <Head title={`${provider.name} - Overview`} />
            <AdminProviderLayout provider={provider}>
                <SettingsSection title="Overview" description="General information about this provider.">
                    <SettingsField label="Provider" description="The cloud infrastructure provider.">
                        <div className="flex items-center gap-3">
                            <ProviderLogo slug={provider.slug} />
                            <span className="text-sm font-medium">{provider.name}</span>
                        </div>
                    </SettingsField>

                    <SettingsField label="Status" description="Whether this provider is currently active.">
                        {provider.is_active ? (
                            <Badge variant="outline" className="gap-1.5">
                                <div className="size-2 rounded-full bg-emerald-500" />
                                Active
                            </Badge>
                        ) : (
                            <Badge variant="outline" className="gap-1.5 text-muted-foreground">
                                <div className="size-2 rounded-full bg-muted-foreground/40" />
                                Inactive
                            </Badge>
                        )}
                    </SettingsField>

                    <SettingsField label="API token" description="Whether an API token has been configured.">
                        <Badge variant="outline" className={provider.has_api_token ? '' : 'text-muted-foreground'}>
                            {provider.has_api_token ? 'Configured' : 'Not configured'}
                        </Badge>
                    </SettingsField>

                    <SettingsField label="Regions" description="Number of synced regions for this provider.">
                        <span className="text-sm font-medium">{regionsCount}</span>
                    </SettingsField>
                </SettingsSection>
            </AdminProviderLayout>
        </AppLayout>
    );
}
