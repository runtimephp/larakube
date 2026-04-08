<?php

declare(strict_types=1);

use App\Models\PlatformRegion;
use App\Models\Provider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Provider::class)
                ->constrained();
            $table->foreignIdFor(PlatformRegion::class)
                ->constrained();
            $table->string('name');
            $table->text('kubeconfig')->nullable();
            $table->string('status');
            $table->unique(['provider_id', 'platform_region_id']);
        });
    }
};
