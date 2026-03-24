<?php

declare(strict_types=1);

use App\Models\CloudProvider;
use App\Models\Organization;
use App\Models\Region;
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
        Schema::create('infrastructures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Organization::class)
                ->constrained();
            $table->foreignIdFor(CloudProvider::class)
                ->constrained();
            $table->foreignIdFor(Region::class)
                ->nullable()
                ->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('healthy');
        });
    }
};
