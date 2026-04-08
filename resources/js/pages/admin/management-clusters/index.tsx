import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { SiDigitalocean, SiDocker, SiHetzner, SiKubernetes } from '@icons-pack/react-simple-icons';
import { Cloud, Layers, MapPin, MoreHorizontal, Settings, Terminal, Trash2 } from 'lucide-react';

interface ManagementCluster {
    id: string;
    name: string;
    provider: string;
    provider_name: string;
    region: string;
    region_name: string;
    status: string;
    version: string;
    created_at: string;
}

interface ManagementClustersPageProps {
    clusters: ManagementCluster[];
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

function ProviderLogo({ provider }: { provider: string }) {
    const config = PROVIDER_CONFIG[provider];

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

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Index({ clusters }: ManagementClustersPageProps) {
    return (
        <AppLayout tabs={adminTabs}>
            <Head title="Management Clusters" />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-bold">Management Clusters</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Platform infrastructure for provisioning and managing Kubernetes clusters.
                    </p>
                </div>

                {clusters.length === 0 ? (
                    <Empty>
                        <EmptyHeader>
                            <EmptyMedia variant="icon">
                                <SiKubernetes className="size-6" />
                            </EmptyMedia>
                            <EmptyTitle>No management clusters</EmptyTitle>
                            <EmptyDescription>
                                No management clusters have been provisioned yet. Run <code>kuven:init</code> to bootstrap one.
                            </EmptyDescription>
                        </EmptyHeader>
                    </Empty>
                ) : (
                    <div className="flex flex-col gap-3">
                        {clusters.map((cluster) => (
                            <Link
                                key={cluster.id}
                                href={`/admin/management-clusters/${cluster.id}`}
                                className="block rounded-lg border bg-card p-4 transition-colors hover:bg-muted/30"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <ProviderLogo provider={cluster.provider} />
                                        <span className="font-semibold">{cluster.name}</span>
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <Badge variant="outline" className="gap-1.5">
                                            <div className={`size-2 rounded-full ${STATUS_DOT_COLORS[cluster.status] ?? 'bg-muted-foreground'}`} />
                                            {cluster.status}
                                        </Badge>

                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <Button variant="ghost" size="sm" className="size-8 p-0">
                                                    <MoreHorizontal className="size-4" />
                                                    <span className="sr-only">Actions for {cluster.name}</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem className="gap-2">
                                                    <Settings className="size-4" />
                                                    Edit settings
                                                </DropdownMenuItem>
                                                <DropdownMenuItem className="gap-2">
                                                    <Terminal className="size-4" />
                                                    View logs
                                                </DropdownMenuItem>
                                                <DropdownMenuItem className="text-destructive gap-2">
                                                    <Trash2 className="size-4" />
                                                    Delete
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </div>

                                <div className="mt-2 flex items-center gap-4 pl-12 text-sm text-muted-foreground">
                                    <span className="flex items-center gap-1.5">
                                        <MapPin className="size-3.5" />
                                        {cluster.region_name}
                                    </span>
                                    <span>&middot;</span>
                                    <span className="flex items-center gap-1.5">
                                        <SiKubernetes className="size-3.5" />
                                        {cluster.version}
                                    </span>
                                    <span>&middot;</span>
                                    <span className="flex items-center gap-1.5">
                                        <Layers className="size-3.5" />
                                        0 tenant clusters
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
