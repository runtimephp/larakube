<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformRole;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'platform_role' => PlatformRole::Admin,
        ]);

        $this->call(ProviderSeeder::class);
        $this->call(ManagementClusterSeeder::class);
    }
}
