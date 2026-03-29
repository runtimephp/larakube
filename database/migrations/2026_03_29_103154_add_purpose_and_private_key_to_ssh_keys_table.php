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
        Schema::table('ssh_keys', function (Blueprint $table) {
            $table->string('purpose')->after('public_key');
            $table->text('private_key')->nullable()->after('purpose');
        });
    }
};
