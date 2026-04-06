import { Badge } from '@/components/ui/badge';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';
import { SiAkamai, SiDigitalocean, SiDocker, SiHetzner, SiVultr } from '@icons-pack/react-simple-icons';
import { Cloud, KeyRound, MapPin } from 'lucide-react';

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

export default function Show({ provider, regions }: ShowProviderPageProps) {
    const currentSection = 'overview';

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
                            {/* Header card */}
                            <div className="rounded-lg border bg-card p-6">
                                <div className="flex items-center gap-4">
                                    <ProviderLogo slug={provider.slug} />
                                    <div className="flex-1">
                                        <h1 className="text-lg font-semibold">{provider.name}</h1>
                                        <p className="text-muted-foreground text-sm">Platform cloud provider</p>
                                    </div>
                                    <div className="flex items-center gap-2">
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
                                        <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                            <KeyRound className="size-3.5" />
                                            {provider.has_api_token ? 'Token configured' : 'No token'}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Regions */}
                            <div className="rounded-lg border bg-card">
                                <div className="border-b px-6 py-4">
                                    <h2 className="text-sm font-medium">Regions ({regions.length})</h2>
                                </div>
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
                                    <div className="divide-y">
                                        {regions.map((region) => (
                                            <div key={region.id} className="flex items-center justify-between px-6 py-3">
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
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right spacer */}
                <div className="hidden w-full max-w-56 xl:block" />
            </div>
        </AppLayout>
    );
}
