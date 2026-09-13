<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Running Xal'atath totals per game type for the Void Corruption site event.
 *
 * Written by Globals_Calculator as replays are processed. Raw totals only; any
 * themed conversion happens on display.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('xalatath_data')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('xalatath_data', function (Blueprint $table) {
            $table->unsignedTinyInteger('game_type')->primary();
            $table->unsignedBigInteger('games_played')->default(0);
            $table->unsignedBigInteger('wins')->default(0);
            $table->unsignedBigInteger('bans')->default(0);
            $table->unsignedBigInteger('takedowns')->default(0);
            $table->unsignedBigInteger('deaths')->default(0);
            $table->unsignedBigInteger('siege_damage')->default(0);
            $table->unsignedBigInteger('spell_damage')->default(0);
            $table->unsignedBigInteger('multikill')->default(0);
            $table->unsignedBigInteger('time_cc_enemy_heroes')->default(0);
            $table->unsignedBigInteger('teamfight_hero_damage')->default(0);
            $table->unsignedBigInteger('on_fire_time')->default(0);

            // Best single-game streak, not a sum.
            $table->unsignedBigInteger('highest_kill_streak')->default(0);

            $table->unsignedBigInteger('escapes')->default(0);
            $table->unsignedBigInteger('outnumbered_deaths')->default(0);
            $table->unsignedBigInteger('time_spent_dead')->default(0);
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('xalatath_data');
    }
};
