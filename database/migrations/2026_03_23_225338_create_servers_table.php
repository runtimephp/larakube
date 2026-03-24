<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('cloud_provider_id')->constrained()->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name');
            $table->string('status');
            $table->string('type');
            $table->string('region');
            $table->string('ipv4')->nullable();
            $table->string('ipv6')->nullable();
            $table->json('metadata')->nullable();

            $table->unique(['cloud_provider_id', 'external_id']);
        });
    }
};
