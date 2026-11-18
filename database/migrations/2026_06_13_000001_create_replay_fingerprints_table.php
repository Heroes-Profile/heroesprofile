<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('replay_fingerprints', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('replayID', true);
            $table->integer('hotsapi_replayID')->nullable();
            $table->string('fingerprint', 45)->nullable();
            $table->tinyInteger('checked_hotsapi')->nullable()->default(0);
            $table->string('region', 45)->nullable()->default('US');
            $table->tinyInteger('parsed')->nullable()->default(0);
            $table->tinyInteger('valid')->nullable();
            $table->tinyInteger('deleted')->nullable();
            $table->integer('game_type')->nullable();

            $table->index('checked_hotsapi', 'checkedHotsapi');
            $table->index('deleted', 'deleted');
            $table->unique('fingerprint', 'fingerprint');
            $table->index('hotsapi_replayID', 'hotsapiReplayID');
            $table->index('parsed', 'parsed');
            $table->index(['region', 'parsed'], 'region_parsed');
            $table->index(['replayID', 'parsed'], 'replayID_parsed');
            $table->index('valid', 'valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replay_fingerprints');
    }
};
