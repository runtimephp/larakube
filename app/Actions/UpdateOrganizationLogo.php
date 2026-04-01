<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateOrganizationLogo
{
    public function __construct(
        private DeletePublicStorageUrl $deletePublicStorageUrl,
    ) {}

    public function handle(Organization $organization, UploadedFile $logo): void
    {
        $this->deletePublicStorageUrl->handle($organization->logo);

        $path = $logo->store('organizations/logos', 'public');

        $organization->query()
            ->whereKey($organization->getKey())
            ->update([
                'logo' => Storage::disk('public')->url($path),
            ]);
    }
}
