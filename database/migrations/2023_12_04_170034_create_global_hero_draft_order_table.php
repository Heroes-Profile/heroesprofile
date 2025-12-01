<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalHeroDraftOrderTable extends Migration
{
    public function up()
    {
        Schema::connection('heroesprofile_globals')->create('global_hero_draft_order', function (Blueprint $table) {
            $table->increments('global_hero_draft_order_id');
            $table->string('game_version', 45);
            $table->tinyInteger('game_type');
            $table->tinyInteger('league_tier');
            $table->tinyInteger('hero_league_tier')->default(0);
            $table->tinyInteger('role_league_tier')->default(0);
            $table->tinyInteger('game_map');
            $table->integer('hero_level');
            $table->integer('region');
            $table->tinyInteger('hero');
            $table->integer('pick_number')->nullable();
            $table->tinyInteger('win_loss')->nullable();
            $table->double('count')->default(0);

            $table->unique(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero', 'pick_number', 'win_loss'], 'unique');
            $table->index(['game_version', 'game_type', 'league_tier', 'hero_league_tier', 'role_league_tier', 'game_map', 'hero_level', 'region', 'hero', 'pick_number', 'win_loss', 'count'], 'Index_Count');
        });
    }

    public function down()
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_hero_draft_order');
    }
}
