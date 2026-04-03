<?php

declare(strict_types=1);

use App\Enums\ManagementClusterStatus;
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
            $table->string('name');
            $table->string('region');
            $table->string('provider');
            $table->text('kubeconfig')->nullable();
            $table->string('status')->default(ManagementClusterStatus::Bootstrapping->value);
            $table->unique(['provider', 'region']);
        });
    }
};
