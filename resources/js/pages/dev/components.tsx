import { AccentCard } from '@/components/ui/accent-card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { AppTopBar } from '@/components/app-top-bar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ButtonGroup } from '@/components/ui/button-group';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { CodeBlock } from '@/components/ui/code-block';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Field, FieldContent, FieldDescription, FieldError, FieldGroup, FieldLabel, FieldSeparator } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group';
import { Item, ItemActions, ItemContent, ItemDescription, ItemMedia, ItemTitle } from '@/components/ui/item';
import { Kbd, KbdGroup } from '@/components/ui/kbd';
import { Label } from '@/components/ui/label';
import { SectionCard, SectionCardContent, SectionCardDescription, SectionCardFooter, SectionCardHeader, SectionCardTitle } from '@/components/ui/section-card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SidebarNav } from '@/components/ui/sidebar-nav';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';
import { StepDialog, StepDialogBody, StepDialogFooter, StepDialogHeader } from '@/components/ui/step-dialog';
import { Switch } from '@/components/ui/switch';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { useAppearance } from '@/hooks/use-appearance';
import { Head } from '@inertiajs/react';
import { AwsLogo } from '@/components/icons/aws-logo';
import { ValkeyLogo } from '@/components/icons/valkey-logo';
import {
    SiAkamai,
    SiDigitalocean,
    SiDocker,
    SiGithub,
    SiGitlab,
    SiGrafana,
    SiHetzner,
    SiHelm,
    SiKubernetes,
    SiMysql,
    SiPostgresql,
    SiPrometheus,
    SiRedis,
    SiTerraform,
    SiVultr,
} from '@icons-pack/react-simple-icons';
import { AlertCircle, AppWindow, Bolt, Box, Cloud, Container, Copy, Database, FolderOpen, Globe, Heart, Loader2, Mail, Monitor, Moon, Network, Package, Rocket, Search, Server, Shield, SquareTerminal, Sun, Terminal, Trash2, Workflow } from 'lucide-react';
import { useState } from 'react';

const colorTokens = [
    { name: '--background', class: 'bg-background' },
    { name: '--foreground', class: 'bg-foreground' },
    { name: '--card', class: 'bg-card' },
    { name: '--primary', class: 'bg-primary' },
    { name: '--primary-foreground', class: 'bg-primary-foreground' },
    { name: '--secondary', class: 'bg-secondary' },
    { name: '--muted', class: 'bg-muted' },
    { name: '--accent', class: 'bg-accent' },
    { name: '--destructive', class: 'bg-destructive' },
    { name: '--border', class: 'bg-border' },
    { name: '--ring', class: 'bg-ring' },
];

const chartTokens = [
    { name: '--chart-1', class: 'bg-chart-1' },
    { name: '--chart-2', class: 'bg-chart-2' },
    { name: '--chart-3', class: 'bg-chart-3' },
    { name: '--chart-4', class: 'bg-chart-4' },
    { name: '--chart-5', class: 'bg-chart-5' },
];

const demoTabs = [
    { title: 'Dashboard', url: '/demo/dashboard' },
    { title: 'Applications', url: '/demo/applications' },
    { title: 'Resources', url: '/demo/resources' },
    { title: 'Clusters', url: '/demo/clusters' },
    { title: 'Settings', url: '/dev/components' },
];

const sidebarNavItems = [
    { title: 'General', url: '/dev/components' },
    { title: 'Security', url: '/dev/security' },
    { title: 'Members', url: '/dev/members' },
    { title: 'API Keys', url: '/dev/api-keys' },
];

const sampleYaml = `apiVersion: apps/v1
kind: Deployment
metadata:
  name: kuven-app
  labels:
    app: kuven
spec:
  replicas: 3
  selector:
    matchLabels:
      app: kuven`;

function Section({ title, description, children }: { title: string; description: string; children: React.ReactNode }) {
    return (
        <section className="space-y-4">
            <div>
                <h3 className="font-headline text-lg font-bold tracking-tight">{title}</h3>
                <p className="text-muted-foreground text-sm">{description}</p>
            </div>
            {children}
        </section>
    );
}

