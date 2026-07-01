<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroesprofile_cache.friend_foe_cache', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->string('type', 10);
            $table->char('params_hash', 64)->unique();
            $table->unsignedBigInteger('latest_replayID')->default(0);
            $table->longText('data')->nullable();
            $table->timestamps();

            $table->index(['blizz_id', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_cache.friend_foe_cache');
    }
};
