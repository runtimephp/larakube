import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import OrganizationSettingsLayout from '@/layouts/organization-settings-layout';
import { type BreadcrumbItem, type Organization } from '@/types';
import { Head } from '@inertiajs/react';

interface OrganizationSettingsPlaceholderPageProps {
    organization: Organization;
    section: {
        title: string;
        description: string;
    };
    stats?: {
        connected?: number;
    };
}

export default function OrganizationSettingsPlaceholderPage({ organization, section, stats }: OrganizationSettingsPlaceholderPageProps) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: `/${organization.slug}/dashboard`,
        },
        {
            title: 'Organization Settings',
            href: `/${organization.slug}/settings/general`,
        },
        {
            title: section.title,
            href: window.location.pathname,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${organization.name} ${section.title}`} />

            <OrganizationSettingsLayout organization={organization}>
                <Card>
                    <CardHeader>
                        <CardTitle>{section.title}</CardTitle>
                        <CardDescription>{section.description}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {typeof stats?.connected === 'number' ? (
                                <div className="rounded-lg border p-4">
                                    <p className="text-sm font-medium">Connected providers</p>
                                    <p className="text-3xl font-semibold">{stats.connected}</p>
                                </div>
                            ) : null}

                            <div className="text-muted-foreground rounded-lg border border-dashed p-6 text-sm">
                                This section is planned next and now has a stable route, submenu entry, and navigation access point.
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </OrganizationSettingsLayout>
        </AppLayout>
    );
}
