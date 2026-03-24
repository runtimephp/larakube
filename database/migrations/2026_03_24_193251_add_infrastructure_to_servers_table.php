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
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignUuid('infrastructure_id')
                ->constrained();
            $table->foreignUuid('kubernetes_cluster_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('role')->nullable();
        });
    }
};
