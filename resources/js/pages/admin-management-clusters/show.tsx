import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { type ManagementCluster } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { SiDigitalocean, SiDocker, SiHetzner, SiKubernetes } from '@icons-pack/react-simple-icons';
import { Cloud, Layers, MapPin, Server } from 'lucide-react';

interface ShowManagementClusterPageProps {
    cluster: ManagementCluster;
}

const STATUS_DOT_COLORS: Record<string, string> = {
    ready: 'bg-emerald-500',
    bootstrapping: 'bg-amber-500',
    failed: 'bg-destructive',
};

const PROVIDER_CONFIG: Record<string, { icon: React.ReactNode; bg: string; color: string }> = {
    hetzner: { icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
    docker: { icon: <SiDocker className="size-[18px]" />, bg: 'bg-[#2496ed]', color: 'text-white' },
    digital_ocean: { icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
};

const NAV_ITEMS = [
    { title: 'Overview', section: 'overview' },
    { title: 'Tenant Clusters', section: 'tenant-clusters' },
    { title: 'Monitoring', section: 'monitoring' },
    { title: 'Logs', section: 'logs' },
    { title: 'Danger Zone', section: 'danger-zone' },
];

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

function DetailRow({ icon, label, value }: { icon: React.ReactNode; label: string; value: React.ReactNode }) {
    return (
        <div className="flex items-center justify-between py-3">
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                {icon}
                {label}
            </div>
            <div className="text-sm font-medium">{value}</div>
        </div>
    );
}

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Show({ cluster }: ShowManagementClusterPageProps) {
    const currentSection = 'overview';

    return (
        <AppLayout tabs={adminTabs}>
            <Head title={cluster.name} />
            <div className="flex items-start justify-center px-7">
                {/* Left sidebar */}
                <div className="sticky top-[calc(var(--header-height,56px)+2.5rem)] w-56 shrink-0 pt-6">
                    <div className="space-y-5">
                        <div>
                            <Link
                                href="/admin/management-clusters"
                                className="text-muted-foreground hover:text-foreground text-xs transition-colors"
                            >
                                &larr; All clusters
                            </Link>
                            <h2 className="text-foreground mt-2 pl-3 text-xl/8 font-medium">{cluster.name}</h2>
                        </div>
                        <nav className="space-y-1">
                            {NAV_ITEMS.map((item) => (
                                <button
                                    key={item.section}
                                    className={cn(
                                        'hover:bg-accent flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm focus:outline-hidden',
                                        currentSection === item.section
                                            ? 'bg-accent text-foreground font-medium'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {item.title}
                                </button>
                            ))}
                        </nav>
                    </div>
                </div>

                {/* Main content */}
                <div className="mt-8 w-[calc(768px+6rem)] max-w-none pt-6 pr-0 pb-20 pl-6 xl:px-12">
                    <div className="mx-auto flex w-full items-start justify-center">
                        <div className="w-full space-y-6">
                            {/* Header card */}
                            <div className="rounded-lg border bg-card p-6">
                                <div className="flex items-center gap-4">
                                    <ProviderLogo slug={cluster.provider.slug} />
                                    <div className="flex-1">
                                        <h1 className="text-lg font-semibold">{cluster.name}</h1>
                                        <p className="text-muted-foreground text-sm">
                                            {cluster.provider.name} management cluster
                                        </p>
                                    </div>
                                    <Badge variant="outline" className="gap-1.5">
                                        <div className={`size-2 rounded-full ${STATUS_DOT_COLORS[cluster.status] ?? 'bg-muted-foreground'}`} />
                                        {cluster.status}
                                    </Badge>
                                </div>
                            </div>

                            {/* Details */}
                            <div className="rounded-lg border bg-card">
                                <div className="border-b px-6 py-4">
                                    <h2 className="text-sm font-medium">Cluster Details</h2>
                                </div>
                                <div className="divide-y px-6">
                                    <DetailRow
                                        icon={<Server className="size-4" />}
                                        label="Provider"
                                        value={cluster.provider.name}
                                    />
                                    <DetailRow
                                        icon={<MapPin className="size-4" />}
                                        label="Region"
                                        value={
                                            <span className="flex items-center gap-2">
                                                {cluster.region.name}
                                                <code className="bg-muted rounded px-1 py-0.5 text-xs">{cluster.region.slug}</code>
                                            </span>
                                        }
                                    />
                                    <DetailRow
                                        icon={<SiKubernetes className="size-4" />}
                                        label="Kubernetes Version"
                                        value={
                                            <span className="flex items-center gap-2">
                                                {cluster.version.name}
                                                {!cluster.version.is_supported && (
                                                    <Badge variant="outline" className="text-destructive gap-1.5">EOL</Badge>
                                                )}
                                            </span>
                                        }
                                    />
                                    <DetailRow
                                        icon={<Layers className="size-4" />}
                                        label="Tenant Clusters"
                                        value="0"
                                    />
                                    <DetailRow
                                        icon={<Cloud className="size-4" />}
                                        label="Created"
                                        value={new Date(cluster.created_at).toLocaleDateString()}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right spacer */}
                <div className="hidden w-full max-w-56 xl:block" />
            </div>
        </AppLayout>
    );
}
