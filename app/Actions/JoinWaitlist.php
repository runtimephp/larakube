<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Facades\DB;

final class JoinWaitlist
{
    public function handle(string $email): void
    {
        DB::table('waitlist_entries')->insertOrIgnore([
            'email' => $email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
