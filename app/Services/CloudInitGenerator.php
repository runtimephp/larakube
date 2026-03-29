<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final readonly class CloudInitGenerator
{
    public function __construct(
        private string $basePath = '',
    ) {}

    public function bastion(string $bastionPublicKey): string
    {
        $base = $this->loadTemplate('bastion.yaml');

        $base['ssh_authorized_keys'] = [
            $bastionPublicKey,
        ];

        return "#cloud-config\n".Yaml::dump($base, 4, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadTemplate(string $filename): array
    {
        $path = $this->resolvePath($filename);

        if (! file_exists($path)) {
            throw new RuntimeException("Cloud-init template not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("Failed to read cloud-init template: {$path}");
        }

        return Yaml::parse($content);
    }

    private function resolvePath(string $filename): string
    {
        if ($this->basePath !== '') {
            return $this->basePath.'/'.$filename;
        }

        return base_path('infrastructure/cloud-init/'.$filename);
    }
}
