<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill of the two tables recording where a replay came from. Both sit beside
 * `replay_fingerprints` in the main schema.
 *
 * Index names are reproduced exactly as production has them, typo included, so a
 * schema built from migrations matches what is deployed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('uploaded_replay_data')) {
            Schema::create('uploaded_replay_data', function (Blueprint $table) {
                $table->increments('uploaded_replay_data_id');
                $table->integer('replayID')->nullable();
                $table->string('uploaded_filename', 255)->nullable();
                $table->string('uploaded_source', 255)->nullable();
                $table->string('uploader_version', 45)->nullable();
                $table->integer('uploader_compile_checker')->nullable();
                $table->dateTime('uploader_start_diff')->nullable();
                $table->dateTime('game_date')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->integer('upload_team')->nullable();
                $table->string('ip', 255)->nullable();

                $table->index('uploaded_source', 'INDEX1');
                $table->index(['replayID', 'uploaded_source'], 'IDNEX2');
                $table->index('uploaded_filename', 'INDEX3');
                $table->index('uploader_version', 'INDEX4');
                $table->index('uploader_compile_checker', 'INDEX5');
                $table->index('ip', 'INDEX6');
            });

            DB::statement('ALTER TABLE `uploaded_replay_data` ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
        }

        // Singular, as production has it.
        if (! Schema::hasTable('uploader_source_change')) {
            Schema::create('uploader_source_change', function (Blueprint $table) {
                $table->increments('uploader_source_change_id');
                $table->integer('replayID')->nullable();
                $table->string('source', 45)->nullable();
                $table->tinyInteger('parsed')->nullable()->default(0);
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('created_at')->nullable();
            });

            DB::statement('ALTER TABLE `uploader_source_change` ENGINE=InnoDB ROW_FORMAT=COMPRESSED DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('uploader_source_change');
        Schema::dropIfExists('uploaded_replay_data');
    }
};
