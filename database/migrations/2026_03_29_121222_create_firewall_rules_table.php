<?php

declare(strict_types=1);

use App\Models\Firewall;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firewall_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->foreignIdFor(Firewall::class)->constrained();
            $table->string('direction');
            $table->string('protocol');
            $table->unsignedInteger('port_start');
            $table->unsignedInteger('port_end');
            $table->json('source_ips')->nullable();
            $table->json('destination_ips')->nullable();
        });
    }
};
