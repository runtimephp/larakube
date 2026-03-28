<?php

declare(strict_types=1);

namespace App\Console\Services;

use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Data\SessionUserData;
use Illuminate\Container\Attributes\Config;

final class SessionManager
{
    private array $config = [];

    private readonly string $path;

    public function __construct(
        #[Config('larakube.sessions_path')] string $sessionsPath,
        #[Config('larakube.api_url')] string $apiUrl,
    ) {
        $this->path = $sessionsPath.'/'.md5($apiUrl).'.json';
        $this->load();
    }

    public function save(): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        file_put_contents($this->path, json_encode($this->config, JSON_PRETTY_PRINT));
        chmod($this->path, 0600);
    }

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
        $this->save();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function clear(): void
    {
        $this->config = [];
        $this->save();
    }

    public function isAuthenticated(): bool
    {
        return ! empty($this->config['token']);
    }

    public function setUser(SessionUserData $user): void
    {
        $this->set('user', $user->toArray());
        $this->set('token', $user->token);
    }

    public function getUser(): ?SessionUserData
    {
        $data = $this->get('user');

        if (! is_array($data)) {
            return null;
        }

        return SessionUserData::fromArray($data);
    }

    public function setOrganization(SessionOrganizationData $organization): void
    {
        $this->set('organization', $organization->toArray());
    }

    public function getOrganization(): ?SessionOrganizationData
    {
        $data = $this->get('organization');

        if (! is_array($data)) {
            return null;
        }

        return SessionOrganizationData::fromArray($data);
    }

    public function hasOrganization(): bool
    {
        return $this->get('organization') !== null;
    }

    public function setInfrastructure(SessionInfrastructureData $infrastructure): void
    {
        $this->set('infrastructure', $infrastructure->toArray());
    }

    public function getInfrastructure(): ?SessionInfrastructureData
    {
        $data = $this->get('infrastructure');

        if (! is_array($data)) {
            return null;
        }

        return SessionInfrastructureData::fromArray($data);
    }

    public function clearOrganization(): void
    {
        $this->set('organization', null);
    }

    public function clearInfrastructure(): void
    {
        $this->set('infrastructure', null);
    }

    private function load(): void
    {
        if (file_exists($this->path)) {
            $this->config = json_decode(file_get_contents($this->path), true) ?? [];
        }
    }
}
