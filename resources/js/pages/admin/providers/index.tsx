import { store as storeProvider } from '@/actions/App/Http/Controllers/Admin/ProviderController';
import { AwsLogo } from '@/components/icons/aws-logo';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Empty, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { StepDialog, StepDialogBody, StepDialogFooter, StepDialogHeader } from '@/components/ui/step-dialog';
import AdminSettingsLayout from '@/layouts/admin-settings-layout';
import AppLayout from '@/layouts/app-layout';
import { type Provider } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { SiAkamai, SiDigitalocean, SiDocker, SiHetzner, SiVultr } from '@icons-pack/react-simple-icons';
import { Cloud, KeyRound, LoaderCircle, Plus } from 'lucide-react';
import { useState } from 'react';

interface AvailableSlug {
    value: string;
    label: string;
}

interface ProvidersPageProps {
    providers: Provider[];
    availableSlugs: AvailableSlug[];
    can: {
        create: boolean;
    };
}

const adminTabs = [
    { title: 'Management Clusters', url: '/admin/management-clusters' },
    { title: 'Settings', url: '/admin/settings/providers' },
];

const PROVIDER_CONFIG: Record<string, { icon: React.ReactNode; bg: string; color: string }> = {
    hetzner: { icon: <SiHetzner className="size-[18px]" />, bg: 'bg-[#d50c2d]', color: 'text-white' },
    digital_ocean: { icon: <SiDigitalocean className="size-[18px]" />, bg: 'bg-[#0080ff]', color: 'text-white' },
    aws: { icon: <AwsLogo className="size-[18px]" />, bg: 'bg-[#232f3e]', color: 'text-[#ff9900]' },
    vultr: { icon: <SiVultr className="size-[18px]" />, bg: 'bg-[#007bfc]', color: 'text-white' },
    akamai: { icon: <SiAkamai className="size-[18px]" />, bg: 'bg-[#0096d6]', color: 'text-white' },
    docker: { icon: <SiDocker className="size-[18px]" />, bg: 'bg-[#2496ed]', color: 'text-white' },
};

function ProviderLogo({ slug }: { slug: string }) {
    const config = PROVIDER_CONFIG[slug];

    if (!config) {
        return (
            <div className="bg-muted flex size-9 shrink-0 items-center justify-center rounded-lg">
                <Cloud className="text-muted-foreground size-4" />
            </div>
        );
    }

    return (
        <div className={`flex size-9 shrink-0 items-center justify-center rounded-lg ${config.bg} ${config.color}`}>
            {config.icon}
        </div>
    );
}

