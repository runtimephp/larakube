import { AppTopBar } from '@/components/app-top-bar';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useAppearance } from '@/hooks/use-appearance';
import { Head } from '@inertiajs/react';
import { AlertCircle, Heart, Loader2, Mail, Monitor, Moon, Sun, Terminal } from 'lucide-react';

const demoTabs = [
    { title: 'Dashboard', url: '/demo/dashboard' },
    { title: 'Applications', url: '/demo/applications' },
    { title: 'Resources', url: '/demo/resources' },
    { title: 'Clusters', url: '/demo/clusters' },
    { title: 'Settings', url: '/dev/components' },
];

const colorTokens = [
    { name: '--background', class: 'bg-background' },
    { name: '--foreground', class: 'bg-foreground' },
    { name: '--card', class: 'bg-card' },
    { name: '--primary', class: 'bg-primary' },
    { name: '--primary-foreground', class: 'bg-primary-foreground' },
    { name: '--secondary', class: 'bg-secondary' },
    { name: '--muted', class: 'bg-muted' },
    { name: '--muted-foreground', class: 'bg-muted-foreground' },
    { name: '--accent', class: 'bg-accent' },
    { name: '--destructive', class: 'bg-destructive' },
    { name: '--border', class: 'bg-border' },
    { name: '--input', class: 'bg-input' },
    { name: '--ring', class: 'bg-ring' },
];

const chartTokens = [
    { name: '--chart-1', class: 'bg-chart-1' },
    { name: '--chart-2', class: 'bg-chart-2' },
    { name: '--chart-3', class: 'bg-chart-3' },
    { name: '--chart-4', class: 'bg-chart-4' },
    { name: '--chart-5', class: 'bg-chart-5' },
];

function Section({ title, description, children }: { title: string; description: string; children: React.ReactNode }) {
    return (
        <section className="space-y-4">
            <div>
                <h2 className="font-headline text-2xl font-bold tracking-tight">{title}</h2>
                <p className="text-muted-foreground text-sm">{description}</p>
            </div>
            {children}
        </section>
    );
}

