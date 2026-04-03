import * as React from 'react';

import { cn } from '@/lib/utils';

function SectionCard({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('overflow-hidden rounded-lg border border-border bg-card', className)} {...props}>
            {children}
        </div>
    );
}

function SectionCardHeader({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('px-6 py-5', className)} {...props}>
            {children}
        </div>
    );
}

function SectionCardTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
    return <h3 className={cn('text-sm font-bold tracking-tight', className)} {...props} />;
}

function SectionCardDescription({ className, ...props }: React.HTMLAttributes<HTMLParagraphElement>) {
    return <p className={cn('text-muted-foreground mt-0.5 text-[11px]', className)} {...props} />;
}

function SectionCardContent({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('border-t border-border bg-muted/30 p-6', className)} {...props}>
            {children}
        </div>
    );
}

function SectionCardFooter({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('flex items-center justify-end gap-2 border-t border-border bg-muted/20 px-6 py-3', className)} {...props}>
            {children}
        </div>
    );
}

export { SectionCard, SectionCardContent, SectionCardDescription, SectionCardFooter, SectionCardHeader, SectionCardTitle };
