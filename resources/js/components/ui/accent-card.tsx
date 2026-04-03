import * as React from 'react';

import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';

const accentCardVariants = cva('flex gap-4 rounded-lg border p-5', {
    variants: {
        variant: {
            default: 'border-border bg-card',
            destructive: 'border-destructive/30 bg-card',
            warning: 'border-amber-500/30 bg-card',
        },
    },
    defaultVariants: {
        variant: 'default',
    },
});

const accentCardIconVariants = cva('flex size-10 shrink-0 items-center justify-center rounded-lg', {
    variants: {
        variant: {
            default: 'bg-muted text-muted-foreground',
            destructive: 'bg-destructive/10 text-destructive',
            warning: 'bg-amber-500/10 text-amber-500',
        },
    },
    defaultVariants: {
        variant: 'default',
    },
});

interface AccentCardProps extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof accentCardVariants> {
    icon: React.ReactNode;
    title: string;
    description: string;
    action?: React.ReactNode;
}

function AccentCard({ className, variant, icon, title, description, action, ...props }: AccentCardProps) {
    return (
        <div className={cn(accentCardVariants({ variant }), className)} {...props}>
            <div className={cn(accentCardIconVariants({ variant }))}>{icon}</div>
            <div className="flex flex-1 flex-col gap-2">
                <div>
                    <h4 className="text-sm font-bold">{title}</h4>
                    <p className="text-muted-foreground text-xs leading-relaxed">{description}</p>
                </div>
                {action && <div>{action}</div>}
            </div>
        </div>
    );
}

export { AccentCard, accentCardVariants };
