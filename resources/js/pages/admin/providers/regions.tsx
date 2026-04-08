import { store as syncRegions } from '@/actions/App/Http/Controllers/Admin/ProviderRegionSyncController';
import { SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AppLayout from '@/layouts/app-layout';
import AdminProviderLayout from '@/layouts/admin-provider-layout';
import { type PlatformRegion, type Provider } from '@/types';
import { Head, router } from '@inertiajs/react';
import { LoaderCircle, MapPin, RefreshCw } from 'lucide-react';
import { useState } from 'react';

interface RegionsPageProps {
    provider: Provider;
    regions: PlatformRegion[];
    can: {
        sync_regions: boolean;
    };
}

const adminTabs = [
    { title: 'Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Regions({ provider, regions, can }: RegionsPageProps) {
    const [syncing, setSyncing] = useState(false);

    function handleSync() {
        setSyncing(true);
        router.post(
            syncRegions.url(provider.id),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ['regions'],
                onFinish: () => setSyncing(false),
            },
        );
    }

    return (
        <AppLayout tabs={adminTabs}>
            <Head title={`${provider.name} - Regions`} />
            <AdminProviderLayout provider={provider}>
                <SettingsSection
                    title="Regions"
                    description={`Regions available for ${provider.name}.`}
                    action={
                        can.sync_regions && (
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                disabled={!provider.has_api_token || syncing}
                                onClick={handleSync}
                            >
                                {syncing ? <LoaderCircle className="size-4 animate-spin" /> : <RefreshCw className="size-4" />}
                                Sync regions
                            </Button>
                        )
                    }
                >
                    {regions.length === 0 ? (
                        <div className="px-6 py-8">
                            <Empty>
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <MapPin className="size-6" />
                                    </EmptyMedia>
                                    <EmptyTitle>No regions synced</EmptyTitle>
                                    <EmptyDescription>
                                        Regions will appear here after syncing from the provider&apos;s API.
                                    </EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        </div>
                    ) : (
                        regions.map((region) => (
                            <div key={region.id} className="flex items-center justify-between p-5 sm:px-6">
                                <div>
                                    <span className="text-sm font-medium">{region.name}</span>
                                    <div className="text-muted-foreground mt-0.5 flex items-center gap-2 text-xs">
                                        <code className="bg-muted rounded px-1 py-0.5">{region.slug}</code>
                                        {region.country && region.city && (
                                            <span>
                                                {region.city}, {region.country}
                                            </span>
                                        )}
                                    </div>
                                </div>
                                {region.is_available ? (
                                    <Badge variant="outline" className="gap-1.5">
                                        <div className="size-2 rounded-full bg-emerald-500" />
                                        Available
                                    </Badge>
                                ) : (
                                    <Badge variant="outline" className="gap-1.5 text-muted-foreground">
                                        <div className="size-2 rounded-full bg-muted-foreground/40" />
                                        Unavailable
                                    </Badge>
                                )}
                            </div>
                        ))
                    )}
                </SettingsSection>
            </AdminProviderLayout>
        </AppLayout>
    );
}
