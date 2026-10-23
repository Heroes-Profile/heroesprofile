<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replays Globals_Calculator's real-rank pass has added to the *_real_rank tables, so none is counted twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('heroesprofile_globals')->create('global_real_rank_processed', function (Blueprint $table) {
            $table->unsignedInteger('replayID')->primary();
        });
    }

    public function down(): void
    {
        Schema::connection('heroesprofile_globals')->dropIfExists('global_real_rank_processed');
    }
};
