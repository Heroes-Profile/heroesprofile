<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reset tokens for API accounts. Laravel 8 shape, matching production — no primary
 * key, which Laravel's token repository doesn't need.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('password_resets')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('password_resets');
    }
};
