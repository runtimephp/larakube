import { cn } from '@/lib/utils';
import { show as showProvider } from '@/actions/App/Http/Controllers/Admin/ProviderOverviewController';
import { show as showRegions } from '@/actions/App/Http/Controllers/Admin/ProviderRegionsController';
import { show as showSettings } from '@/actions/App/Http/Controllers/Admin/ProviderSettingsController';
import { Link } from '@inertiajs/react';
import { SiAkamai, SiDigitalocean, SiDocker, SiHetzner, SiVultr } from '@icons-pack/react-simple-icons';
import { Cloud } from 'lucide-react';

interface Provider {
    id: string;
    name: string;
    slug: string;
}

interface AdminProviderLayoutProps {
    children: React.ReactNode;
    provider: Provider;
}

const PROVIDER_CONFIG: Record<string, { icon: React.ReactNode; bg: string; color: string }> = {
    hetzner: { icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
    digital_ocean: { icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
    aws: { icon: <Cloud className="size-[18px]" />, bg: 'bg-[#232f3e]', color: 'text-white' },
    vultr: { icon: <SiVultr className="size-[18px]" />, bg: 'bg-[#007bfc]', color: 'text-white' },
    akamai: { icon: <SiAkamai className="size-[18px]" />, bg: 'bg-[#0096d6]', color: 'text-white' },
    docker: { icon: <SiDocker className="size-[18px]" />, bg: 'bg-[#2496ed]', color: 'text-white' },
};

export function ProviderLogo({ slug }: { slug: string }) {
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

export default function AdminProviderLayout({ children, provider }: AdminProviderLayoutProps) {
    const currentPath = window.location.pathname;
    const navigationItems = [
        { title: 'Overview', href: showProvider.url(provider.id) },
        { title: 'Regions', href: showRegions.url(provider.id) },
        { title: 'Settings', href: showSettings.url(provider.id) },
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
                        <h2 className="text-foreground mt-2 pl-3 text-xl/8 font-medium">{provider.name}</h2>
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