export default function Components() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <div className="bg-background text-foreground min-h-screen">
            <Head title="Component Catalogue" />

            <div className="mx-auto max-w-5xl space-y-16 px-6 py-12">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="font-headline text-4xl font-extrabold tracking-tight">Kuven Design System</h1>
                        <p className="text-muted-foreground mt-1">Component catalogue — dev only</p>
                    </div>
                    <div className="inline-flex gap-1 rounded-lg bg-secondary p-1">
                        {[
                            { value: 'light' as const, icon: Sun, label: 'Light' },
                            { value: 'dark' as const, icon: Moon, label: 'Dark' },
                            { value: 'system' as const, icon: Monitor, label: 'System' },
                        ].map(({ value, icon: Icon, label }) => (
                            <button
                                key={value}
                                onClick={() => updateAppearance(value)}
                                className={`flex items-center rounded-md px-3.5 py-1.5 text-sm transition-colors ${
                                    appearance === value
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                <Icon className="-ml-1 h-4 w-4" />
                                <span className="ml-1.5">{label}</span>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Header */}
                <Section title="App Header" description="Glassmorphic top bar with brand, org switcher, search, notifications, and nav tabs.">
                    <div className="border-border overflow-hidden rounded-lg border">
                        <AppTopBar tabs={demoTabs} />
                    </div>
                </Section>

                {/* Colors */}
                <Section title="Colors" description="Core design tokens mapped from Stitch spec. Remove kuven-light/kuven-dark class to see shadcn defaults.">
                    <div className="grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-8">
                        {colorTokens.map((token) => (
                            <div key={token.name} className="space-y-1.5">
                                <div className={`${token.class} h-12 rounded-md border border-border`} />
                                <p className="text-muted-foreground truncate text-[10px] font-mono">{token.name}</p>
                            </div>
                        ))}
                    </div>
                    <div>
                        <p className="text-muted-foreground mb-2 text-xs font-medium uppercase tracking-wider">Chart</p>
                        <div className="flex gap-2">
                            {chartTokens.map((token) => (
                                <div key={token.name} className="space-y-1.5">
                                    <div className={`${token.class} h-8 w-16 rounded-md`} />
                                    <p className="text-muted-foreground text-[10px] font-mono">{token.name}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </Section>

                {/* Typography */}
                <Section title="Typography" description="Manrope for headlines, Inter for body text. Hierarchy via weight and size contrast.">
                    <div className="space-y-6">
                        <div className="space-y-3">
                            <p className="text-muted-foreground text-xs font-medium uppercase tracking-wider">Headlines — Manrope</p>
                            <h1 className="font-headline text-5xl font-extrabold tracking-tight">Display Large</h1>
                            <h2 className="font-headline text-3xl font-bold tracking-tight">Headline Medium</h2>
                            <h3 className="font-headline text-xl font-bold">Headline Small</h3>
                            <h4 className="font-headline text-lg font-bold">Title</h4>
                        </div>
                        <div className="space-y-3">
                            <p className="text-muted-foreground text-xs font-medium uppercase tracking-wider">Body — Inter</p>
                            <p className="text-base">Body text — The quick brown fox jumps over the lazy dog. Regular weight for comfortable reading.</p>
                            <p className="text-sm font-medium">Body small medium — Used for labels and secondary content with emphasis.</p>
                            <p className="text-muted-foreground text-sm">Body small muted — De-emphasized supporting text for descriptions and hints.</p>
                            <p className="text-xs font-medium uppercase tracking-wider">Label — All caps micro text</p>
                        </div>
                    </div>
                </Section>

                {/* Buttons */}
                <Section title="Buttons" description="All variant and size combinations.">
                    <div className="space-y-6">
                        <div>
                            <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Variants</p>
                            <div className="flex flex-wrap items-center gap-3">
                                <Button>Default</Button>
                                <Button variant="secondary">Secondary</Button>
                                <Button variant="destructive">Destructive</Button>
                                <Button variant="outline">Outline</Button>
                                <Button variant="ghost">Ghost</Button>
                                <Button variant="link">Link</Button>
                            </div>
                        </div>
                        <div>
                            <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Sizes</p>
                            <div className="flex flex-wrap items-center gap-3">
                                <Button size="lg">Large</Button>
                                <Button>Default</Button>
                                <Button size="sm">Small</Button>
                                <Button size="icon"><Heart className="h-4 w-4" /></Button>
                            </div>
                        </div>
                        <div>
                            <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">States</p>
                            <div className="flex flex-wrap items-center gap-3">
                                <Button disabled>Disabled</Button>
                                <Button disabled><Loader2 className="h-4 w-4 animate-spin" /> Loading</Button>
                            </div>
                        </div>
                    </div>
                </Section>

                {/* Inputs */}
                <Section title="Inputs" description="Form fields with default, error, and disabled states.">
                    <div className="grid max-w-md gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="default-input">Default</Label>
                            <Input id="default-input" placeholder="Enter text..." />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="error-input">With error</Label>
                            <Input id="error-input" placeholder="Invalid value" className="border-destructive" />
                            <p className="text-destructive text-sm">This field is required.</p>
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="disabled-input">Disabled</Label>
                            <Input id="disabled-input" placeholder="Cannot edit" disabled />
                        </div>
                    </div>
                </Section>

                {/* Badges */}
                <Section title="Badges" description="Status indicators and labels.">
                    <div className="flex flex-wrap gap-3">
                        <Badge>Default</Badge>
                        <Badge variant="secondary">Secondary</Badge>
                        <Badge variant="destructive">Destructive</Badge>
                        <Badge variant="outline">Outline</Badge>
                    </div>
                </Section>

                {/* Cards */}
                <Section title="Cards" description="Content containers with surface hierarchy.">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Cluster Overview</CardTitle>
                                <CardDescription>3 nodes running in nuremberg region</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <p className="text-muted-foreground text-sm">All systems operational. Last health check 2 minutes ago.</p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardTitle>Resource Usage</CardTitle>
                                <CardDescription>Current allocation across worker nodes</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2 text-sm">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">CPU</span>
                                        <span className="font-medium">42%</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Memory</span>
                                        <span className="font-medium">67%</span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </Section>

                {/* Alerts */}
                <Section title="Alerts" description="Informational and error messages.">
                    <div className="grid gap-4 max-w-lg">
                        <Alert>
                            <Terminal className="h-4 w-4" />
                            <AlertTitle>Heads up!</AlertTitle>
                            <AlertDescription>Your cluster upgrade to v1.30 is scheduled for tonight at 02:00 UTC.</AlertDescription>
                        </Alert>
                        <Alert variant="destructive">
                            <AlertCircle className="h-4 w-4" />
                            <AlertTitle>Error</AlertTitle>
                            <AlertDescription>Node pool scaling failed. Check your cloud provider quota.</AlertDescription>
                        </Alert>
                    </div>
                </Section>

                {/* Avatars */}
                <Section title="Avatars" description="User and organization identity.">
                    <div className="flex items-center gap-4">
                        <Avatar className="h-12 w-12">
                            <AvatarImage src="https://github.com/shadcn.png" alt="User" />
                            <AvatarFallback>CN</AvatarFallback>
                        </Avatar>
                        <Avatar className="h-10 w-10">
                            <AvatarFallback className="bg-primary text-primary-foreground">FB</AvatarFallback>
                        </Avatar>
                        <Avatar className="h-8 w-8">
                            <AvatarFallback className="bg-secondary text-secondary-foreground text-xs">K</AvatarFallback>
                        </Avatar>
                    </div>
                </Section>

                {/* Skeleton */}
                <Section title="Skeleton" description="Loading placeholders for content.">
                    <div className="flex items-center gap-4">
                        <Skeleton className="h-12 w-12 rounded-full" />
                        <div className="space-y-2">
                            <Skeleton className="h-4 w-48" />
                            <Skeleton className="h-4 w-32" />
                        </div>
                    </div>
                </Section>

                {/* Tooltips */}
                <Section title="Tooltips" description="Contextual hints on hover.">
                    <TooltipProvider>
                        <div className="flex gap-4">
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button variant="outline" size="icon">
                                        <Mail className="h-4 w-4" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Send notification</p>
                                </TooltipContent>
                            </Tooltip>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button variant="outline">Hover me</Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>This is a tooltip with more detail</p>
                                </TooltipContent>
                            </Tooltip>
                        </div>
                    </TooltipProvider>
                </Section>

                {/* Footer */}
                <div className="border-border text-muted-foreground border-t pt-8 text-center text-xs">
                    Kuven Design System — Theme: <code className="bg-muted rounded px-1 py-0.5 font-mono">{appearance}</code>
                </div>
            </div>
        </div>
    );
}
