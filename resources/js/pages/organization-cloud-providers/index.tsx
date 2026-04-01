import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import OrganizationSettingsLayout from '@/layouts/organization-settings-layout';
import cloudProviderRoutes from '@/routes/organizations/settings/cloud-providers';
import { type CloudProvider, type Organization } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { LoaderCircle, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface OrganizationCloudProvidersPageProps {
    organization: Organization;
    cloudProviders: CloudProvider[];
    can: {
        manage: boolean;
    };
    status?: string;
}

const PROVIDER_LABELS: Record<string, string> = {
    hetzner: 'Hetzner',
    digital_ocean: 'DigitalOcean',
};

export default function OrganizationCloudProvidersPage({ organization, cloudProviders, can, status }: OrganizationCloudProvidersPageProps) {
    const [addDialogOpen, setAddDialogOpen] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<CloudProvider | null>(null);

    const tabs = [
        { title: 'Dashboard', url: `/${organization.slug}/dashboard` },
        { title: 'Clusters', url: `/${organization.slug}/clusters` },
        { title: 'Resources', url: `/${organization.slug}/resources` },
        { title: 'Settings', url: `/${organization.slug}/settings/general` },
    ];

    const addForm = useForm({
        name: '',
        type: '',
        api_token: '',
    });

    function submitAdd(event: React.FormEvent) {
        event.preventDefault();
        addForm.post(cloudProviderRoutes.store.url(organization.slug), {
            preserveScroll: true,
            onSuccess: () => {
                setAddDialogOpen(false);
                addForm.reset();
            },
        });
    }

    function confirmDelete() {
        if (!deleteTarget) {
            return;
        }
        router.delete(cloudProviderRoutes.destroy.url({ organization: organization.slug, cloudProvider: deleteTarget.id }), {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    }

    return (
        <AppLayout tabs={tabs}>
            <Head title={`${organization.name} — Cloud Providers`} />

            <OrganizationSettingsLayout organization={organization}>
                <Card>
                    <CardHeader className="flex flex-row items-start justify-between gap-4">
                        <div>
                            <CardTitle>Cloud Providers</CardTitle>
                            <CardDescription>Manage the infrastructure providers connected to this organization.</CardDescription>
                        </div>

                        {can.manage && (
                            <Dialog open={addDialogOpen} onOpenChange={setAddDialogOpen}>
                                <DialogTrigger asChild>
                                    <Button size="sm">
                                        <Plus className="size-4" />
                                        Add provider
                                    </Button>
                                </DialogTrigger>

                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Add cloud provider</DialogTitle>
                                        <DialogDescription>
                                            Connect a cloud provider by entering a name, selecting a type, and providing an API token.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <form id="add-provider-form" onSubmit={submitAdd} className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                placeholder="My Hetzner account"
                                                value={addForm.data.name}
                                                onChange={(e) => addForm.setData('name', e.target.value)}
                                                disabled={addForm.processing}
                                            />
                                            <InputError message={addForm.errors.name} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="type">Provider</Label>
                                            <Select
                                                value={addForm.data.type}
                                                onValueChange={(value) => addForm.setData('type', value)}
                                                disabled={addForm.processing}
                                            >
                                                <SelectTrigger id="type">
                                                    <SelectValue placeholder="Select a provider" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="hetzner">Hetzner</SelectItem>
                                                    <SelectItem value="digital_ocean">DigitalOcean</SelectItem>
                                                </SelectContent>
                                            </Select>
                                            <InputError message={addForm.errors.type} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="api_token">API token</Label>
                                            <Input
                                                id="api_token"
                                                type="password"
                                                placeholder="Paste your API token"
                                                value={addForm.data.api_token}
                                                onChange={(e) => addForm.setData('api_token', e.target.value)}
                                                disabled={addForm.processing}
                                            />
                                            <InputError message={addForm.errors.api_token} />
                                        </div>
                                    </form>

                                    <DialogFooter>
                                        <Button variant="outline" onClick={() => setAddDialogOpen(false)} disabled={addForm.processing}>
                                            Cancel
                                        </Button>
                                        <Button type="submit" form="add-provider-form" disabled={addForm.processing}>
                                            {addForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                            Connect provider
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        )}
                    </CardHeader>

                    <CardContent>
                        {cloudProviders.length === 0 ? (
                            <div className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">
                                {status === 'cloud-provider-deleted' ? 'Provider removed.' : 'No cloud providers connected yet.'}
                            </div>
                        ) : (
                            <ul className="divide-y">
                                {cloudProviders.map((provider) => (
                                    <li key={provider.id} className="flex items-center justify-between py-3">
                                        <div className="flex items-center gap-3">
                                            <div>
                                                <p className="text-sm font-medium">{provider.name}</p>
                                                <p className="text-muted-foreground text-xs">{PROVIDER_LABELS[provider.type] ?? provider.type}</p>
                                            </div>
                                            <Badge variant={provider.is_verified ? 'default' : 'outline'}>
                                                {provider.is_verified ? 'Verified' : 'Unverified'}
                                            </Badge>
                                        </div>

                                        {can.manage && (
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => setDeleteTarget(provider)}
                                                className="text-destructive hover:text-destructive"
                                            >
                                                <Trash2 className="size-4" />
                                                <span className="sr-only">Remove {provider.name}</span>
                                            </Button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}

                        {status === 'cloud-provider-created' && cloudProviders.length > 0 && (
                            <p className="text-muted-foreground mt-4 text-sm">Provider connected successfully.</p>
                        )}
                    </CardContent>
                </Card>
            </OrganizationSettingsLayout>

            {/* Delete confirmation dialog */}
            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Remove cloud provider</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to remove <strong>{deleteTarget?.name}</strong>? Any infrastructure relying on this provider may
                            stop working.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteTarget(null)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Remove provider
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
