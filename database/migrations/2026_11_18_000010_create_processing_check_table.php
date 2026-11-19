<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Switches and settings the background processors read, one row per process.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_processing';

    public function up(): void
    {
        Schema::connection(self::CONNECTION)->create('processing_check', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('processing_check', true);
            $table->string('process', 45)->nullable();
            $table->tinyInteger('running')->nullable();
            $table->integer('stat_id_ran')->nullable()->default(0);
            $table->tinyInteger('complete')->nullable()->default(0);
            $table->decimal('sample_rate', 5, 2)->nullable();

            $table->index(['process', 'running'], 'index');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('processing_check');
    }
};
