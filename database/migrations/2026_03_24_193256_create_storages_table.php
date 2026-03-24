<?php

declare(strict_types=1);

use App\Models\Infrastructure;
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
        Schema::create('storages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Infrastructure::class)
                ->constrained();
            $table->string('name');
            $table->string('external_volume_id')->nullable();
            $table->integer('size_gb')->nullable();
            $table->string('status')->default('healthy');
        });
    }
};
