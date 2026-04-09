import { type PlatformRegion, type Provider } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ChevronDownIcon, ChevronRight } from 'lucide-react';
import { index, store } from '@/routes/admin/management-clusters'
import { Card, CardContent } from '@/components/ui/card';
import { FieldGroup, FieldSet, Field, FieldLabel, FieldDescription } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useState } from 'react';
import { Transition } from '@headlessui/react';
import InputError from '@/components/input-error';

interface CreateManagementClusterPageProps {
    providers: Provider[];
}

export default function CreateManagementClusterPage({providers}: CreateManagementClusterPageProps ) {

    const [selectedProvider, setSelectedProvider] = useState<Provider>();
    const [selectedRegion, setSelectedRegion] = useState<PlatformRegion>();

    function handleProviderSelect(provider: Provider) {
        setSelectedProvider(provider);
        setSelectedRegion(undefined);
    }
        return (
            <AppLayout>
                <Head title="Create cluster" />

                <div className="mx-auto flex w-full max-w-xl flex-col justify-center space-y-6 p-4 sm:p-0">
                    <div className="mb-6 flex cursor-pointer items-center space-x-1 text-sm">
                        <Link href={index().url} className="flex items-center space-x-1 text-sm text-gray-500 hover:text-gray-700">
                            <ArrowLeft size="16" />
                            <span>Back</span>
                        </Link>
                    </div>

                    <div className="flex flex-col space-y-2">
                        <h2 className="font-semibold">Create a new Cluster</h2>
                        <p className="text-muted-foreground text-sm">
                            Provision a new management cluster by selecting a provider and entering the required configuration details.
                        </p>
                    </div>

                    <div className="flex flex-col">
                        <Card>
                            <CardContent>
                                <Form
                                    action={store()}
                                    transform={(data) => ({
                                        ...data,
                                        provider_id: selectedProvider?.id,
                                        region_id: selectedRegion?.id,
                                    })}
                                    options={{ preserveScroll: true }}
                                    className="space-y-4"
                                >
                                    {({ processing, recentlySuccessful, errors }) => (
                                        <>
                                            <FieldSet>
                                                <FieldGroup>
                                                    <Field orientation="vertical" className="gap-0.5 space-y-1.5">
                                                        <FieldLabel>Name</FieldLabel>
                                                        <Input id="name" name="name" type="text" autoComplete={'off'} />
                                                        <div>
                                                            <FieldDescription className="mt-24 block">
                                                                The name of the time off type.
                                                            </FieldDescription>
                                                            <InputError message={errors.name} />
                                                        </div>
                                                    </Field>

                                                    <Field orientation="vertical" className="gap-0.5 space-y-1.5">
                                                        <FieldLabel>Provider</FieldLabel>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button variant="outline" className="justify-start">
                                                                    <div className="flex w-full items-center justify-between">
                                                                        <span>{selectedProvider?.name ?? 'Select a provider'}</span>
                                                                        <ChevronDownIcon />
                                                                    </div>
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent className="w-(--radix-dropdown-menu-trigger-width)">
                                                                {providers.map((provider) => (
                                                                    <DropdownMenuItem
                                                                        onSelect={() => handleProviderSelect(provider)}
                                                                        key={provider.id}
                                                                    >
                                                                        {provider.name}
                                                                    </DropdownMenuItem>
                                                                ))}
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>

                                                        <InputError message={errors.provider_id} />
                                                    </Field>

                                                    <Field orientation="vertical" className="gap-0.5 space-y-1.5">
                                                        <FieldLabel>Region</FieldLabel>
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger asChild>
                                                                <Button
                                                                    variant="outline"
                                                                    className="justify-start"
                                                                    disabled={!selectedProvider?.regions?.length}
                                                                >
                                                                    <div className="flex w-full items-center justify-between">
                                                                        <span>
                                                                            {selectedRegion
                                                                                ? `${selectedRegion.name} (${selectedRegion.slug})`
                                                                                : 'Select a region'}
                                                                        </span>
                                                                        <ChevronDownIcon />
                                                                    </div>
                                                                </Button>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent className="w-(--radix-dropdown-menu-trigger-width)">
                                                                {selectedProvider?.regions?.map((region) => (
                                                                    <DropdownMenuItem onSelect={() => setSelectedRegion(region)} key={region.id}>
                                                                        {region.name}{' '}
                                                                        <span className="text-muted-foreground ml-auto text-xs">{region.slug}</span>
                                                                    </DropdownMenuItem>
                                                                ))}
                                                            </DropdownMenuContent>
                                                        </DropdownMenu>
                                                        <InputError message={errors.region_id} />
                                                    </Field>
                                                </FieldGroup>
                                            </FieldSet>
                                            <div className="mt-8 flex flex-col items-center gap-y-4">
                                                <Button
                                                    disabled={processing}
                                                    data-test="submit-time-off-type-form"
                                                    type="submit"
                                                    className="flex w-full items-center"
                                                >
                                                    Create cluster <ChevronRight className="size-3.5" />
                                                </Button>
                                                <Transition
                                                    show={recentlySuccessful}
                                                    enter="transition ease-in-out"
                                                    enterFrom="opacity-0"
                                                    leave="transition ease-in-out"
                                                    leaveTo="opacity-0"
                                                >
                                                    <p className="text-sm text-neutral-600">Saved</p>
                                                </Transition>
                                            </div>
                                        </>
                                    )}
                                </Form>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </AppLayout>
        );
}
