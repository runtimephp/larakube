import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import { create, switchMethod } from '@/routes/organizations';
import { type Organization, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, Check, ChevronsUpDown, Plus, Search } from 'lucide-react';

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
                <button className="flex items-center gap-2 rounded-md border border-border/15 px-3 py-1.5 transition-colors hover:bg-accent">
                    <span className="text-muted-foreground text-[10px] font-black uppercase tracking-widest">Org</span>
                    <span className="text-sm font-semibold">{currentOrganization.name}</span>
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
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <button className="size-8 overflow-hidden rounded-md border border-border/15 transition-transform active:scale-95">
                    <Avatar className="size-full rounded-md">
                        <AvatarImage src={auth.user.avatar} alt={auth.user.name} />
                        <AvatarFallback className="bg-muted text-muted-foreground rounded-md text-xs">
                            {getInitials(auth.user.name)}
                        </AvatarFallback>
                    </Avatar>
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" sideOffset={8} className="min-w-56">
                <UserMenuContent user={auth.user} />
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

export function AppTopBar({ tabs }: { tabs?: TabItem[] }) {
    const { auth, currentOrganization, organizations } = usePage<SharedData>().props;
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

    return (
        <header className="sticky top-0 z-40 w-full border-b border-border bg-background/80 backdrop-blur-[20px]">
            <div className="mx-auto flex max-w-[1920px] flex-col px-6 sm:px-12">
                {/* Row 1: Brand, Org Switcher, Search, Notifications, Avatar */}
                <div className="flex items-center justify-between py-4 border-b border-border/10">
                    <div className="flex items-center gap-10">
                        {/* Brand */}
                        <Link
                            href={currentOrganization ? `/${currentOrganization.slug}/dashboard` : '/dashboard'}
                            className="font-headline text-primary text-2xl font-black tracking-tighter"
                        >
                            Kuven
                        </Link>

                        {/* Org switcher */}
                        {currentOrganization && organizations && (
                            <OrgSwitcher currentOrganization={currentOrganization} organizations={organizations} />
                        )}
                    </div>

                    <div className="flex items-center gap-5">
                        {/* Admin link */}
                        {auth.user.platform_role === 'admin' && (
                            <Link
                                href="/admin/management-clusters"
                                className={cn(
                                    'text-xs font-medium uppercase tracking-wider transition-colors duration-200',
                                    currentPath.startsWith('/admin')
                                        ? 'text-primary font-bold'
                                        : 'text-muted-foreground/70 hover:text-primary',
                                )}
                            >
                                Admin
                            </Link>
                        )}

                        {/* Search */}
                        <div className="relative w-64">
                            <Search className="text-muted-foreground/40 absolute left-3 top-1/2 size-3.5 -translate-y-1/2" />
                            <Input
                                type="text"
                                placeholder="Search resources or settings..."
                                className="h-9 bg-card pl-9 text-xs"
                            />
                        </div>

                        {/* Notifications */}
                        <button className="text-muted-foreground hover:text-primary relative rounded-md p-1.5 transition-colors">
                            <Bell className="size-[22px]" />
                            <span className="ring-background absolute top-1.5 right-1.5 size-1.5 rounded-full bg-primary ring-2" />
                        </button>

                        {/* User avatar */}
                        <UserAvatar />
                    </div>
                </div>

                {/* Row 2: Navigation tabs */}
                {tabs && tabs.length > 0 && (
                    <nav className="flex items-center gap-10 py-3">
                        {tabs.map((tab) => {
                            const isActive = currentPath === tab.url || currentPath.startsWith(tab.url + '/');
                            return (
                                <Link
                                    key={tab.url}
                                    href={tab.url}
                                    prefetch
                                    className={cn(
                                        'text-xs font-medium tracking-wider transition-colors duration-200',
                                        isActive
                                            ? 'text-primary border-b-2 border-primary pb-2.5 -mb-3 font-bold'
                                            : 'text-muted-foreground/70 hover:text-primary',
                                    )}
                                >
                                    {tab.title}
                                </Link>
                            );
                        })}
                    </nav>
                )}
            </div>
        </header>
    );
}
