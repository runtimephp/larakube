import { cn } from '@/lib/utils';

interface SettingsSectionProps {
    title: string;
    description?: string;
    children: React.ReactNode;
    variant?: 'default' | 'danger';
}

export function SettingsSection({ title, description, children, variant = 'default' }: SettingsSectionProps) {
    return (
        <div className="bg-muted/55 border-border/80 rounded-2xl border p-0.75">
            <div className="flex items-center justify-between px-5 py-3 sm:px-6">
                <div className="space-y-1">
                    <h3 className={cn('text-sm font-semibold tracking-tight', variant === 'danger' ? 'text-destructive' : 'text-foreground')}>
                        {title}
                    </h3>
                    {description && <p className="text-muted-foreground max-w-2xl text-[13px] leading-5 text-pretty">{description}</p>}
                </div>
            </div>
            <div className="divide-border/80 bg-background ring-border/70 relative divide-y overflow-hidden rounded-xl shadow-xs ring-1">
                {children}
            </div>
        </div>
    );
}

interface SettingsFieldProps {
    label: string;
    description?: string;
    children: React.ReactNode;
    htmlFor?: string;
    stretch?: boolean;
}

export function SettingsField({ label, description, children, htmlFor, stretch = false }: SettingsFieldProps) {
    return (
        <div
            className={cn(
                'flex w-full flex-col gap-4 p-5 sm:px-6 lg:flex-row lg:justify-between lg:gap-x-12 lg:gap-y-8',
                stretch ? 'lg:items-stretch' : 'lg:items-start',
            )}
        >
            <label htmlFor={htmlFor} className="text-foreground max-w-[560px] shrink-0 text-sm leading-6 font-medium select-none lg:flex-1">
                {label}
                {description && (
                    <p className="text-muted-foreground mt-1 max-w-[560px] text-[13px] leading-5 font-normal text-pretty">{description}</p>
                )}
            </label>
            <div className="flex min-w-0 grow items-end justify-end lg:max-w-[320px]">{children}</div>
        </div>
    );
}
