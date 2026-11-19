<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroesprofile_cache.friend_foe_cache', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('blizz_id');
            $table->unsignedTinyInteger('region');
            $table->string('type', 10);
            $table->char('params_hash', 64);
            $table->unsignedBigInteger('latest_replayID')->default(0);
            $table->longText('data')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['blizz_id', 'region'], 'friend_foe_cache_blizz_id_region_index');
            $table->unique('params_hash', 'friend_foe_cache_params_hash_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_cache.friend_foe_cache');
    }
};
