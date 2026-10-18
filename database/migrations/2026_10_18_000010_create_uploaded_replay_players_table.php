<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The players of each Storm League replay as the upload-time parse (fingerprintOnly) sees them, kept
 * for a few days: ranks the uploader's rank reader sends for a replay are checked against them the
 * moment they arrive, before the full parse has filled `player`. Pruned as uploads come in
 * (ReplayUploadService), so it never holds more than a few days of games.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_replay_players', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('replayID');
            $table->string('battletag', 45);
            $table->unsignedTinyInteger('region');
            $table->unsignedTinyInteger('team');
            $table->dateTime('created_at');

            $table->unique(['replayID', 'battletag', 'region'], 'replay_player');
            $table->index('created_at');
        });

        DB::statement('ALTER TABLE `uploaded_replay_players` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_replay_players');
    }
};
