<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Localized talent text, keyed by heroesprofile.heroes_data_talents.talent_id.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_translations';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('talents')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('talents', function (Blueprint $table) {
            $table->unsignedInteger('talent_id');
            $table->string('locale', 8);
            $table->string('name', 255);
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('raw_description')->nullable();

            $table->primary(['talent_id', 'locale']);
            $table->index('locale', 'talents_locale_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `talents` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('talents');
    }
};