function FoundationTab() {
    return (
        <div className="space-y-12">
            <Section title="Colors" description="Design tokens from Stitch spec. Remove kuven-light/kuven-dark class to see shadcn defaults.">
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

            <Section title="Typography" description="Manrope for headlines, Inter for body text.">
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
                        <p className="text-base">Body text — The quick brown fox jumps over the lazy dog.</p>
                        <p className="text-sm font-medium">Body small medium — Labels and secondary content.</p>
                        <p className="text-muted-foreground text-sm">Body small muted — Descriptions and hints.</p>
                        <p className="text-xs font-medium uppercase tracking-wider">Label — All caps micro text</p>
                    </div>
                </div>
            </Section>

            <Section title="Keyboard Shortcuts" description="Kbd for displaying keyboard shortcuts.">
                <div className="flex items-center gap-6">
                    <KbdGroup><Kbd>⌘</Kbd><Kbd>K</Kbd></KbdGroup>
                    <KbdGroup><Kbd>Ctrl</Kbd><Kbd>Shift</Kbd><Kbd>P</Kbd></KbdGroup>
                    <Kbd>Esc</Kbd>
                </div>
            </Section>
        </div>
    );
}

function FormsTab() {
    return (
        <div className="space-y-12">
            <Section title="Field (Vertical)" description="Labels, inputs, descriptions, and errors in vertical layout.">
                <FieldGroup className="max-w-md">
                    <Field>
                        <FieldLabel htmlFor="field-name">Cluster name</FieldLabel>
                        <FieldContent>
                            <Input id="field-name" placeholder="my-cluster" />
                            <FieldDescription>A unique name for your cluster.</FieldDescription>
                        </FieldContent>
                    </Field>
                    <FieldSeparator className="-mx-6" />
                    <Field data-invalid>
                        <FieldLabel htmlFor="field-error">Region</FieldLabel>
                        <FieldContent>
                            <Input id="field-error" placeholder="eu-central-1" aria-invalid />
                            <FieldError>Region is required.</FieldError>
                        </FieldContent>
                    </Field>
                </FieldGroup>
            </Section>

            <Section title="Field (Horizontal)" description="Label and description on left, control on right.">
                <FieldGroup className="max-w-2xl">
                    <Field orientation="horizontal" className="gap-16">
                        <div className="flex-1">
                            <FieldLabel htmlFor="hz-name">Organization name</FieldLabel>
                            <FieldDescription>The display name for your organization.</FieldDescription>
                        </div>
                        <FieldContent className="w-64 shrink-0">
                            <Input id="hz-name" placeholder="Acme Corp" />
                        </FieldContent>
                    </Field>
                    <FieldSeparator className="-mx-6" />
                    <Field orientation="horizontal" className="gap-16">
                        <div className="flex-1">
                            <FieldLabel htmlFor="hz-switch">Maintenance mode</FieldLabel>
                            <FieldDescription>Temporarily disable all deployments.</FieldDescription>
                        </div>
                        <FieldContent className="w-64 shrink-0 items-end">
                            <Switch id="hz-switch" />
                        </FieldContent>
                    </Field>
                </FieldGroup>
            </Section>

            <Section title="Input Group" description="Inputs with prefixes, suffixes, and action buttons.">
                <div className="grid max-w-md gap-4">
                    <div className="grid gap-2">
                        <Label>With prefix</Label>
                        <InputGroup>
                            <InputGroupAddon>
                                <InputGroupText>kuven.app/</InputGroupText>
                            </InputGroupAddon>
                            <InputGroupInput placeholder="my-org" />
                        </InputGroup>
                    </div>
                    <div className="grid gap-2">
                        <Label>With icon</Label>
                        <InputGroup>
                            <InputGroupAddon>
                                <Search className="size-4 text-muted-foreground" />
                            </InputGroupAddon>
                            <InputGroupInput placeholder="Search clusters..." />
                        </InputGroup>
                    </div>
                    <div className="grid gap-2">
                        <Label>With copy button</Label>
                        <InputGroup>
                            <InputGroupInput value="org-d779a2f9-5931-11ef-b2c9-96973344" readOnly />
                            <InputGroupAddon align="inline-end">
                                <Button variant="ghost" size="sm" className="h-7 gap-1 px-2 text-xs">
                                    <Copy className="size-3" /> Copy
                                </Button>
                            </InputGroupAddon>
                        </InputGroup>
                    </div>
                </div>
            </Section>

            <Section title="Inputs" description="Default, error, disabled states.">
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
                    <div className="grid gap-2">
                        <Label htmlFor="textarea-default">Textarea</Label>
                        <Textarea id="textarea-default" placeholder="Enter a longer description..." />
                    </div>
                </div>
            </Section>

            <Section title="Select" description="Dropdown selection.">
                <div className="max-w-md">
                    <Select>
                        <SelectTrigger>
                            <SelectValue placeholder="Select a region" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="nuremberg">Nuremberg (eu-central)</SelectItem>
                            <SelectItem value="falkenstein">Falkenstein (eu-central)</SelectItem>
                            <SelectItem value="helsinki">Helsinki (eu-north)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </Section>

            <Section title="Checkbox & Switch" description="Toggle controls.">
                <div className="space-y-4 max-w-md">
                    <div className="flex items-center gap-2">
                        <Checkbox id="check-1" />
                        <Label htmlFor="check-1">Changes apply to new resource instantiations</Label>
                    </div>
                    <div className="flex items-center gap-2">
                        <Checkbox id="check-2" defaultChecked />
                        <Label htmlFor="check-2">Enable automatic backups</Label>
                    </div>
                    <div className="flex items-center gap-2">
                        <Switch id="switch-1" />
                        <Label htmlFor="switch-1">Enable notifications</Label>
                    </div>
                </div>
            </Section>
        </div>
    );
}

