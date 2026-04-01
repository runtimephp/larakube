import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        url: '/settings/profile',
    },
    {
        title: 'Password',
        url: '/settings/password',
    },
];

export default function SettingsLayout({ children }: { children: React.ReactNode }) {
    const currentPath = window.location.pathname;

    return (
        <div className="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6">
            <div className="flex flex-col gap-8 lg:flex-row">
                <aside className="w-full shrink-0 lg:w-48">
                    <nav className="flex flex-col gap-0.5">
                        {sidebarNavItems.map((item) => (
                            <Link
                                key={item.url}
                                href={item.url}
                                prefetch
                                className={cn(
                                    'rounded-md px-3 py-2 text-sm transition-colors',
                                    currentPath === item.url
                                        ? 'bg-accent text-accent-foreground font-medium'
                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                )}
                            >
                                {item.title}
                            </Link>
                        ))}
                    </nav>
                </aside>

                <div className="flex-1 max-w-2xl">
                    <div className="space-y-10">{children}</div>
                </div>
            </div>
        </div>
    );
}
