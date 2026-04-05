import { Badge } from '@/components/ui/badge';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AdminSettingsLayout from '@/layouts/admin-settings-layout';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { SiAkamai, SiDigitalocean, SiDocker, SiHetzner, SiVultr } from '@icons-pack/react-simple-icons';
import { Cloud, KeyRound } from 'lucide-react';

interface Provider {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    has_api_token: boolean;
    created_at: string;
}

interface ProvidersPageProps {
    providers: Provider[];
}

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

const PROVIDER_CONFIG: Record<string, { icon: React.ReactNode; bg: string; color: string }> = {
    hetzner: { icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
    digital_ocean: { icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
    aws: { icon: <Cloud className="size-[18px]" />, bg: 'bg-[#232f3e]', color: 'text-white' },
    vultr: { icon: <SiVultr className="size-[18px]" />, bg: 'bg-[#007bfc]', color: 'text-white' },
    akamai: { icon: <SiAkamai className="size-[18px]" />, bg: 'bg-[#0096d6]', color: 'text-white' },
    docker: { icon: <SiDocker className="size-[18px]" />, bg: 'bg-[#2496ed]', color: 'text-white' },
};

function ProviderLogo({ slug }: { slug: string }) {
    const config = PROVIDER_CONFIG[slug];

    if (!config) {
        return (
            <div className="bg-muted flex size-9 shrink-0 items-center justify-center rounded-lg">
                <Cloud className="text-muted-foreground size-4" />
            </div>
        );
    }

    return (
        <div className={`flex size-9 shrink-0 items-center justify-center rounded-lg ${config.bg} ${config.color}`}>
            {config.icon}
        </div>
    );
}

export default function Index({ providers }: ProvidersPageProps) {
    return (
        <AppLayout tabs={adminTabs}>
            <Head title="Providers" />
            <AdminSettingsLayout>
                <div>
                    <h1 className="text-2xl font-bold">Providers</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Platform cloud providers available for provisioning infrastructure.
                    </p>
                </div>

                {providers.length === 0 ? (
                    <Empty>
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <Cloud className="size-6" />
                            </EmptyMedia>
                            <EmptyTitle>No providers</EmptyTitle>
                            <EmptyDescription>No cloud providers have been configured yet.</EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                ) : (
                    <div className="flex flex-col gap-3">
                        {providers.map((provider) => (
                            <div key={provider.id} className="rounded-lg border bg-card p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <ProviderLogo slug={provider.slug} />
                                        <div>
                                            <span className="font-semibold">{provider.name}</span>
                                            <div className="mt-1 flex items-center gap-2">
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
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                        <KeyRound className="size-3.5" />
                                        {provider.has_api_token ? 'Token configured' : 'No token'}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </AdminSettingsLayout>
        </AppLayout>
    );
}
