import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import general from '@/routes/organizations/settings/general';
import { LoaderCircle } from 'lucide-react';

export default function Profile({ mustVerifyEmail, status }: { mustVerifyEmail: boolean; status?: string }) {
    const { auth, currentOrganization } = usePage<SharedData>().props;

    const tabs = currentOrganization
        ? [
              { title: 'Dashboard', url: `/${currentOrganization.slug}/dashboard` },
              { title: 'Clusters', url: `/${currentOrganization.slug}/clusters` },
              { title: 'Resources', url: `/${currentOrganization.slug}/resources` },
              { title: 'Settings', url: general.edit({ organization: currentOrganization.slug }).url },
          ]
        : [{ title: 'Settings', url: '/settings/profile' }];

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: auth.user.name,
        email: auth.user.email,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <AppLayout tabs={tabs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <form onSubmit={submit}>
                    <SettingsSection title="Profile" description="Update your personal account information.">
                        <SettingsField label="Name" description="Your full name as displayed across the app." htmlFor="name">
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    autoComplete="name"
                                    placeholder="Full name"
                                    disabled={processing}
                                />
                                <InputError message={errors.name} />
                            </div>
                        </SettingsField>

                        <SettingsField label="Email address" description="Your email for notifications and sign-in." htmlFor="email">
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                    autoComplete="username"
                                    placeholder="Email address"
                                    disabled={processing}
                                />
                                <InputError message={errors.email} />

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="text-muted-foreground text-[13px]">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={route('verification.send')}
                                                method="post"
                                                as="button"
                                                className="text-foreground text-[13px] underline hover:no-underline focus:outline-hidden"
                                            >
                                                Re-send verification email.
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <p className="mt-1 text-[13px] font-medium text-green-600">A new verification link has been sent.</p>
                                        )}
                                    </div>
                                )}

                                {recentlySuccessful && <p className="text-muted-foreground text-[13px]">Changes saved.</p>}
                            </div>
                        </SettingsField>

                        <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                            <Button type="submit" size="sm" disabled={processing}>
                                {processing && <LoaderCircle className="size-4 animate-spin" />}
                                Save changes
                            </Button>
                        </div>
                    </SettingsSection>
                </form>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
