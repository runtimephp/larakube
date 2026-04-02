import { update as updateGeneralSettings } from '@/actions/App/Http/Controllers/OrganizationGeneralSettingsController';
import { update as updateOrganizationLogo } from '@/actions/App/Http/Controllers/OrganizationLogoController';
import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
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
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        detailsForm.patch(updateGeneralSettings.url(organization.slug), {
                            preserveScroll: true,
                        });
                    }}
                >
                    <SettingsSection title="General" description="General settings related to this organization.">
                        <SettingsField label="Organization name" description="The name used to identify your organization." htmlFor="name">
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="name"
                                    value={detailsForm.data.name}
                                    onChange={(event) => detailsForm.setData('name', event.target.value)}
                                    disabled={!can.update || detailsForm.processing}
                                    required
                                />
                                <InputError message={detailsForm.errors.name} />
                                {detailsForm.recentlySuccessful || status === 'organization-general-settings-updated' ? (
                                    <p className="text-muted-foreground text-[13px]">Changes saved.</p>
                                ) : null}
                            </div>
                        </SettingsField>

                        <SettingsField label="Description" description="A brief description of your organization." htmlFor="description">
                            <div className="w-[260px] space-y-2">
                                <Textarea
                                    id="description"
                                    rows={4}
                                    value={detailsForm.data.description}
                                    onChange={(event) => detailsForm.setData('description', event.target.value)}
                                    disabled={!can.update || detailsForm.processing}
                                />
                                <InputError message={detailsForm.errors.description} />
                            </div>
                        </SettingsField>

                        <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                            <Button type="submit" size="sm" disabled={!can.update || detailsForm.processing || !detailsForm.isDirty}>
                                {detailsForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                Save changes
                            </Button>
                        </div>
                    </SettingsSection>
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
                    <SettingsSection title="Avatar" description="Upload an organization avatar for menus and settings screens.">
                        <SettingsField label="Organization avatar" description="Add an image to identify your organization." stretch>
                            <div className="flex items-center gap-6">
                                <Avatar className="size-20">
                                    <AvatarImage src={previewUrl ?? undefined} alt={organization.name} />
                                    <AvatarFallback className="text-lg font-semibold">{initials}</AvatarFallback>
                                </Avatar>
                                <div>
                                    <input ref={fileInputRef} type="file" accept="image/*" className="hidden" onChange={handleLogoChange} />
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => fileInputRef.current?.click()}
                                        disabled={!can.update || logoForm.processing}
                                    >
                                        <Upload className="size-4" />
                                        Upload file
                                    </Button>
                                </div>
                            </div>
                        </SettingsField>

                        <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                            <Button type="submit" size="sm" disabled={!can.update || logoForm.processing || !logoForm.data.logo}>
                                {logoForm.processing && <LoaderCircle className="size-4 animate-spin" />}
                                Save avatar
                            </Button>
                        </div>
                    </SettingsSection>
                </form>
            </OrganizationSettingsLayout>
        </AppLayout>
    );
}
