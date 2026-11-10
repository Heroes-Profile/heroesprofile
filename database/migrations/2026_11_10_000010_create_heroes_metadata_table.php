<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-hero game data for the /Heroes pages. Written by UpdateTalentTooltips from the game decompile, latest build only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroes_metadata', function (Blueprint $table) {
            $table->tinyInteger('hero_id')->primary();
            $table->string('title', 100)->nullable();
            $table->string('franchise', 32)->nullable();
            $table->string('difficulty', 20)->nullable();
            $table->string('description', 500)->nullable();
            $table->text('lore')->nullable();
            $table->string('rarity', 20)->nullable();
            $table->boolean('is_melee')->nullable();
            $table->tinyInteger('rating_complexity')->unsigned()->nullable();
            $table->tinyInteger('rating_damage')->unsigned()->nullable();
            $table->tinyInteger('rating_survivability')->unsigned()->nullable();
            $table->tinyInteger('rating_utility')->unsigned()->nullable();
            $table->string('playstyles')->nullable();
            $table->integer('game_data_build')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes_metadata');
    }
};