export default function Index({ providers, availableSlugs, can }: ProvidersPageProps) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [dialogStep, setDialogStep] = useState<'select' | 'form'>('select');
    const [selectedSlug, setSelectedSlug] = useState<AvailableSlug | null>(null);

    const createForm = useForm({
        slug: '',
        api_token: '',
    });

    const openDialog = () => {
        setDialogStep('select');
        setSelectedSlug(null);
        createForm.reset();
        createForm.clearErrors();
        setDialogOpen(true);
    };

    const selectProvider = (slug: AvailableSlug) => {
        setSelectedSlug(slug);
        createForm.setData('slug', slug.value);
        createForm.setData('api_token', '');
        createForm.clearErrors();
        setDialogStep('form');
    };

    const submitCreate = (event: React.FormEvent) => {
        event.preventDefault();
        createForm.post(storeProvider.url(), {
            onSuccess: () => setDialogOpen(false),
        });
    };

    return (
        <AppLayout tabs={adminTabs}>
            <Head title="Providers" />
            <AdminSettingsLayout>
                <SettingsSection title="Providers" description="Platform cloud providers available for provisioning infrastructure.">
                    {providers.length === 0 ? (
                        <div className="px-6 py-8">
                            <Empty>
                                <EmptyHeader>
                                    <EmptyMedia variant="icon">
                                        <Cloud className="size-6" />
                                    </EmptyMedia>
                                    <EmptyTitle>No providers</EmptyTitle>
                                    <EmptyDescription>No cloud providers have been configured yet.</EmptyDescription>
                                </EmptyHeader>
                                {can.create && availableSlugs.length > 0 && (
                                    <Button size="sm" className="mt-4" onClick={openDialog}>
                                        <Plus className="size-4" />
                                        Add provider
                                    </Button>
                                )}
                            </Empty>
                        </div>
                    ) : (
                        <>
                            {providers.map((provider) => (
                                <Link
                                    key={provider.id}
                                    href={`/admin/settings/providers/${provider.id}`}
                                    className="flex items-center justify-between p-5 transition-colors hover:bg-muted/30 sm:px-6"
                                >
                                    <div className="flex items-center gap-3">
                                        <ProviderLogo slug={provider.slug} />
                                        <div>
                                            <span className="text-sm font-medium">{provider.name}</span>
                                            <div className="mt-1 flex items-center gap-2">
                                                {provider.is_active ? (
                                                    <Badge variant="outline" className="gap-1.5">
                                                        <div className="size-2 rounded-full bg-emerald-500" />
                                                        Active
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline" className="gap-1.5 text-muted-foreground">
                                                        <div className="size-2 rounded-full bg-muted-foreground/40" />
                                                        Inactive
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                                        <KeyRound className="size-3.5" />
                                        {provider.has_api_token ? 'Token configured' : 'No token'}
                                    </div>
                                </Link>
                            ))}

                            {can.create && availableSlugs.length > 0 && (
                                <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                                    <Button size="sm" onClick={openDialog}>
                                        <Plus className="size-4" />
                                        Add provider
                                    </Button>
                                </div>
                            )}
                        </>
                    )}
                </SettingsSection>
            </AdminSettingsLayout>

            <StepDialog open={dialogOpen} onOpenChange={setDialogOpen}>
                {dialogStep === 'select' ? (
                    <>
                        <StepDialogHeader
                            title="New cloud provider"
                            description="Connect a cloud provider to deploy clusters. Infrastructure costs are billed directly via your provider account."
                        />
                        <StepDialogBody>
                            <div className="space-y-2">
                                {availableSlugs.map((slug) => {
                                    const config = PROVIDER_CONFIG[slug.value];

                                    return (
                                        <button
                                            key={slug.value}
                                            onClick={() => selectProvider(slug)}
                                            className="flex w-full items-center gap-3 rounded-lg border border-border px-4 py-3 text-left transition-colors hover:bg-accent"
                                        >
                                            <div className={`flex size-8 items-center justify-center rounded-md ${config?.bg ?? 'bg-muted'} ${config?.color ?? 'text-muted-foreground'}`}>
                                                {config?.icon ?? <Cloud className="size-[18px]" />}
                                            </div>
                                            <span className="text-sm font-medium">{slug.label}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </StepDialogBody>
                    </>
                ) : (
                    <>
                        <StepDialogHeader
                            title={selectedSlug?.label ?? ''}
                            description={`Connect your ${selectedSlug?.label} account to deploy servers.`}
                            onBack={() => setDialogStep('select')}
                        />
                        {selectedSlug && (
                            <form id="create-provider-form" onSubmit={submitCreate}>
                                <StepDialogBody>
                                    <div className="mb-6 flex items-center gap-3">
                                        <div className={`flex size-8 items-center justify-center rounded-md ${PROVIDER_CONFIG[selectedSlug.value]?.bg ?? 'bg-muted'} ${PROVIDER_CONFIG[selectedSlug.value]?.color ?? 'text-muted-foreground'}`}>
                                            {PROVIDER_CONFIG[selectedSlug.value]?.icon ?? <Cloud className="size-[18px]" />}
                                        </div>
                                        <span className="text-sm font-medium">{selectedSlug.label}</span>
                                    </div>
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="provider-token">API Token</Label>
                                            <Input
                                                id="provider-token"
                                                type="password"
                                                placeholder={`Enter your ${selectedSlug.label} API token`}
                                                value={createForm.data.api_token}
                                                onChange={(e) => createForm.setData('api_token', e.target.value)}
                                                disabled={createForm.processing}
                                                autoComplete="new-password"
                                            />
                                            <InputError message={createForm.errors.api_token} />
                                        </div>
                                    </div>
                                </StepDialogBody>
                                <StepDialogFooter>
                                    <Button type="submit" className="w-full" disabled={createForm.processing}>
                                        {createForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                        Add provider
                                    </Button>
                                </StepDialogFooter>
                            </form>
                        )}
                    </>
                )}
            </StepDialog>
        </AppLayout>
    );
}
