<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Localized map names. English stays in heroesprofile.maps.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_translations';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('maps')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('maps', function (Blueprint $table) {
            $table->tinyInteger('map_id');
            $table->string('locale', 8);
            $table->string('name', 255);

            $table->primary(['map_id', 'locale']);
            $table->index('locale', 'maps_locale_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `maps` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('maps');
    }
};
