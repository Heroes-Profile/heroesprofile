<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLaravelProfilePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.laravel_profile_page', function (Blueprint $table) {
            $table->id('profile_page_id');
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->string('game_type')->nullable();
            $table->string('season')->nullable();
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('first_to_ten_wins')->default(0);
            $table->integer('first_to_ten_losses')->default(0);
            $table->integer('second_to_ten_wins')->default(0);
            $table->integer('second_to_ten_losses')->default(0);
            $table->integer('bruiser_wins')->default(0);
            $table->integer('bruiser_losses')->default(0);
            $table->integer('support_wins')->default(0);
            $table->integer('support_losses')->default(0);
            $table->integer('ranged_assassin_wins')->default(0);
            $table->integer('ranged_assassin_losses')->default(0);
            $table->integer('melee_assassin_wins')->default(0);
            $table->integer('melee_assassin_losses')->default(0);
            $table->integer('healer_wins')->default(0);
            $table->integer('healer_losses')->default(0);
            $table->integer('tank_wins')->default(0);
            $table->integer('tank_losses')->default(0);
            $table->integer('total_time_played')->default(0);
            $table->integer('account_level')->default(0);
            $table->integer('kills')->default(0);
            $table->integer('deaths')->default(0);
            $table->integer('takedowns')->default(0);
            $table->longText('hero_data')->nullable();
            $table->longText('map_data')->nullable();
            $table->longText('matches')->nullable();
            $table->integer('mvp_games')->default(0);
            $table->integer('games_mvp')->default(0);
            $table->integer('time_on_fire_games')->default(0);
            $table->integer('time_on_fire_total')->default(0);
            $table->integer('stack_one_wins')->default(0);
            $table->integer('stack_one_losses')->default(0);
            $table->integer('stack_two_wins')->default(0);
            $table->integer('stack_two_losses')->default(0);
            $table->integer('stack_three_wins')->default(0);
            $table->integer('stack_three_losses')->default(0);
            $table->integer('stack_four_wins')->default(0);
            $table->integer('stack_four_losses')->default(0);
            $table->integer('stack_five_wins')->default(0);
            $table->integer('stack_five_losses')->default(0);
            $table->integer('latest_replayID')->nullable();
            $table->unique(['blizz_id', 'region', 'game_type', 'season'], 'Unique');
            $table->index('season', 'Season');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laravel_profile_page');
    }
}
