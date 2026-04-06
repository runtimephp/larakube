import { SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AdminSettingsLayout from '@/layouts/admin-settings-layout';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
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
                <SettingsSection title="Providers" description="Platform cloud providers available for provisioning infrastructure.">
                    {providers.length === 0 ? (
                        <div className="px-6 py-8">
                            <Empty>
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <Cloud className="size-6" />
                                    </EmptyMedia>
                                    <EmptyTitle>No providers</EmptyTitle>
                                    <EmptyDescription>No cloud providers have been configured yet.</EmptyDescription>
                                </EmptyHeader>
                            </Empty>
                        </div>
                    ) : (
                        providers.map((provider) => (
                            <Link
                                key={provider.id}
                                href={`/admin/settings/providers/${provider.id}`}
                                className="flex items-center justify-between p-5 transition-colors hover:bg-muted/30 sm:px-6"
                            >
                                <div className="flex items-center gap-3">
                                    <ProviderLogo slug={provider.slug} />
                                    <div>
                                        <span className="text-sm font-medium">{provider.name}</span>
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
                            </Link>
                        ))
                    )}
                </SettingsSection>
            </AdminSettingsLayout>
        </AppLayout>
    );
}
