<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('replay_fingerprints', function (Blueprint $table) {
            $table->integer('replayID')->autoIncrement();
            $table->integer('hotsapi_replayID')->nullable();
            $table->string('fingerprint', 45)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->tinyInteger('checked_hotsapi')->default(0);
            $table->string('region', 45)->default('US')->collation('utf8mb4_0900_ai_ci');
            $table->tinyInteger('parsed')->default(0);
            $table->tinyInteger('valid')->nullable();
            $table->tinyInteger('deleted')->nullable();
            $table->integer('game_type')->nullable();

            $table->unique('fingerprint', 'fingerprint');
            $table->index(['replayID', 'parsed'], 'replayID_parsed');
            $table->index('hotsapi_replayID', 'hotsapiReplayID');
            $table->index('checked_hotsapi', 'checkedHotsapi');
            $table->index('parsed', 'parsed');
            $table->index(['region', 'parsed'], 'region_parsed');
            $table->index('valid', 'valid');
            $table->index('deleted', 'deleted');
        });

        DB::statement('ALTER TABLE `replay_fingerprints` ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replay_fingerprints');
    }
};
