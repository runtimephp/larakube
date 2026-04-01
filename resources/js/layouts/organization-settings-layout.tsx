import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import organizationSettings from '@/routes/organizations/settings';
import { type Organization } from '@/types';
import { Link } from '@inertiajs/react';
import { Cloud, Settings, ShieldAlert, Users, WalletCards } from 'lucide-react';

interface OrganizationSettingsLayoutProps {
    children: React.ReactNode;
    organization: Organization;
}

export default function OrganizationSettingsLayout({ children, organization }: OrganizationSettingsLayoutProps) {
    const currentPath = window.location.pathname;
    const navigationItems = [
        { title: 'General', href: organizationSettings.general.edit.url(organization.slug), icon: Settings },
        { title: 'Members', href: organizationSettings.members.url(organization.slug), icon: Users },
        { title: 'Billing', href: organizationSettings.billing.url(organization.slug), icon: WalletCards },
        { title: 'Cloud Providers', href: organizationSettings.cloudProviders.url(organization.slug), icon: Cloud },
        { title: 'Danger Zone', href: organizationSettings.dangerZone.url(organization.slug), icon: ShieldAlert },
    ];

    return (
        <div className="px-4 py-6">
            <Heading title="Organization Settings" description={`Manage settings for ${organization.name}`} />

            <div className="flex flex-col gap-8 lg:flex-row lg:gap-12">
                <aside className="w-full max-w-xl lg:w-56">
                    <nav className="flex flex-col gap-1">
                        {navigationItems.map((item) => (
                            <Button
                                key={item.href}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start gap-2', {
                                    'bg-muted': currentPath === item.href,
                                })}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon ? <item.icon className="size-4" /> : null}
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="md:hidden" />

                <div className="min-w-0 flex-1">
                    <section className="max-w-3xl space-y-8">{children}</section>
                </div>
            </div>
        </div>
    );
}
