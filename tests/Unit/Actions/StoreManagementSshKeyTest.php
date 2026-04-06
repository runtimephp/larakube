<?php

declare(strict_types=1);

use App\Actions\StoreManagementSshKey;
use App\Models\ManagementCluster;

test('stores encrypted ssh private key on management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var ManagementCluster $cluster */
        $cluster = ManagementCluster::factory()->create();

        $action = new StoreManagementSshKey;
        $action->handle($cluster, 'fake-private-key-content');

        $cluster->refresh();

        expect($cluster->ssh_private_key)->toBe('fake-private-key-content');
    });
