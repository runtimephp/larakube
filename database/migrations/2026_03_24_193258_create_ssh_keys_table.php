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
        Schema::create('ssh_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Infrastructure::class)
                ->constrained();
            $table->string('name');
            $table->string('fingerprint');
            $table->text('public_key')->nullable();
        });
    }
};
