<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\Storage;

final class DeletePublicStorageUrl
{
    public function handle(?string $url): void
    {
        if (! is_string($url) || $url === '') {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! str_starts_with($path, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(str($path)->after('/storage/')->toString());
    }
}
