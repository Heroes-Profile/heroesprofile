<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The answers a poll offers, in sort_order.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_polls';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('poll_options')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('poll_options', function (Blueprint $table) {
            $table->increments('poll_option_id');
            $table->unsignedInteger('poll_id');
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->index('poll_id', 'poll_options_poll_id_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `poll_options` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('poll_options');
    }
};
