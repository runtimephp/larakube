<?php

declare(strict_types=1);

use App\Enums\ClusterTopology;

test('label returns correct labels', function (): void {
    expect(ClusterTopology::SingleCp->label())->toBe('Single Control Plane')
        ->and(ClusterTopology::Ha->label())->toBe('High Availability');
});

test('cases returns all cases', function (): void {
    expect(ClusterTopology::cases())->toBe([
        ClusterTopology::SingleCp,
        ClusterTopology::Ha,
    ]);
});
