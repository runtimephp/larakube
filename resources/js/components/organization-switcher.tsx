import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { useIsMobile } from '@/hooks/use-mobile';
import { create, switchMethod } from '@/routes/organizations';
import organizationSettings from '@/routes/organizations/settings';
import { type Organization, type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, Plus, Settings } from 'lucide-react';

function OrganizationAvatar({ organization }: { organization: Organization }) {
    if (organization.logo) {
        return <img src={organization.logo} alt={organization.name} className="size-6 shrink-0 rounded" />;
    }

    return (
        <div className="bg-primary text-primary-foreground flex size-6 shrink-0 items-center justify-center rounded text-xs font-semibold">
            {organization.name.charAt(0).toUpperCase()}
        </div>
    );
}

export function OrganizationSwitcher() {
    const { currentOrganization, organizations } = usePage<SharedData>().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();

    if (!currentOrganization || !organizations) {
        return null;
    }

    const switchOrganization = (organization: Organization) => {
        if (organization.id === currentOrganization.id) {
            return;
        }

        router.post(switchMethod.url(organization.slug));
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <OrganizationAvatar organization={currentOrganization} />
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-medium">{currentOrganization.name}</span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={isMobile ? 'bottom' : state === 'collapsed' ? 'right' : 'bottom'}
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="text-muted-foreground text-xs font-medium">Organizations</DropdownMenuLabel>
                        {organizations.map((organization) => (
                            <DropdownMenuItem
                                key={organization.id}
                                onClick={() => switchOrganization(organization)}
                                className="cursor-pointer gap-2 p-2"
                            >
                                <OrganizationAvatar organization={organization} />
                                <span className="truncate">{organization.name}</span>
                                {organization.id === currentOrganization.id && <Check className="ml-auto size-4" />}
                            </DropdownMenuItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            onClick={() => router.visit(organizationSettings.general.edit.url(currentOrganization.slug))}
                            className="cursor-pointer gap-2 p-2"
                        >
                            <div className="bg-background flex size-4 items-center justify-center rounded border">
                                <Settings className="size-3" />
                            </div>
                            <span>Organization settings</span>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            onClick={() => router.visit(create.url())}
                            className="cursor-pointer gap-2 p-2"
                        >
                            <div className="bg-background flex size-4 items-center justify-center rounded border">
                                <Plus className="size-3" />
                            </div>
                            <span>Create organization</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
