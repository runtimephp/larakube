import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { Appearance, useAppearance } from '@/hooks/use-appearance';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { cn } from '@/lib/utils';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { LogOut, Monitor, Moon, Settings, Sun } from 'lucide-react';

interface UserMenuContentProps {
    user: User;
}

const appearanceTabs: { value: Appearance; icon: typeof Sun; label: string }[] = [
    { value: 'system', icon: Monitor, label: 'System' },
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
];

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();
    const { appearance, updateAppearance } = useAppearance();

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link className="block w-full" href={route('profile.edit')} as="button" prefetch onClick={cleanup}>
                        <Settings className="mr-2 size-4" />
                        Account
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link className="block w-full" method="post" href={route('logout')} as="button" onClick={cleanup}>
                    <LogOut className="mr-2 size-4" />
                    Sign out
                </Link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <div className="px-2 py-1.5">
                <div className="flex items-center justify-between">
                    <span className="text-muted-foreground text-sm">Theme</span>
                    <div className="flex items-center gap-0.5 rounded-md bg-secondary p-0.5">
                        {appearanceTabs.map(({ value, icon: Icon, label }) => (
                            <button
                                key={value}
                                onClick={() => updateAppearance(value)}
                                title={label}
                                className={cn(
                                    'flex items-center rounded px-2 py-1 transition-colors',
                                    appearance === value
                                        ? 'bg-background text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                <Icon className="size-3.5" />
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
