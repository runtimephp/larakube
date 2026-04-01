import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

export default function Dashboard() {
    const { currentOrganization } = usePage<SharedData>().props;

    const tabs = currentOrganization
        ? [
              { title: 'Dashboard', url: `/${currentOrganization.slug}/dashboard` },
              { title: 'Clusters', url: `/${currentOrganization.slug}/clusters` },
              { title: 'Resources', url: `/${currentOrganization.slug}/resources` },
              { title: 'Settings', url: '/settings/profile' },
          ]
        : [];

    return (
        <AppLayout tabs={tabs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4 sm:p-6">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="border-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="border-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="border-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="border-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
