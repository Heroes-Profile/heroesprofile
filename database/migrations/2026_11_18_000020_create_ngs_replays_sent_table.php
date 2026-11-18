<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every replay NGS has sent for ingest.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_logs';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->create('ngs_replays_sent', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('request_id', true);
            $table->integer('replayID')->nullable();
            $table->string('dev_prod', 45)->nullable();
            $table->string('api_key', 250)->nullable();
            $table->string('tournament', 45)->nullable();
            $table->string('season', 45)->nullable();
            $table->string('division', 45)->nullable();
            $table->string('division_0', 45)->nullable();
            $table->string('division_1', 45)->nullable();
            $table->string('replay_url', 200)->nullable();
            $table->string('round', 45)->nullable();
            $table->string('game', 45)->nullable();
            $table->string('team_one_name', 200)->nullable();
            $table->string('team_one_player', 45)->nullable();
            $table->string('team_one_map_ban_1', 200)->nullable();
            $table->string('team_one_map_ban_2', 200)->nullable();
            $table->string('team_one_image_url', 200)->nullable();
            $table->string('team_two_name', 200)->nullable();
            $table->string('team_two_player', 45)->nullable();
            $table->string('team_two_map_ban_1', 200)->nullable();
            $table->string('team_two_map_ban_2', 200)->nullable();
            $table->string('team_two_image_url', 200)->nullable();
            $table->string('return_data', 10000)->nullable();
            $table->dateTime('date_sent')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('ngs_replays_sent');
    }
};
