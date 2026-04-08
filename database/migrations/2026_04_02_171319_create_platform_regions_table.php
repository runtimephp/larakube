<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_regions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignUuid('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_available');
            $table->json('metadata')->nullable();

            $table->unique(['provider_id', 'slug']);
        });
    }
};
