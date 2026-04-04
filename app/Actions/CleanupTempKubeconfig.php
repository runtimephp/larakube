<?php

declare(strict_types=1);

namespace App\Actions;

final class CleanupTempKubeconfig
{
    public function handle(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
