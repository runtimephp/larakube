<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

final readonly class CloudInitGenerator
{
    public function __construct(
        private string $basePath = '',
    ) {}

    public function bastion(string $bastionPublicKey): string
    {
        $trimmed = trim($bastionPublicKey);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Bastion public key must not be empty.');
        }

        $base = $this->loadTemplate('bastion.yaml');

        $base['ssh_authorized_keys'] = [
            $trimmed,
        ];

        return "#cloud-config\n".Yaml::dump($base, 4, 2);
    }

    /**
     * @param  list<string>  $dnsServers
     */
    public function node(string $nodePublicKey, ?string $networkGateway = null, array $dnsServers = []): string
    {
        $trimmed = trim($nodePublicKey);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Node public key must not be empty.');
        }

        $config = [
            'ssh_authorized_keys' => [
                $trimmed,
            ],
        ];

        if ($networkGateway !== null) {
            $dnsLine = $dnsServers !== [] ? implode(' ', $dnsServers) : '1.1.1.1 8.8.8.8';

            $config['runcmd'] = [
                ['bash', '-c', 'systemctl disable --now hc-utils 2>/dev/null || true'],
                ['bash', '-c', "for i in $(seq 1 30); do ip route get {$networkGateway} >/dev/null 2>&1 && break; sleep 1; done"],
                ['bash', '-c', "ip route add default via {$networkGateway} dev enp7s0 2>/dev/null || true"],
                ['bash', '-c', "sed -i 's/^#DNS=.*/DNS={$dnsLine}/' /etc/systemd/resolved.conf"],
                ['systemctl', 'restart', 'systemd-resolved'],
            ];

            $config['write_files'] = [
                [
                    'path' => '/etc/systemd/network/10-enp7s0.network',
                    'content' => "[Match]\nName=enp7s0\n\n[Network]\nDHCP=yes\n\n[Route]\nGateway={$networkGateway}\n",
                    'permissions' => '0644',
                ],
            ];
        }

        return "#cloud-config\n".Yaml::dump($config, 4, 2);
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

        if ($content === false) { // @codeCoverageIgnore
            throw new RuntimeException("Failed to read cloud-init template: {$path}"); // @codeCoverageIgnore
        } // @codeCoverageIgnore

        $parsed = Yaml::parse($content);

        if (! is_array($parsed)) {
            throw new RuntimeException("Invalid cloud-init template format: expected mapping in {$path}");
        }

        return $parsed;
    }

    private function resolvePath(string $filename): string
    {
        if ($this->basePath !== '') {
            return $this->basePath.'/'.$filename;
        }

        return base_path('infrastructure/cloud-init/'.$filename);
    }
}
