import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';

export default function Index() {
    return (
        <AppLayout>
            <Head title="Management Clusters" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4 sm:p-6">
                <h1 className="text-2xl font-bold">Management Clusters</h1>
                <p className="text-muted-foreground">Platform administration for management clusters.</p>
            </div>
        </AppLayout>
    );
}
