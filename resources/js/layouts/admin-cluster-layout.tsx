import { cn } from '@/lib/utils';
import { type ManagementCluster } from '@/types';
import { show as showCluster } from '@/routes/admin/management-clusters';
import { Link } from '@inertiajs/react';

interface AdminClusterLayoutProps {
    children: React.ReactNode;
    cluster: Pick<ManagementCluster, 'id' | 'name' >;
}


export default function AdminClusterLayout({ children, cluster }: AdminClusterLayoutProps) {
    const currentPath = window.location.pathname;
    const navigationItems = [
        { title: 'Overview', href: showCluster.url(cluster.id) },
        { title: 'Regions', href: '#' },
        { title: 'Settings', href: '#' },
    ];

    return (
        <div className="flex items-start justify-center px-7">
            {/* Left sidebar */}
            <div className="sticky top-[calc(var(--header-height,56px)+2.5rem)] w-56 shrink-0 pt-6">
                <div className="space-y-5">
                    <div>
                        <Link
                            href="/admin/settings/providers"
                            className="text-muted-foreground hover:text-foreground text-xs transition-colors"
                        >
                            &larr; All providers
                        </Link>
                        <h2 className="text-foreground mt-2 pl-3 text-xl/8 font-medium">{cluster.name}</h2>
                    </div>
                    <nav className="space-y-1">
                        {navigationItems.map((item) => (
                            <Link
                                key={item.href}
                                href={item.href}
                                prefetch
                                className={cn(
                                    'hover:bg-accent flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm focus:outline-hidden',
                                    currentPath === item.href ? 'bg-accent text-foreground font-medium' : 'text-muted-foreground',
                                )}
                            >
                                {item.title}
                            </Link>
                        ))}
                    </nav>
                </div>
            </div>

            {/* Main content */}
            <div className="mt-8 w-[calc(768px+6rem)] max-w-none pt-6 pr-0 pb-20 pl-6 xl:px-12">
                <div className="mx-auto flex w-full items-start justify-center">
                    <div className="w-full space-y-6">{children}</div>
                </div>
            </div>

            {/* Right spacer */}
            <div className="hidden w-full max-w-56 xl:block" />
        </div>
    );
}
