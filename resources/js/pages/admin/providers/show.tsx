import { update as updateProvider } from '@/actions/App/Http/Controllers/Admin/ProviderController';
import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Head, Link, useForm } from '@inertiajs/react';
import { SiAkamai, SiDigitalocean, SiDocker, SiHetzner, SiVultr } from '@icons-pack/react-simple-icons';
import { Cloud, LoaderCircle, MapPin } from 'lucide-react';

interface Provider {
    id: string;
    name: string;
    slug: string;
    is_active: boolean;
    has_api_token: boolean;
    created_at: string;
}

interface PlatformRegion {
    id: string;
    name: string;
    slug: string;
    country: string | null;
    city: string | null;
    is_available: boolean;
}

interface ShowProviderPageProps {
    provider: Provider;
    regions: PlatformRegion[];
    can: {
        update: boolean;
    };
}

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

const NAV_ITEMS = [
    { title: 'Overview', section: 'overview' },
    { title: 'Regions', section: 'regions' },
    { title: 'Settings', section: 'settings' },
];

const PROVIDER_CONFIG: Record<string, { icon: React.ReactNode; bg: string; color: string }> = {
    hetzner: { icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
    digital_ocean: { icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
    aws: { icon: <Cloud className="size-[18px]" />, bg: 'bg-[#232f3e]', color: 'text-white' },
    vultr: { icon: <SiVultr className="size-[18px]" />, bg: 'bg-[#007bfc]', color: 'text-white' },
    akamai: { icon: <SiAkamai className="size-[18px]" />, bg: 'bg-[#0096d6]', color: 'text-white' },
    docker: { icon: <SiDocker className="size-[18px]" />, bg: 'bg-[#2496ed]', color: 'text-white' },
};

function ProviderLogo({ slug }: { slug: string }) {
    const config = PROVIDER_CONFIG[slug];

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

export default function Show({ provider, regions, can }: ShowProviderPageProps) {
    const currentSection = 'overview';

    const settingsForm = useForm({
        api_token: '',
        is_active: provider.is_active,
    });

    return (
        <AppLayout tabs={adminTabs}>
            <Head title={provider.name} />
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
                            <h2 className="text-foreground mt-2 pl-3 text-xl/8 font-medium">{provider.name}</h2>
                        </div>
                        <nav className="space-y-1">
                            {NAV_ITEMS.map((item) => (
                                <button
                                    key={item.section}
                                    className={cn(
                                        'hover:bg-accent flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm focus:outline-hidden',
                                        currentSection === item.section
                                            ? 'bg-accent text-foreground font-medium'
                                            : 'text-muted-foreground',
                                    )}
                                >
                                    {item.title}
                                </button>
                            ))}
                        </nav>
                    </div>
                </div>

                {/* Main content */}
                <div className="mt-8 w-[calc(768px+6rem)] max-w-none pt-6 pr-0 pb-20 pl-6 xl:px-12">
                    <div className="mx-auto flex w-full items-start justify-center">
                        <div className="w-full space-y-6">
                            {/* Overview card */}
                            <SettingsSection title="Overview" description="General information about this provider.">
                                <SettingsField label="Provider" description="The cloud infrastructure provider.">
                                    <div className="flex items-center gap-3">
                                        <ProviderLogo slug={provider.slug} />
                                        <span className="text-sm font-medium">{provider.name}</span>
                                    </div>
                                </SettingsField>

                                <SettingsField label="Status" description="Whether this provider is currently active.">
                                    {provider.is_active ? (
                                        <Badge variant="outline" className="gap-1.5">
                                            <div className="size-2 rounded-full bg-emerald-500" />
                                            Active
                                        </Badge>
                                    ) : (
                                        <Badge variant="outline" className="gap-1.5 text-muted-foreground">
                                            <div className="size-2 rounded-full bg-muted-foreground/40" />
                                            Inactive
                                        </Badge>
                                    )}
                                </SettingsField>

                                <SettingsField label="API token" description="Whether an API token has been configured.">
                                    <Badge variant="outline" className={provider.has_api_token ? '' : 'text-muted-foreground'}>
                                        {provider.has_api_token ? 'Configured' : 'Not configured'}
                                    </Badge>
                                </SettingsField>
                            </SettingsSection>

                            {/* Regions */}
                            <SettingsSection title="Regions" description={`Regions available for ${provider.name}.`}>
                                {regions.length === 0 ? (
                                    <div className="px-6 py-8">
                                        <Empty>
                                            <EmptyHeader>
                                                <EmptyMedia variant="icon">
                                                    <MapPin className="size-6" />
                                                </EmptyMedia>
                                                <EmptyTitle>No regions synced</EmptyTitle>
                                                <EmptyDescription>
                                                    Regions will appear here after syncing from the provider&apos;s API.
                                                </EmptyDescription>
                                            </EmptyHeader>
                                        </Empty>
                                    </div>
                                ) : (
                                    regions.map((region) => (
                                        <div key={region.id} className="flex items-center justify-between p-5 sm:px-6">
                                            <div>
                                                <span className="text-sm font-medium">{region.name}</span>
                                                <div className="mt-0.5 flex items-center gap-2 text-xs text-muted-foreground">
                                                    <code className="rounded bg-muted px-1 py-0.5">{region.slug}</code>
                                                    {region.country && region.city && (
                                                        <span>
                                                            {region.city}, {region.country}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                            {region.is_available ? (
                                                <Badge variant="outline" className="gap-1.5">
                                                    <div className="size-2 rounded-full bg-emerald-500" />
                                                    Available
                                                </Badge>
                                            ) : (
                                                <Badge variant="outline" className="gap-1.5 text-muted-foreground">
                                                    <div className="size-2 rounded-full bg-muted-foreground/40" />
                                                    Unavailable
                                                </Badge>
                                            )}
                                        </div>
                                    ))
                                )}
                            </SettingsSection>

                            {/* Settings */}
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    settingsForm.patch(updateProvider.url(provider.id), {
                                        preserveScroll: true,
                                        onSuccess: () => settingsForm.reset('api_token'),
                                    });
                                }}
                            >
                                <SettingsSection title="Settings" description="Configure API credentials and activation status.">
                                    <SettingsField
                                        label="API token"
                                        description="The API token used to sync catalog data from this provider."
                                        htmlFor="api_token"
                                    >
                                        <div className="w-[260px] space-y-2">
                                            <Input
                                                id="api_token"
                                                type="password"
                                                placeholder={provider.has_api_token ? '••••••••' : 'Enter API token'}
                                                value={settingsForm.data.api_token}
                                                onChange={(event) => settingsForm.setData('api_token', event.target.value)}
                                                disabled={!can.update || settingsForm.processing}
                                            />
                                            <InputError message={settingsForm.errors.api_token} />
                                        </div>
                                    </SettingsField>

                                    <SettingsField
                                        label="Active"
                                        description="Enable this provider for use across the platform."
                                        htmlFor="is_active"
                                    >
                                        <Switch
                                            id="is_active"
                                            checked={settingsForm.data.is_active}
                                            onCheckedChange={(checked) => settingsForm.setData('is_active', checked)}
                                            disabled={!can.update || settingsForm.processing}
                                        />
                                    </SettingsField>

                                    <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                                        <Button
                                            type="submit"
                                            size="sm"
                                            disabled={!can.update || settingsForm.processing || !settingsForm.isDirty}
                                        >
                                            {settingsForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                            Save changes
                                        </Button>
                                    </div>
                                </SettingsSection>
                            </form>
                        </div>
                    </div>
                </div>

                {/* Right spacer */}
                <div className="hidden w-full max-w-56 xl:block" />
            </div>
        </AppLayout>
    );
}
