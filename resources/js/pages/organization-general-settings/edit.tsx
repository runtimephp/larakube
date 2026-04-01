import { update as updateGeneralSettings } from '@/actions/App/Http/Controllers/OrganizationGeneralSettingsController';
import { update as updateOrganizationLogo } from '@/actions/App/Http/Controllers/OrganizationLogoController';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import OrganizationSettingsLayout from '@/layouts/organization-settings-layout';
import { type Organization } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, Upload } from 'lucide-react';
import { type ChangeEvent, useEffect, useMemo, useRef } from 'react';

interface OrganizationGeneralSettingsPageProps {
    organization: Organization;
    can: {
        update: boolean;
    };
    status?: string;
}

export default function EditOrganizationGeneralSettingsPage({ organization, can, status }: OrganizationGeneralSettingsPageProps) {
    const fileInputRef = useRef<HTMLInputElement | null>(null);

    const tabs = [
        { title: 'Dashboard', url: `/${organization.slug}/dashboard` },
        { title: 'Clusters', url: `/${organization.slug}/clusters` },
        { title: 'Resources', url: `/${organization.slug}/resources` },
        { title: 'Settings', url: `/${organization.slug}/settings/general` },
    ];

    const detailsForm = useForm({
        name: organization.name,
        description: organization.description ?? '',
    });

    const logoForm = useForm<{
        logo: File | null;
    }>({
        logo: null,
    });

    const previewUrl = useMemo(() => {
        if (logoForm.data.logo instanceof File) {
            return URL.createObjectURL(logoForm.data.logo);
        }

        return organization.logo;
    }, [logoForm.data.logo, organization.logo]);

    useEffect(() => {
        return () => {
            if (logoForm.data.logo instanceof File && previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
        };
    }, [logoForm.data.logo, previewUrl]);

    const handleLogoChange = (event: ChangeEvent<HTMLInputElement>) => {
        const [file] = event.target.files ?? [];

        logoForm.setData('logo', file ?? null);
    };

    const initials = organization.name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <AppLayout tabs={tabs}>
            <Head title={`${organization.name} Settings`} />

            <OrganizationSettingsLayout organization={organization}>
                <div className="space-y-8">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            detailsForm.patch(updateGeneralSettings.url(organization.slug), {
                                preserveScroll: true,
                            });
                        }}
                    >
                        <Card>
                            <CardHeader>
                                <CardTitle>General</CardTitle>
                                <CardDescription>Update the organization profile shown across the workspace.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Organization name</Label>
                                    <Input
                                        id="name"
                                        value={detailsForm.data.name}
                                        onChange={(event) => detailsForm.setData('name', event.target.value)}
                                        disabled={!can.update || detailsForm.processing}
                                    />
                                    <InputError message={detailsForm.errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">Description</Label>
                                    <textarea
                                        id="description"
                                        rows={4}
                                        value={detailsForm.data.description}
                                        onChange={(event) => detailsForm.setData('description', event.target.value)}
                                        disabled={!can.update || detailsForm.processing}
                                        className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring min-h-28 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError message={detailsForm.errors.description} />
                                </div>
                            </CardContent>
                            <CardFooter className="flex items-center justify-between gap-4 border-t pt-6">
                                <div className="text-muted-foreground text-sm">
                                    {detailsForm.recentlySuccessful || status === 'organization-general-settings-updated'
                                        ? 'Changes saved.'
                                        : 'Save changes when ready.'}
                                </div>
                                <Button type="submit" disabled={!can.update || detailsForm.processing || !detailsForm.isDirty}>
                                    {detailsForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                    Save changes
                                </Button>
                            </CardFooter>
                        </Card>
                    </form>

                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            logoForm.patch(updateOrganizationLogo.url(organization.slug), {
                                forceFormData: true,
                                preserveScroll: true,
                                onSuccess: () => {
                                    logoForm.reset();
                                },
                            });
                        }}
                    >
                        <Card>
                            <CardHeader>
                                <CardTitle>Avatar</CardTitle>
                                <CardDescription>Upload an organization avatar for menus and settings screens.</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="flex flex-col gap-4 rounded-lg border border-dashed p-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div className="flex items-center gap-4">
                                        <Avatar className="size-16">
                                            <AvatarImage src={previewUrl ?? undefined} alt={organization.name} />
                                            <AvatarFallback className="text-lg font-semibold">{initials}</AvatarFallback>
                                        </Avatar>

                                        <div className="space-y-1">
                                            <p className="font-medium">{organization.name}</p>
                                            <p className="text-muted-foreground text-sm">PNG, JPG, GIF, or WEBP up to 2 MB.</p>
                                        </div>
                                    </div>

                                    <div className="flex gap-2">
                                        <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={handleLogoChange} />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => fileInputRef.current?.click()}
                                            disabled={!can.update || logoForm.processing}
                                        >
                                            <Upload className="size-4" />
                                            Choose avatar
                                        </Button>
                                    </div>
                                </div>

                                <InputError message={logoForm.errors.logo} />
                            </CardContent>
                            <CardFooter className="flex items-center justify-between gap-4 border-t pt-6">
                                <div className="text-muted-foreground text-sm">
                                    {logoForm.recentlySuccessful || status === 'organization-logo-updated'
                                        ? 'Avatar updated.'
                                        : 'Upload a new organization avatar.'}
                                </div>
                                <Button type="submit" disabled={!can.update || logoForm.processing || !logoForm.data.logo}>
                                    {logoForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                    Save avatar
                                </Button>
                            </CardFooter>
                        </Card>
                    </form>
                </div>
            </OrganizationSettingsLayout>
        </AppLayout>
    );
}