function DataDisplayTab() {
    return (
        <div className="space-y-12">
            <Section title="Section Card" description="Settings-style card with header, content area, and footer actions.">
                <SectionCard className="max-w-3xl">
                    <SectionCardHeader>
                        <SectionCardTitle>General Settings</SectionCardTitle>
                        <SectionCardDescription>Manage your organization's core identity and default configurations.</SectionCardDescription>
                    </SectionCardHeader>
                    <SectionCardContent>
                        <FieldGroup>
                            <Field orientation="horizontal" className="gap-16">
                                <div className="flex-1">
                                    <FieldLabel htmlFor="sc-name">Name</FieldLabel>
                                    <FieldDescription>The display name for your organization.</FieldDescription>
                                </div>
                                <FieldContent className="w-64 shrink-0">
                                    <Input id="sc-name" placeholder="Acme Corp" />
                                </FieldContent>
                            </Field>
                            <FieldSeparator className="-mx-6" />
                            <Field orientation="vertical">
                                <FieldLabel htmlFor="sc-desc">Description</FieldLabel>
                                <FieldContent>
                                    <Textarea id="sc-desc" placeholder="A brief description of your organization..." />
                                    <FieldDescription>Visible to all members.</FieldDescription>
                                </FieldContent>
                            </Field>
                        </FieldGroup>
                    </SectionCardContent>
                    <SectionCardFooter>
                        <Button variant="outline" size="sm">Cancel</Button>
                        <Button size="sm">Save changes</Button>
                    </SectionCardFooter>
                </SectionCard>
            </Section>

            <Section title="Accent Card" description="Cards with colored icon accents for danger zones and operations.">
                <div className="grid gap-4 max-w-2xl sm:grid-cols-2">
                    <AccentCard
                        variant="destructive"
                        icon={<Trash2 className="size-5" />}
                        title="Delete Organization"
                        description="Permanently delete this organization and all associated data."
                        action={<Button variant="destructive" size="sm">Terminate Workspaces</Button>}
                    />
                    <AccentCard
                        variant="warning"
                        icon={<Shield className="size-5" />}
                        title="Audit Infrastructure"
                        description="Review recent infrastructure changes and deployment logs."
                        action={<Button variant="outline" size="sm">View Logs</Button>}
                    />
                </div>
            </Section>

            <Section title="Cards" description="Content containers — feature cards, service cards, and data cards from the LP design.">
                <div className="space-y-8">
                    <div>
                        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Feature cards (LP style)</p>
                        <div className="grid gap-4 sm:grid-cols-3 max-w-3xl">
                            {[
                                { icon: <Server className="size-6" />, title: 'Universal Runtime', desc: 'Deploy any stack seamlessly. Our engine normalizes the environment for peak efficiency.' },
                                { icon: <Cloud className="size-6" />, title: 'Automated Kube-ification', desc: "Don't write another line of YAML. Kuven translates your intent into production-grade manifests." },
                                { icon: <Bolt className="size-6" />, title: 'One-Click Self-Service', desc: 'Provision infra, databases, and monitoring in seconds through an intuitive marketplace.' },
                            ].map((f) => (
                                <div key={f.title} className="group relative overflow-hidden rounded-xl bg-secondary p-6 transition-all duration-300 hover:bg-accent">
                                    <div className="mb-4 inline-flex size-8 items-center justify-center rounded-md bg-primary/10 text-primary">
                                        {f.icon}
                                    </div>
                                    <h3 className="font-headline text-base font-bold mb-2">{f.title}</h3>
                                    <p className="text-muted-foreground text-sm leading-relaxed">{f.desc}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                    <div>
                        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Service cards (LP catalog style)</p>
                        <div className="grid gap-4 sm:grid-cols-2 max-w-2xl">
                            {[
                                { icon: <Package className="size-4" />, badge: 'Running', badgeColor: 'bg-emerald-500/10 text-emerald-500', name: 'auth-service', meta: 'Laravel • us-east-1', progress: 94 },
                                { icon: <Package className="size-4" />, badge: 'Degraded', badgeColor: 'bg-amber-500/10 text-amber-500', name: 'frontend-portal', meta: 'Next.js • global-edge', progress: 88 },
                                { icon: <Server className="size-4" />, badge: 'Failed', badgeColor: 'bg-destructive/10 text-destructive', name: 'Postgres 18', meta: 'RDS • us-east-1' },
                                { icon: <Bolt className="size-4" />, badge: 'Provisioning', badgeColor: 'bg-primary/10 text-primary', name: 'Redis Valkey', meta: 'Cache • us-east-1' },
                            ].map((s) => (
                                <div key={s.name} className="rounded-lg bg-secondary p-4 transition-all hover:border-primary/20 border border-transparent">
                                    <div className="flex items-start justify-between mb-3">
                                        <div className="flex size-8 items-center justify-center rounded bg-primary/10 text-primary">{s.icon}</div>
                                        <span className={`rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-tighter ${s.badgeColor}`}>{s.badge}</span>
                                    </div>
                                    <h4 className="font-headline text-sm font-bold mb-0.5">{s.name}</h4>
                                    <p className="text-muted-foreground text-[10px] mb-3">{s.meta}</p>
                                    {s.progress && (
                                        <div className="h-1 w-full overflow-hidden rounded-full bg-accent">
                                            <div className="h-full rounded-full bg-primary" style={{ width: `${s.progress}%` }} />
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                    <div>
                        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Data card</p>
                        <div className="grid gap-4 sm:grid-cols-2 max-w-2xl">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm font-semibold">Cluster Overview</CardTitle>
                                    <CardDescription>3 nodes running in nuremberg</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between"><span className="text-muted-foreground">Status</span><Badge variant="secondary">Healthy</Badge></div>
                                        <div className="flex justify-between"><span className="text-muted-foreground">Uptime</span><span className="font-medium">99.9%</span></div>
                                    </div>
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm font-semibold">Resource Usage</CardTitle>
                                    <CardDescription>Current allocation</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2 text-sm">
                                        <div className="flex justify-between"><span className="text-muted-foreground">CPU</span><span className="font-medium">42%</span></div>
                                        <div className="flex justify-between"><span className="text-muted-foreground">Memory</span><span className="font-medium">67%</span></div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title="Code Block" description="Syntax-highlighted code with optional label and copy button.">
                <CodeBlock label="deployment manifest (.yaml)" copyable code={sampleYaml} className="max-w-2xl" />
            </Section>

            <Section title="Item" description="Versatile list items with media, content, and actions.">
                <div className="grid gap-8 max-w-lg">
                    <div>
                        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Stacked</p>
                        <div className="overflow-hidden rounded-lg border border-border">
                            <Item className="rounded-none border-0">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>web-1</ItemTitle>
                                    <ItemDescription>cx31 — nuremberg — running</ItemDescription>
                                </ItemContent>
                                <ItemActions><Badge variant="secondary">Healthy</Badge></ItemActions>
                            </Item>
                            <hr className="border-border" />
                            <Item className="rounded-none border-0">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>web-2</ItemTitle>
                                    <ItemDescription>cx31 — nuremberg — running</ItemDescription>
                                </ItemContent>
                                <ItemActions><Badge variant="destructive">Degraded</Badge></ItemActions>
                            </Item>
                            <hr className="border-border" />
                            <Item className="rounded-none border-0">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>worker-1</ItemTitle>
                                    <ItemDescription>cx41 — falkenstein — starting</ItemDescription>
                                </ItemContent>
                                <ItemActions><Spinner className="size-4" /></ItemActions>
                            </Item>
                        </div>
                    </div>
                    <div>
                        <p className="text-muted-foreground mb-3 text-xs font-medium uppercase tracking-wider">Spaced</p>
                        <div className="space-y-2">
                            <Item variant="outline">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>web-1</ItemTitle>
                                    <ItemDescription>cx31 — nuremberg — running</ItemDescription>
                                </ItemContent>
                                <ItemActions><Badge variant="secondary">Healthy</Badge></ItemActions>
                            </Item>
                            <Item variant="outline">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>web-2</ItemTitle>
                                    <ItemDescription>cx31 — nuremberg — running</ItemDescription>
                                </ItemContent>
                                <ItemActions><Badge variant="destructive">Degraded</Badge></ItemActions>
                            </Item>
                            <Item variant="outline">
                                <ItemMedia variant="icon"><Server className="size-4" /></ItemMedia>
                                <ItemContent>
                                    <ItemTitle>worker-1</ItemTitle>
                                    <ItemDescription>cx41 — falkenstein — starting</ItemDescription>
                                </ItemContent>
                                <ItemActions><Spinner className="size-4" /></ItemActions>
                            </Item>
                        </div>
                    </div>
                </div>
            </Section>

            <Section title="Table" description="Structured data display.">
                <div className="max-w-2xl rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Name</TableHead>
                                <TableHead>Region</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Nodes</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow>
                                <TableCell className="font-medium">production</TableCell>
                                <TableCell>nuremberg</TableCell>
                                <TableCell><Badge variant="secondary">Healthy</Badge></TableCell>
                                <TableCell className="text-right">6</TableCell>
                            </TableRow>
                            <TableRow>
                                <TableCell className="font-medium">staging</TableCell>
                                <TableCell>falkenstein</TableCell>
                                <TableCell><Badge variant="secondary">Healthy</Badge></TableCell>
                                <TableCell className="text-right">3</TableCell>
                            </TableRow>
                            <TableRow>
                                <TableCell className="font-medium">dev</TableCell>
                                <TableCell>helsinki</TableCell>
                                <TableCell><Badge variant="destructive">Failed</Badge></TableCell>
                                <TableCell className="text-right">1</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Section>

            <Section title="Empty State" description="Placeholder for pages with no data.">
                <div className="max-w-lg">
                    <Empty className="rounded-lg border border-dashed border-border py-12">
                        <EmptyHeader>
                            <EmptyMedia variant="icon"><FolderOpen className="size-6" /></EmptyMedia>
                            <EmptyTitle>No clusters</EmptyTitle>
                            <EmptyDescription>You haven't created any clusters yet. Create your first cluster to get started.</EmptyDescription>
                        </EmptyHeader>
                        <EmptyContent>
                            <Button><Cloud className="size-4 mr-1" /> Create cluster</Button>
                        </EmptyContent>
                    </Empty>
                </div>
            </Section>

            <Section title="Badges" description="Status indicators and labels.">
                <div className="flex flex-wrap gap-3">
                    <Badge>Default</Badge>
                    <Badge variant="secondary">Secondary</Badge>
                    <Badge variant="destructive">Destructive</Badge>
                    <Badge variant="outline">Outline</Badge>
                </div>
            </Section>

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

            <Section title="Skeleton" description="Loading placeholders.">
                <div className="flex items-center gap-4">
                    <Skeleton className="h-12 w-12 rounded-full" />
                    <div className="space-y-2">
                        <Skeleton className="h-4 w-48" />
                        <Skeleton className="h-4 w-32" />
                    </div>
                </div>
            </Section>
        </div>
    );
}

function NavigationTab() {
    return (
        <div className="space-y-12">
            <Section title="App Header" description="Glassmorphic top bar with brand, org switcher, search, notifications, and nav tabs.">
                <div className="border-border overflow-hidden rounded-lg border">
                    <AppTopBar tabs={demoTabs} />
                </div>
            </Section>

            <Section title="Sidebar Nav" description="Vertical navigation with active state for settings and sub-pages.">
                <div className="max-w-xs">
                    <SidebarNav title="Settings" items={sidebarNavItems} currentPath="/dev/components" />
                </div>
            </Section>

            <Section title="Tabs" description="Tab navigation for content switching.">
                <Tabs defaultValue="overview" className="max-w-lg">
                    <TabsList>
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="nodes">Nodes</TabsTrigger>
                        <TabsTrigger value="logs">Logs</TabsTrigger>
                    </TabsList>
                    <TabsContent value="overview" className="mt-4">
                        <p className="text-muted-foreground text-sm">Cluster overview content goes here.</p>
                    </TabsContent>
                    <TabsContent value="nodes" className="mt-4">
                        <p className="text-muted-foreground text-sm">Node list content goes here.</p>
                    </TabsContent>
                    <TabsContent value="logs" className="mt-4">
                        <p className="text-muted-foreground text-sm">Log output content goes here.</p>
                    </TabsContent>
                </Tabs>
            </Section>
        </div>
    );
}

function FeedbackTab() {
    return (
        <div className="space-y-12">
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

            <Section title="Spinner" description="Loading indicators.">
                <div className="flex items-center gap-6">
                    <Spinner className="size-4" />
                    <Spinner className="size-6" />
                    <Spinner className="size-8" />
                    <Button disabled><Spinner className="size-4" /> Loading...</Button>
                </div>
            </Section>

            <Section title="Tooltips" description="Contextual hints on hover.">
                <TooltipProvider>
                    <div className="flex gap-4">
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="outline" size="icon"><Mail className="h-4 w-4" /></Button>
                            </TooltipTrigger>
                            <TooltipContent><p>Send notification</p></TooltipContent>
                        </Tooltip>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="outline">Hover me</Button>
                            </TooltipTrigger>
                            <TooltipContent><p>This is a tooltip</p></TooltipContent>
                        </Tooltip>
                    </div>
                </TooltipProvider>
            </Section>
        </div>
    );
}

function OverlaysTab() {
    const [stepDialogOpen, setStepDialogOpen] = useState(false);
    const [dialogStep, setDialogStep] = useState<'select' | 'form'>('select');
    const [selectedProvider, setSelectedProvider] = useState<string>('');

    const providers = [
        { name: 'Hetzner', icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
        { name: 'DigitalOcean', icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
        { name: 'Vultr', icon: <SiVultr className="size-[18px]" />, bg: 'bg-[#007bfc]', color: 'text-white' },
        { name: 'Akamai', icon: <SiAkamai className="size-[18px]" />, bg: 'bg-[#0096d6]', color: 'text-white' },
        { name: 'AWS', icon: <AwsLogo className="size-[18px]" />, bg: 'bg-[#232f3e]', color: 'text-[#ff9900]' },
    ];

    const openDialog = () => {
        setDialogStep('select');
        setSelectedProvider('');
        setStepDialogOpen(true);
    };

    const selectProvider = (name: string) => {
        setSelectedProvider(name);
        setDialogStep('form');
    };

    const selected = providers.find((p) => p.name === selectedProvider);

    return (
        <div className="space-y-12">
            <Section title="Step Dialog" description="Multi-step modal — select a provider, then fill the form. Forge-inspired cloud provider flow.">
                <Button onClick={openDialog}>Add cloud provider</Button>

                <StepDialog open={stepDialogOpen} onOpenChange={setStepDialogOpen}>
                    {dialogStep === 'select' ? (
                        <>
                            <StepDialogHeader
                                title="New cloud provider"
                                description="Connect a cloud provider to deploy clusters. Infrastructure costs are billed directly via your provider account."
                            />
                            <StepDialogBody>
                                <div className="space-y-2">
                                    {providers.map((provider) => (
                                        <button
                                            key={provider.name}
                                            onClick={() => selectProvider(provider.name)}
                                            className="flex w-full items-center gap-3 rounded-lg border border-border px-4 py-3 text-left transition-colors hover:bg-accent"
                                        >
                                            <div className={`flex size-8 items-center justify-center rounded-md ${provider.bg} ${provider.color}`}>
                                                {provider.icon}
                                            </div>
                                            <span className="text-sm font-medium">{provider.name}</span>
                                        </button>
                                    ))}
                                </div>
                            </StepDialogBody>
                        </>
                    ) : (
                        <>
                            <StepDialogHeader
                                title={selectedProvider}
                                description={`Connect your ${selectedProvider} account to deploy servers.`}
                                onBack={() => setDialogStep('select')}
                            />
                            {selected && (
                                <StepDialogBody>
                                    <div className="mb-6 flex items-center gap-3">
                                        <div className={`flex size-8 items-center justify-center rounded-md ${selected.bg} ${selected.color}`}>
                                            {selected.icon}
                                        </div>
                                        <span className="text-sm font-medium">{selected.name}</span>
                                    </div>
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="provider-name">Profile name</Label>
                                            <Input id="provider-name" placeholder="Production" autoComplete="off" />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="provider-token">API Token</Label>
                                            <Input id="provider-token" type="password" placeholder={`Enter your ${selectedProvider} API token`} autoComplete="new-password" />
                                        </div>
                                    </div>
                                </StepDialogBody>
                            )}
                            <StepDialogFooter>
                                <Button className="w-full">Add provider</Button>
                            </StepDialogFooter>
                        </>
                    )}
                </StepDialog>
            </Section>

            <Section title="Command" description="Command palette for search and actions.">
                <Command className="max-w-md rounded-lg border">
                    <CommandInput placeholder="Search clusters, providers, settings..." />
                    <CommandList>
                        <CommandEmpty>No results found.</CommandEmpty>
                        <CommandGroup heading="Clusters">
                            <CommandItem><Server className="mr-2 size-4" /> production</CommandItem>
                            <CommandItem><Server className="mr-2 size-4" /> staging</CommandItem>
                        </CommandGroup>
                        <CommandGroup heading="Actions">
                            <CommandItem><Package className="mr-2 size-4" /> Create cluster</CommandItem>
                            <CommandItem><Bolt className="mr-2 size-4" /> Add provider</CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
            </Section>
        </div>
    );
}

function ActionsTab() {
    return (
        <div className="space-y-12">
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

            <Section title="Button Group" description="Related buttons grouped together.">
                <div className="space-y-4">
                    <ButtonGroup>
                        <Button variant="outline">Overview</Button>
                        <Button variant="outline">Nodes</Button>
                        <Button variant="outline">Logs</Button>
                    </ButtonGroup>
                </div>
            </Section>
        </div>
    );
}

const iconGroups = [
    {
        label: 'Infrastructure',
        icons: [
            { icon: <Server className="size-5" />, name: 'Server' },
            { icon: <Database className="size-5" />, name: 'Database' },
            { icon: <Network className="size-5" />, name: 'Kubernetes Cluster' },
            { icon: <Cloud className="size-5" />, name: 'Cloud Provider' },
            { icon: <Shield className="size-5" />, name: 'Firewall' },
            { icon: <Globe className="size-5" />, name: 'Network' },
        ],
    },
    {
        label: 'Applications',
        icons: [
            { icon: <AppWindow className="size-5" />, name: 'Web Application' },
            { icon: <SquareTerminal className="size-5" />, name: 'Console Application' },
            { icon: <Rocket className="size-5" />, name: 'Deployment' },
            { icon: <Package className="size-5" />, name: 'Service' },
            { icon: <Container className="size-5" />, name: 'Container' },
            { icon: <Workflow className="size-5" />, name: 'Pipeline' },
        ],
    },
    {
        label: 'Data & Cache',
        icons: [
            { icon: <Database className="size-5" />, name: 'PostgreSQL' },
            { icon: <Database className="size-5" />, name: 'MySQL' },
            { icon: <Bolt className="size-5" />, name: 'Redis' },
            { icon: <Bolt className="size-5" />, name: 'Valkey' },
        ],
    },
    {
        label: 'Services',
        icons: [
            { icon: <Box className="size-5" />, name: 'Object Storage' },
            { icon: <Container className="size-5" />, name: 'Container Registry' },
            { icon: <Package className="size-5" />, name: 'Package Registry' },
        ],
    },
    {
        label: 'Status',
        icons: [
            { icon: <div className="size-2.5 rounded-full bg-emerald-500" />, name: 'Healthy / Running' },
            { icon: <div className="size-2.5 rounded-full bg-amber-500" />, name: 'Degraded / Warning' },
            { icon: <div className="size-2.5 rounded-full bg-destructive" />, name: 'Failed / Error' },
            { icon: <div className="size-2.5 rounded-full bg-primary" />, name: 'Provisioning' },
            { icon: <div className="size-2.5 rounded-full bg-muted-foreground" />, name: 'Unknown / Off' },
        ],
    },
];

function IconsTab() {
    return (
        <div className="space-y-12">
            {iconGroups.map((group) => (
                <Section key={group.label} title={group.label} description={`Icon mappings for ${group.label.toLowerCase()} concepts.`}>
                    <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6 max-w-3xl">
                        {group.icons.map((item) => (
                            <div key={item.name} className="flex flex-col items-center gap-2 rounded-lg border border-border p-4 text-center">
                                <div className="text-muted-foreground flex size-10 items-center justify-center">
                                    {item.icon}
                                </div>
                                <span className="text-muted-foreground text-[10px] leading-tight">{item.name}</span>
                            </div>
                        ))}
                    </div>
                </Section>
            ))}
        </div>
    );
}

const logoGroups = [
    {
        label: 'Cloud Providers',
        logos: [
            { icon: <SiHetzner className="size-[18px]" />, name: 'Hetzner', bg: 'bg-[#d50c2d]', color: 'text-white' },
            { icon: <SiDigitalocean className="size-[18px]" />, name: 'DigitalOcean', bg: 'bg-[#0080ff]', color: 'text-white' },
            { icon: <AwsLogo className="size-[18px]" />, name: 'AWS', bg: 'bg-[#232f3e]', color: 'text-[#ff9900]' },
            { icon: <SiVultr className="size-[18px]" />, name: 'Vultr', bg: 'bg-[#007bfc]', color: 'text-white' },
            { icon: <SiAkamai className="size-[18px]" />, name: 'Akamai', bg: 'bg-[#0096d6]', color: 'text-white' },
        ],
    },
    {
        label: 'Container & Orchestration',
        logos: [
            { icon: <SiKubernetes className="size-[18px]" />, name: 'Kubernetes', bg: 'bg-[#326ce5]', color: 'text-white' },
            { icon: <SiDocker className="size-[18px]" />, name: 'Docker', bg: 'bg-[#2496ed]', color: 'text-white' },
            { icon: <SiHelm className="size-[18px]" />, name: 'Helm', bg: 'bg-[#0f1689]', color: 'text-white' },
            { icon: <SiTerraform className="size-[18px]" />, name: 'Terraform', bg: 'bg-[#7b42bc]', color: 'text-white' },
        ],
    },
    {
        label: 'Databases & Cache',
        logos: [
            { icon: <SiPostgresql className="size-[18px]" />, name: 'PostgreSQL', bg: 'bg-[#4169e1]', color: 'text-white' },
            { icon: <SiMysql className="size-[18px]" />, name: 'MySQL', bg: 'bg-[#4479a1]', color: 'text-white' },
            { icon: <SiRedis className="size-[18px]" />, name: 'Redis', bg: 'bg-[#dc382d]', color: 'text-white' },
            { icon: <ValkeyLogo className="size-[18px]" />, name: 'Valkey', bg: 'bg-[#6983ff]', color: 'text-white' },
        ],
    },
    {
        label: 'Monitoring & CI/CD',
        logos: [
            { icon: <SiPrometheus className="size-[18px]" />, name: 'Prometheus', bg: 'bg-[#e6522c]', color: 'text-white' },
            { icon: <SiGrafana className="size-[18px]" />, name: 'Grafana', bg: 'bg-[#f46800]', color: 'text-white' },
            { icon: <SiGithub className="size-[18px]" />, name: 'GitHub', bg: 'bg-[#181717]', color: 'text-white' },
            { icon: <SiGitlab className="size-[18px]" />, name: 'GitLab', bg: 'bg-[#fc6d26]', color: 'text-white' },
        ],
    },
];

function LogosTab() {
    return (
        <div className="space-y-12">
            {logoGroups.map((group) => (
                <Section key={group.label} title={group.label} description={`Brand logos for ${group.label.toLowerCase()}.`}>
                    <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-6 max-w-3xl">
                        {group.logos.map((item) => (
                            <div key={item.name} className="flex flex-col items-center gap-3 rounded-lg border border-border p-4 text-center">
                                <div className={`flex size-8 items-center justify-center rounded-md ${item.bg} ${item.color}`}>
                                    {item.icon}
                                </div>
                                <span className="text-muted-foreground text-[10px] leading-tight">{item.name}</span>
                            </div>
                        ))}
                    </div>
                </Section>
            ))}
        </div>
    );
}

export default function Components() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <div className="bg-background text-foreground min-h-screen">
            <Head title="Component Catalogue" />

            <div className="mx-auto max-w-5xl px-6 py-12">
                {/* Page header */}
                <div className="flex items-center justify-between mb-8">
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

                {/* Tabbed content */}
                <Tabs defaultValue="foundation">
                    <TabsList className="mb-8">
                        <TabsTrigger value="foundation">Foundation</TabsTrigger>
                        <TabsTrigger value="forms">Forms</TabsTrigger>
                        <TabsTrigger value="data-display">Data Display</TabsTrigger>
                        <TabsTrigger value="navigation">Navigation</TabsTrigger>
                        <TabsTrigger value="feedback">Feedback</TabsTrigger>
                        <TabsTrigger value="overlays">Overlays</TabsTrigger>
                        <TabsTrigger value="actions">Actions</TabsTrigger>
                        <TabsTrigger value="icons">Icons</TabsTrigger>
                        <TabsTrigger value="logos">Logos</TabsTrigger>
                    </TabsList>

                    <TabsContent value="foundation"><FoundationTab /></TabsContent>
                    <TabsContent value="forms"><FormsTab /></TabsContent>
                    <TabsContent value="data-display"><DataDisplayTab /></TabsContent>
                    <TabsContent value="navigation"><NavigationTab /></TabsContent>
                    <TabsContent value="feedback"><FeedbackTab /></TabsContent>
                    <TabsContent value="overlays"><OverlaysTab /></TabsContent>
                    <TabsContent value="actions"><ActionsTab /></TabsContent>
                    <TabsContent value="icons"><IconsTab /></TabsContent>
                    <TabsContent value="logos"><LogosTab /></TabsContent>
                </Tabs>

                {/* Footer */}
                <div className="border-border text-muted-foreground mt-16 border-t pt-8 text-center text-xs">
                    Kuven Design System — Theme: <code className="bg-muted rounded px-1 py-0.5 font-mono">{appearance}</code>
                </div>
            </div>
        </div>
    );
}
