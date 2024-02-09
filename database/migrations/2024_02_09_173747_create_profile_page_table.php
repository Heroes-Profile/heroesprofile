<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.profile_page', function (Blueprint $table) {
            $table->id('profile_page_id');
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->integer('game_type')->nullable();
            $table->integer('season')->nullable();
            $table->integer('wins')->nullable();
            $table->integer('losses')->nullable();
            $table->integer('first_to_ten_wins')->nullable();
            $table->integer('first_to_ten_losses')->nullable();
            $table->integer('second_to_ten_wins')->nullable();
            $table->integer('second_to_ten_losses')->nullable();
            $table->integer('bruiser_wins')->nullable();
            $table->integer('bruiser_losses')->nullable();
            $table->integer('support_wins')->nullable();
            $table->integer('support_losses')->nullable();
            $table->integer('ranged_assassin_wins')->nullable();
            $table->integer('ranged_assassin_losses')->nullable();
            $table->integer('melee_assassin_wins')->nullable();
            $table->integer('melee_assassin_losses')->nullable();
            $table->integer('healer_wins')->nullable();
            $table->integer('healer_losses')->nullable();
            $table->integer('tank_wins')->nullable();
            $table->integer('tank_losses')->nullable();
            $table->integer('total_time_played')->nullable();
            $table->integer('account_level')->nullable();
            $table->integer('kills')->nullable();
            $table->integer('deaths')->nullable();
            $table->integer('takedowns')->nullable();
            $table->longText('hero_data')->nullable();
            $table->longText('map_data')->nullable();
            $table->longText('matches')->nullable();
            $table->integer('mvp_games')->nullable();
            $table->integer('games_mvp')->nullable();
            $table->integer('time_on_fire_games')->nullable();
            $table->integer('time_on_fire_total')->nullable();
            $table->string('stack_one_wins')->nullable();
            $table->string('stack_one_losses')->nullable();
            $table->string('stack_two_wins')->nullable();
            $table->string('stack_two_losses')->nullable();
            $table->string('stack_three_wins')->nullable();
            $table->string('stack_three_losses')->nullable();
            $table->string('stack_four_wins')->nullable();
            $table->string('stack_four_losses')->nullable();
            $table->string('stack_five_wins')->nullable();
            $table->string('stack_five_losses')->nullable();
            $table->integer('latest_replayID')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['blizz_id', 'region', 'game_type', 'season'], 'Unique');
            // Index
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
        Schema::dropIfExists('profile_page');
    }
}
