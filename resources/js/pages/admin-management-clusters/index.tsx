import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AppLayout from '@/layouts/app-layout';
import { type ManagementCluster } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { SiKubernetes } from '@icons-pack/react-simple-icons';
import { MoreHorizontal, Plus, Server, Settings, Terminal, Trash2 } from 'lucide-react';
import { Item, ItemActions, ItemContent, ItemDescription, ItemGroup, ItemMedia, ItemSeparator, ItemTitle } from '@/components/ui/item';
import { Fragment } from 'react';

interface ManagementClustersPageProps {
    clusters: ManagementCluster[];
}



const adminTabs = [
    { title: 'Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

export default function Index({ clusters }: ManagementClustersPageProps) {
    return (
        <AppLayout tabs={adminTabs}>
            <Head title="Clusters" />
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 sm:p-6">
                <div className="flex items-center justify-between">
                    <h1 className="font-semibold">Clusters</h1>
                    <div className="items-center gap-4">
                        <Button size="sm" className="gap-2">
                            <Plus className="size-4" />
                            Add cluster
                        </Button>
                    </div>
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
                    <>
                        <ItemGroup className="rounded-xl border ">
                            {clusters.map((cluster: ManagementCluster, index: number) => (
                                <Fragment key={cluster.id}>
                                    {index > 0 && <ItemSeparator />}
                                    <Item asChild>
                                        <Link
                                            key={cluster.id}
                                            href={`/admin/management-clusters/${cluster.id}`}
                                            className="bg-card hover:bg-muted/30 block rounded-lg border p-4 transition-colors"
                                        >
                                        <ItemMedia variant="icon">
                                            <div className="flex items-center justify-center bg-gray-800 dark:bg-gray-600 rounded-md size-8">
                                                <Server className="fill-gray-50 size-5" />
                                            </div>
                                        </ItemMedia>
                                        <ItemContent>
                                            <ItemTitle>{cluster.name}</ItemTitle>
                                            <ItemDescription className="flex items-center gap-2">
                                                <span>{cluster.provider.name}</span>
                                                <span>&middot;</span>
                                                <span className="">{cluster.region.name}</span>
                                                <span>&middot;</span>
                                                <span className="">
                                                    <code className="bg-muted rounded px-1 py-0.5 text-xs">{cluster.region.slug}</code>
                                                </span>
                                                <span>&middot;</span>
                                                <span className="">Kubernetes {cluster.version.name}</span>
                                            </ItemDescription>
                                        </ItemContent>
                                        <ItemActions className="flex items-center gap-4">
                                            <span className="text-muted-foreground text-sm">8 tenant clusters</span>
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
                                        </ItemActions>
                                        </Link>
                                    </Item>
                                </Fragment>
                            ))}
                        </ItemGroup>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
