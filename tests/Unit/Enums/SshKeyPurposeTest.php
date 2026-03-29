<?php

declare(strict_types=1);

use App\Enums\SshKeyPurpose;

test('label returns correct labels', function (): void {
    expect(SshKeyPurpose::Bastion->label())->toBe('Bastion')
        ->and(SshKeyPurpose::Node->label())->toBe('Node');
});

test('cases returns all cases', function (): void {
    expect(SshKeyPurpose::cases())->toBe([
        SshKeyPurpose::Bastion,
        SshKeyPurpose::Node,
    ]);
});
