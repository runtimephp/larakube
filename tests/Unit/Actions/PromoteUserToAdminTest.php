<?php

declare(strict_types=1);

use App\Actions\PromoteUserToAdmin;
use App\Enums\PlatformRole;
use App\Models\User;

beforeEach(function (): void {
    $this->action = app(PromoteUserToAdmin::class);
});

test('promotes a member to admin',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $result = $this->action->handle($user);

        expect($result->platform_role)->toBe(PlatformRole::Admin)
            ->and($result->id)->toBe($user->id);
    });

test('returns fresh instance after promotion',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $result = $this->action->handle($user);

        $fromDb = User::query()->find($user->id);

        expect($fromDb->platform_role)->toBe(PlatformRole::Admin)
            ->and($result->platform_role)->toBe($fromDb->platform_role);
    });
