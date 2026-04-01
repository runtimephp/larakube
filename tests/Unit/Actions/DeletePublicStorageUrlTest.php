<?php

declare(strict_types=1);

use App\Actions\DeletePublicStorageUrl;
use Illuminate\Support\Facades\Storage;

test('deletes a public storage url when present', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('organizations/logos/acme.png', 'image');

    app(DeletePublicStorageUrl::class)->handle('/storage/organizations/logos/acme.png');

    Storage::disk('public')->assertMissing('organizations/logos/acme.png');
});

test('ignores empty or null urls', function (?string $url): void {
    Storage::fake('public');
    Storage::disk('public')->put('organizations/logos/acme.png', 'image');

    app(DeletePublicStorageUrl::class)->handle($url);

    Storage::disk('public')->assertExists('organizations/logos/acme.png');
})->with([null, '']);

test('ignores non storage urls', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('organizations/logos/acme.png', 'image');

    app(DeletePublicStorageUrl::class)->handle('https://example.com/logo.png');

    Storage::disk('public')->assertExists('organizations/logos/acme.png');
});
