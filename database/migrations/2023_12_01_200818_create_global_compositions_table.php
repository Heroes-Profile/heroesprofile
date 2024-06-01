<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalCompositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_compositions', function (Blueprint $table) {
            $table->id('global_compositions_id');
            $table->string('game_version');
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->unsignedInteger('hero_level');
            $table->integer('composition_id');
            $table->integer('hero')->nullable();
            $table->tinyInteger('mirror')->default(0);
            $table->integer('region');
            $table->tinyInteger('win_loss');
            $table->unsignedInteger('games_played')->default(0);
            $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'composition_id', 'hero', 'mirror', 'region', 'win_loss'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'composition_id', 'hero', 'mirror', 'region', 'win_loss', 'games_played'], 'primary_gamesPlayed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_compositions');
    }
}
