import AppLogoIcon from '@/components/app-logo-icon';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { create, switchMethod } from '@/routes/organizations';
import { type Organization, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Plus } from 'lucide-react';

export interface TabItem {
    title: string;
    url: string;
}

function OrganizationAvatar({ organization }: { organization: Organization }) {
    if (organization.logo) {
        return <img src={organization.logo} alt={organization.name} className="size-5 shrink-0 rounded" />;
    }

    return (
        <div className="bg-primary text-primary-foreground flex size-5 shrink-0 items-center justify-center rounded text-xs font-semibold">
            {organization.name.charAt(0).toUpperCase()}
        </div>
    );
}

function OrgSwitcher({ currentOrganization, organizations }: { currentOrganization: Organization; organizations: Organization[] }) {
    const switchOrganization = (organization: Organization) => {
        if (organization.id === currentOrganization.id) {
            return;
        }
        router.post(switchMethod.url(organization.slug));
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button className="hover:bg-accent flex items-center gap-1.5 rounded-md px-2 py-1 text-sm font-medium transition-colors">
                    <OrganizationAvatar organization={currentOrganization} />
                    <span>{currentOrganization.name}</span>
                    <ChevronsUpDown className="text-muted-foreground size-3.5" />
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" sideOffset={8} className="min-w-52">
                <p className="text-muted-foreground px-2 py-1.5 text-xs font-medium">Organizations</p>
                {organizations.map((organization) => (
                    <DropdownMenuItem
                        key={organization.id}
                        onClick={() => switchOrganization(organization)}
                        className="cursor-pointer gap-2"
                    >
                        <OrganizationAvatar organization={organization} />
                        <span className="flex-1 truncate">{organization.name}</span>
                        {organization.id === currentOrganization.id && <Check className="size-4" />}
                    </DropdownMenuItem>
                ))}
                <div className="my-1 h-px bg-border" />
                <DropdownMenuItem
                    onClick={() => router.visit(create.url())}
                    className="cursor-pointer gap-2"
                >
                    <div className="bg-background flex size-5 items-center justify-center rounded border">
                        <Plus className="size-3" />
                    </div>
                    <span>Create organization</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function UserAvatar() {
    const { auth } = usePage<SharedData>().props;
    const getInitials = useInitials();

    return (
        <div className="flex items-center gap-1">
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <button className="hover:ring-border rounded-full transition-all hover:ring-2 hover:ring-offset-1">
                        <Avatar className="size-8">
                            <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                            <AvatarFallback className="bg-muted text-muted-foreground text-xs">
                                {getInitials(auth.user.name)}
                            </AvatarFallback>
                        </Avatar>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" sideOffset={8} className="min-w-56">
                    <UserMenuContent user={auth.user} />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}

export function AppTopBar({ tabs }: { tabs?: TabItem[] }) {
    const { currentOrganization, organizations } = usePage<SharedData>().props;
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <div className="relative sticky top-0 z-40 w-full">
            <div className="bg-background/95 supports-[backdrop-filter]:bg-background/80 backdrop-blur">
                {/* Main bar */}
                <div className="mx-auto flex h-14 max-w-[1920px] items-center gap-3 px-4 sm:px-6">
                    {/* Logo */}
                    <Link
                        href={currentOrganization ? `/${currentOrganization.slug}/dashboard` : '/dashboard'}
                        className="flex shrink-0 items-center gap-2"
                    >
                        <div className="bg-foreground text-background flex size-7 items-center justify-center rounded-md">
                            <AppLogoIcon className="size-4 fill-current" />
                        </div>
                        <span className="text-sm font-semibold">Kuven</span>
                    </Link>

                    {/* Org switcher */}
                    {currentOrganization && organizations && <OrgSwitcher currentOrganization={currentOrganization} organizations={organizations} />}

                    {/* Spacer */}
                    <div className="flex-1" />

                    {/* User avatar */}
                    <UserAvatar />
                </div>

                {/* Tabs row */}
                {tabs && tabs.length > 0 && (
                    <div className="mx-auto flex max-w-[1920px] items-end gap-0 px-4 sm:px-6">
                        {tabs.map((tab, index) => {
                            const isActive = currentPath === tab.url || currentPath.startsWith(tab.url + '/');
                            return (
                                <Link
                                    key={tab.url}
                                    href={tab.url}
                                    prefetch
                                    className={cn(
                                        'relative px-3 py-2.5 text-sm transition-colors',
                                        index === 0 && 'pl-0',
                                        isActive ? 'text-foreground font-medium' : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    {tab.title}
                                    {isActive && (
                                        <span
                                            className={cn(
                                                'bg-foreground absolute right-3 bottom-0 h-0.5 rounded-t-full',
                                                index === 0 ? 'left-0' : 'left-3',
                                            )}
                                        />
                                    )}
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>

            <div aria-hidden="true" className="pointer-events-none absolute inset-x-0 top-[calc(100%-1px)] z-20 bg-background px-2">
                <div className="relative z-20 w-full">
                    <div className="h-2 overflow-hidden">
                        <div className="border-border h-3 rounded-t-lg border-x border-t bg-background" />
                    </div>
                </div>
            </div>
        </div>
    );
}
