import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import OrganizationSettingsLayout from '@/layouts/organization-settings-layout';
import { type Organization } from '@/types';
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
    const tabs = [
        { title: 'Dashboard', url: `/${organization.slug}/dashboard` },
        { title: 'Clusters', url: `/${organization.slug}/clusters` },
        { title: 'Resources', url: `/${organization.slug}/resources` },
        { title: 'Settings', url: `/${organization.slug}/settings/general` },
    ];

    return (
        <AppLayout tabs={tabs}>
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
