import AppLogoIcon from '@/components/app-logo-icon';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
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
                    <button
                        key={organization.id}
                        onClick={() => switchOrganization(organization)}
                        className="hover:bg-accent flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm"
                    >
                        <OrganizationAvatar organization={organization} />
                        <span className="flex-1 truncate text-left">{organization.name}</span>
                        {organization.id === currentOrganization.id && <Check className="size-4" />}
                    </button>
                ))}
                <div className="my-1 h-px bg-neutral-100 dark:bg-neutral-800" />
                <button
                    onClick={() => router.visit(create.url())}
                    className="hover:bg-accent flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm"
                >
                    <div className="bg-background flex size-5 items-center justify-center rounded border">
                        <Plus className="size-3" />
                    </div>
                    <span>Create organization</span>
                </button>
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
                            <AvatarFallback className="bg-neutral-200 text-xs text-black dark:bg-neutral-700 dark:text-white">
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
        <div className="border-border bg-background border-b">
            {/* Main bar */}
            <div className="flex h-14 items-center gap-3 px-4 sm:px-6">
                {/* Logo */}
                <Link href={currentOrganization ? `/${currentOrganization.slug}/dashboard` : '/dashboard'} className="flex items-center gap-2 shrink-0">
                    <div className="bg-foreground text-background flex size-7 items-center justify-center rounded-md">
                        <AppLogoIcon className="size-4 fill-current" />
                    </div>
                    <span className="text-sm font-semibold">Kuven</span>
                </Link>

                {/* Separator */}
                <span className="text-border select-none">/</span>

                {/* Org switcher */}
                {currentOrganization && organizations && (
                    <OrgSwitcher currentOrganization={currentOrganization} organizations={organizations} />
                )}

                {/* Spacer */}
                <div className="flex-1" />

                {/* User avatar */}
                <UserAvatar />
            </div>


            {/* Tabs row */}
            {tabs && tabs.length > 0 && (
                <div className="flex items-end gap-0 px-4 sm:px-6">
                    {tabs.map((tab) => {
                        const isActive = currentPath === tab.url || currentPath.startsWith(tab.url + '/');
                        return (
                            <Link
                                key={tab.url}
                                href={tab.url}
                                prefetch
                                className={cn(
                                    'relative px-3 py-2.5 text-sm transition-colors',
                                    isActive
                                        ? 'text-foreground font-medium'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {tab.title}
                                {isActive && (
                                    <span className="bg-foreground absolute right-3 bottom-0 left-3 h-0.5 rounded-t-full" />
                                )}
                            </Link>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
