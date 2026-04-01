<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function (Model $model): void {
            /** @var string|null $slug */
            $slug = $model->getAttribute('slug');

            if ($slug !== null) {
                return;
            }

            /** @var string $name */
            $name = $model->getAttribute('name');
            $model->setAttribute('slug', static::generateUniqueSlug($name));
        });
    }

    private static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;

        if (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}
