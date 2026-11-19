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
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('profile_page_id', true);
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->string('game_type', 45)->nullable();
            $table->string('season', 45)->nullable();
            $table->integer('wins')->nullable()->default(0);
            $table->integer('losses')->nullable()->default(0);
            $table->integer('first_to_ten_wins')->nullable()->default(0);
            $table->integer('first_to_ten_losses')->nullable()->default(0);
            $table->integer('second_to_ten_wins')->nullable()->default(0);
            $table->integer('second_to_ten_losses')->nullable()->default(0);
            $table->integer('bruiser_wins')->nullable()->default(0);
            $table->integer('bruiser_losses')->nullable()->default(0);
            $table->integer('support_wins')->nullable()->default(0);
            $table->integer('support_losses')->nullable()->default(0);
            $table->integer('ranged_assassin_wins')->nullable()->default(0);
            $table->integer('ranged_assassin_losses')->nullable()->default(0);
            $table->integer('melee_assassin_wins')->nullable()->default(0);
            $table->integer('melee_assassin_losses')->nullable()->default(0);
            $table->integer('healer_wins')->nullable()->default(0);
            $table->integer('healer_losses')->nullable()->default(0);
            $table->integer('tank_wins')->nullable()->default(0);
            $table->integer('tank_losses')->nullable()->default(0);
            $table->integer('total_time_played')->nullable()->default(0);
            $table->integer('account_level')->nullable()->default(0);
            $table->integer('kills')->nullable()->default(0);
            $table->integer('deaths')->nullable()->default(0);
            $table->integer('takedowns')->nullable()->default(0);
            $table->longText('hero_data')->nullable();
            $table->longText('map_data')->nullable();
            $table->longText('matches')->nullable();
            $table->longText('weekday_data')->nullable();
            $table->integer('mvp_games')->nullable()->default(0);
            $table->integer('games_mvp')->nullable()->default(0);
            $table->integer('time_on_fire_games')->nullable()->default(0);
            $table->integer('time_on_fire_total')->nullable()->default(0);
            $table->integer('stack_one_wins')->nullable()->default(0);
            $table->integer('stack_one_losses')->nullable()->default(0);
            $table->integer('stack_two_wins')->nullable()->default(0);
            $table->integer('stack_two_losses')->nullable()->default(0);
            $table->integer('stack_three_wins')->nullable()->default(0);
            $table->integer('stack_three_losses')->nullable()->default(0);
            $table->integer('stack_four_wins')->nullable()->default(0);
            $table->integer('stack_four_losses')->nullable()->default(0);
            $table->integer('stack_five_wins')->nullable()->default(0);
            $table->integer('stack_five_losses')->nullable()->default(0);
            $table->integer('latest_replayID')->nullable();
            $table->unsignedSmallInteger('cache_version')->default(0);

            $table->index(['season', 'latest_replayID'], 'Season');
            // Unique constraint
            $table->unique(['blizz_id', 'region', 'game_type', 'season'], 'Unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_cache.profile_page');
    }
}
