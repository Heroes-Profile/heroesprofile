<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Community polls. Open until closes_at (NULL = open).
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_polls';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('polls')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('polls', function (Blueprint $table) {
            $table->increments('poll_id');
            $table->string('slug', 100);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('allow_multiple')->default(true);
            $table->dateTime('closes_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('slug');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `polls` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('polls');
    }
};
