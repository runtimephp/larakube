import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';

import InputError from '@/components/input-error';
import { SettingsField, SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Trash2 } from 'lucide-react';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const { data, setData, delete: destroy, processing, reset, errors, clearErrors } = useForm({ password: '' });

    const deleteUser: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        clearErrors();
        reset();
    };

    return (
        <SettingsSection title="Danger" description="Destructive settings that cannot be undone." variant="danger">
            <SettingsField label="Delete account" description="Deleting your account will permanently delete all of its resources and data." stretch>
                <div className="flex items-center justify-end">
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button variant="destructive" size="default">
                                <Trash2 className="size-4" />
                                Delete account
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
                            <DialogDescription>
                                Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your
                                password to confirm you would like to permanently delete your account.
                            </DialogDescription>
                            <form className="space-y-6" onSubmit={deleteUser}>
                                <div className="grid gap-2">
                                    <Label htmlFor="password" className="sr-only">
                                        Password
                                    </Label>

                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        ref={passwordInput}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        placeholder="Password"
                                        autoComplete="current-password"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button variant="secondary" onClick={closeModal}>
                                            Cancel
                                        </Button>
                                    </DialogClose>

                                    <Button variant="destructive" disabled={processing} asChild>
                                        <button type="submit">Delete account</button>
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </SettingsField>
        </SettingsSection>
    );
}
