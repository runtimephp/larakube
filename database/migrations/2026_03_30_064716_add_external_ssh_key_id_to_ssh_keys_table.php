<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ssh_keys', function (Blueprint $table) {
            $table->string('external_ssh_key_id')->nullable()->after('purpose');
        });
    }
};
