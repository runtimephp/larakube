import { Badge } from '@/components/ui/badge';
import { type ManagementCluster } from '@/types';
import { Head } from '@inertiajs/react';
import { SiKubernetes } from '@icons-pack/react-simple-icons';
import { Cloud, Layers, MapPin, Server } from 'lucide-react';
import AdminClusterLayout from '@/layouts/admin-cluster-layout';
import { ProviderLogo } from '@/components/provider-logo';

interface ShowManagementClusterPageProps {
    cluster: ManagementCluster;
}

const STATUS_DOT_COLORS: Record<string, string> = {
    ready: 'bg-emerald-500',
    bootstrapping: 'bg-amber-500',
    failed: 'bg-destructive',
};



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



export default function Show({ cluster }: ShowManagementClusterPageProps) {

    return (
        <AdminClusterLayout cluster={cluster}>
            <Head title={cluster.name} />

            <div className="flex mx-auto w-full max-w-[1092px]">
                <div className="w-full">
                    <div className="flex items-center">
                        <div className="flex items-center gap-4">
                            <div className="rounded-md bg-black size-10 flex items-center justify-center">
                                <Server className="fill-gray-50" />
                            </div>
                            <div className="flex flex-col">
                                <h1 className="text-lg font-semibold">{cluster.name}</h1>
                                <p className="text-muted-foreground text-sm">
                                    {cluster.provider.name} management cluster
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div className="flex items-start justify-center px-7">

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
        </AdminClusterLayout>
    );
}
