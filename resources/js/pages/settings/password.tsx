import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import general from '@/routes/organizations/settings/general';
import { type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useRef } from 'react';

export default function Password() {
    const { currentOrganization } = usePage<SharedData>().props;
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const tabs = currentOrganization
        ? [
              { title: 'Dashboard', url: `/${currentOrganization.slug}/dashboard` },
              { title: 'Clusters', url: `/${currentOrganization.slug}/clusters` },
              { title: 'Resources', url: `/${currentOrganization.slug}/resources` },
              { title: 'Settings', url: general.edit({ organization: currentOrganization.slug }).url },
          ]
        : [{ title: 'Settings', url: '/settings/profile' }];

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <AppLayout tabs={tabs}>
            <Head title="Password settings" />

            <SettingsLayout>
                <form onSubmit={updatePassword}>
                    <SettingsSection title="Password" description="Ensure your account is using a long, random password to stay secure.">
                        <SettingsField
                            label="Current password"
                            description="Enter your existing password to verify your identity."
                            htmlFor="current_password"
                        >
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="current_password"
                                    ref={currentPasswordInput}
                                    value={data.current_password}
                                    onChange={(e) => setData('current_password', e.target.value)}
                                    type="password"
                                    autoComplete="current-password"
                                    placeholder="Current password"
                                    disabled={processing}
                                />
                                <InputError message={errors.current_password} />
                            </div>
                        </SettingsField>

                        <SettingsField label="New password" description="Choose a strong password with at least 8 characters." htmlFor="password">
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="password"
                                    ref={passwordInput}
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    type="password"
                                    autoComplete="new-password"
                                    placeholder="New password"
                                    disabled={processing}
                                />
                                <InputError message={errors.password} />
                            </div>
                        </SettingsField>

                        <SettingsField label="Confirm password" description="Re-enter your new password to confirm." htmlFor="password_confirmation">
                            <div className="w-[260px] space-y-2">
                                <Input
                                    id="password_confirmation"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    type="password"
                                    autoComplete="new-password"
                                    placeholder="Confirm password"
                                    disabled={processing}
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>
                        </SettingsField>

                        <div className="bg-muted/20 border-border/70 flex items-center justify-end border-t px-4 py-2.5 sm:px-5">
                            {recentlySuccessful && <p className="text-muted-foreground mr-4 text-[13px]">Password updated.</p>}
                            <Button type="submit" size="sm" disabled={processing}>
                                {processing && <LoaderCircle className="size-4 animate-spin" />}
                                Save password
                            </Button>
                        </div>
                    </SettingsSection>
                </form>
            </SettingsLayout>
        </AppLayout>
    );
}
