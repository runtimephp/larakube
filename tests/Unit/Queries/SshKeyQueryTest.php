<?php

declare(strict_types=1);

use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;
use App\Queries\SshKeyQuery;

test('first returns null when no keys match',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra)->first())->toBeNull();
    });

test('first returns key when found',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        /** @var SshKey $key */
        $key = SshKey::factory()->bastion()->createQuietly(['infrastructure_id' => $infra->id]);

        $query = new SshKeyQuery();
        $result = ($query)()->byInfrastructure($infra)->first();

        expect($result)->not->toBeNull()
            ->and($result->id)->toBe($key->id);
    });

test('by infrastructure filters keys',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra1 */
        $infra1 = Infrastructure::factory()->createQuietly();

        /** @var Infrastructure $infra2 */
        $infra2 = Infrastructure::factory()->createQuietly();

        SshKey::factory()->createQuietly(['infrastructure_id' => $infra1->id]);
        SshKey::factory()->createQuietly(['infrastructure_id' => $infra2->id]);

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra1)->get())->toHaveCount(1);
    });

test('by purpose filters keys',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        SshKey::factory()->bastion()->createQuietly(['infrastructure_id' => $infra->id]);
        SshKey::factory()->node()->createQuietly(['infrastructure_id' => $infra->id]);

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra)->byPurpose(SshKeyPurpose::Bastion)->get())->toHaveCount(1);
    });

test('unregistered filters keys without external id',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        SshKey::factory()->createQuietly(['infrastructure_id' => $infra->id, 'external_ssh_key_id' => '123']);
        SshKey::factory()->createQuietly(['infrastructure_id' => $infra->id, 'external_ssh_key_id' => null]);

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra)->unregistered()->get())->toHaveCount(1);
    });

test('exists returns true when keys exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        SshKey::factory()->createQuietly(['infrastructure_id' => $infra->id]);

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra)->exists())->toBeTrue();
    });

test('exists returns false when no keys',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        $query = new SshKeyQuery();
        expect(($query)()->byInfrastructure($infra)->exists())->toBeFalse();
    });

test('first or fail throws when not found',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infra */
        $infra = Infrastructure::factory()->createQuietly();

        $query = new SshKeyQuery();
        ($query)()->byInfrastructure($infra)->firstOrFail();
    })->throws(Illuminate\Database\Eloquent\ModelNotFoundException::class);
