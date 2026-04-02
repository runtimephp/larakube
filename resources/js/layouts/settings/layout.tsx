import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

const sidebarNavItems = [
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
        <div className="flex items-start justify-center px-7">
            {/* Left sidebar */}
            <div className="sticky top-[calc(var(--header-height,56px)+2.5rem)] w-56 shrink-0 pt-6">
                <div className="space-y-5">
                    <h2 className="text-foreground pl-3 text-xl/8 font-medium">Settings</h2>
                    <nav className="space-y-1">
                        {sidebarNavItems.map((item) => (
                            <Link
                                key={item.url}
                                href={item.url}
                                prefetch
                                className={cn(
                                    'hover:bg-accent flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm focus:outline-hidden',
                                    currentPath === item.url ? 'bg-accent text-foreground font-medium' : 'text-muted-foreground',
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

            {/* Right spacer to balance sidebar on wide screens */}
            <div className="hidden w-full max-w-56 xl:block" />
        </div>
    );
}
