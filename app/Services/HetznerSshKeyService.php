<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SshKeyService;
use App\Data\SshKeyData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class HetznerSshKeyService implements SshKeyService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function register(string $name, string $publicKey): SshKeyData
    {
        $response = Http::withToken($this->token)
            ->post('https://api.hetzner.cloud/v1/ssh_keys', [
                'name' => $name,
                'public_key' => $publicKey,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to register SSH key on Hetzner.'));
        }

        return $this->mapSshKeyData($response->json('ssh_key'));
    }

    /**
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function list(): Collection
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/ssh_keys');

        if (! $response->successful()) {
            throw new RuntimeException($response->json('error.message', 'Failed to list SSH keys on Hetzner.'));
        }

        return collect($response->json('ssh_keys', []))
            ->map($this->mapSshKeyData(...));
    }

    /**
     * @throws ConnectionException
     */
    public function delete(int|string $externalId): bool
    {
        $response = Http::withToken($this->token)
            ->delete("https://api.hetzner.cloud/v1/ssh_keys/{$externalId}");

        return $response->successful();
    }

    /**
     * @param  array<string, mixed>  $sshKey
     */
    private function mapSshKeyData(array $sshKey): SshKeyData
    {
        return new SshKeyData(
            externalId: $sshKey['id'],
            name: $sshKey['name'],
            fingerprint: $sshKey['fingerprint'],
            publicKey: $sshKey['public_key'],
        );
    }
}
