<?php

declare(strict_types=1);

use App\Enums\ServerRole;

test('label returns correct labels', function (): void {
    expect(ServerRole::Bastion->label())->toBe('Bastion')
        ->and(ServerRole::ControlPlane->label())->toBe('Control Plane')
        ->and(ServerRole::Node->label())->toBe('Node');
});

test('cases returns all cases', function (): void {
    expect(ServerRole::cases())->toBe([
        ServerRole::Bastion,
        ServerRole::ControlPlane,
        ServerRole::Node,
    ]);
});
