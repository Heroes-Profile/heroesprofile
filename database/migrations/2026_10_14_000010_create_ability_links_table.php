<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replay ability link (abilLink) -> ability, per game build.
 *
 * Rebuilt from the game data and checked against replays; names, slots and icons
 * come from HeroesDataParser. hero_id is NULL for shared abilities (mount, hearth, map).
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('ability_links')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('ability_links', function (Blueprint $table) {
            $table->unsignedInteger('build');
            $table->unsignedSmallInteger('abil_link');
            $table->string('ability_id', 100);
            $table->unsignedInteger('hero_id')->nullable();
            $table->string('slot', 32)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('icon', 150)->nullable();
            $table->string('hdp_link_id', 200)->nullable();
            $table->string('confidence', 10);
            // Base cooldown in seconds and max charges (NULL = single charge), from HeroesDataParser
            $table->decimal('cooldown', 6, 2)->nullable();
            $table->unsignedTinyInteger('charges')->nullable();

            $table->primary(['build', 'abil_link']);
            $table->index('hero_id', 'hero_id');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `ability_links` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('ability_links');
    }
};
