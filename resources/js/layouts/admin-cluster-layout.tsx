import { type ManagementCluster } from '@/types';
import { show as showCluster } from '@/routes/admin/management-clusters';
import { AppTopBar } from '@/components/app-top-bar';

interface AdminClusterLayoutProps {
    children: React.ReactNode;
    cluster: Pick<ManagementCluster, 'id' | 'name' >;
}


export default function AdminClusterLayout({ children, cluster }: AdminClusterLayoutProps) {

    const tabs = [
        { title: 'Overview', url: showCluster.url(cluster.id) },
        { title: 'Infrastructure', url: '#' },
        { title: 'Tenants', url: '#' },
        { title: 'Deployments', url: '#' },
        { title: 'Logs', url: '#' },
        { title: 'Metrics', url: '#' },
        { title: 'Settings', url: '#' },
    ];



    return (
        <div className="bg-background flex min-h-screen flex-col">
            <AppTopBar tabs={tabs} />
            <main className="mx-auto flex w-full max-w-[1920px] flex-1 flex-col px-6 py-8 sm:px-12">{children}</main>
        </div>
    );

}
