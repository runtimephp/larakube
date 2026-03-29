<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infrastructures', function (Blueprint $table) {
            $table->string('provisioning_step')->nullable()->after('status');
            $table->string('provisioning_phase')->nullable()->after('provisioning_step');
        });

        Schema::table('kubernetes_clusters', function (Blueprint $table) {
            $table->text('kubeconfig')->nullable()->after('status');
            $table->string('api_endpoint')->nullable()->after('kubeconfig');
            $table->string('pod_cidr')->nullable()->after('api_endpoint');
            $table->string('service_cidr')->nullable()->after('pod_cidr');
            $table->string('topology')->nullable()->after('service_cidr');
        });
    }
};
