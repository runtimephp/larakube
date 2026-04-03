import * as React from 'react';

import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

interface SidebarNavItem {
    title: string;
    url: string;
    icon?: React.ReactNode;
}

interface SidebarNavProps extends React.HTMLAttributes<HTMLElement> {
    title?: string;
    items: SidebarNavItem[];
    currentPath?: string;
}

function SidebarNav({ title, items, currentPath, className, ...props }: SidebarNavProps) {
    return (
        <nav className={cn('space-y-1', className)} {...props}>
            {title && (
                <p className="text-muted-foreground mb-6 px-4 text-[10px] font-bold uppercase tracking-[0.2em]">{title}</p>
            )}
            {items.map((item) => {
                const isActive = currentPath === item.url || currentPath?.startsWith(item.url + '/');
                return (
                    <Link
                        key={item.url}
                        href={item.url}
                        prefetch
                        className={cn(
                            'flex items-center rounded-md px-4 py-2 text-xs font-medium uppercase tracking-widest transition-all',
                            isActive
                                ? 'bg-primary/10 text-primary font-extrabold'
                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                        )}
                    >
                        {item.icon && <span className="mr-2">{item.icon}</span>}
                        {item.title}
                    </Link>
                );
            })}
        </nav>
    );
}

export { SidebarNav, type SidebarNavItem };
