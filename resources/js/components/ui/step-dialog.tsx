import * as React from 'react';

import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import { ArrowLeft } from 'lucide-react';

interface StepDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    children: React.ReactNode;
}

function StepDialog({ open, onOpenChange, children }: StepDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="gap-0 p-0 sm:max-w-md">{children}</DialogContent>
        </Dialog>
    );
}

interface StepDialogHeaderProps {
    title: string;
    description?: string;
    onBack?: () => void;
}

function StepDialogHeader({ title, description, onBack }: StepDialogHeaderProps) {
    return (
        <DialogHeader className="p-6 pb-4">
            {onBack && (
                <button
                    onClick={onBack}
                    className="text-muted-foreground hover:text-foreground mb-2 inline-flex items-center gap-1 text-sm transition-colors self-start"
                >
                    <ArrowLeft className="size-3.5" />
                    Back
                </button>
            )}
            <DialogTitle>{title}</DialogTitle>
            {description && <DialogDescription>{description}</DialogDescription>}
        </DialogHeader>
    );
}

function StepDialogBody({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('px-6 pb-6', className)} {...props}>
            {children}
        </div>
    );
}

function StepDialogFooter({ className, children, ...props }: React.HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={cn('flex items-center justify-end gap-2 border-t border-border px-6 py-4', className)} {...props}>
            {children}
        </div>
    );
}

export { StepDialog, StepDialogBody, StepDialogFooter, StepDialogHeader };
