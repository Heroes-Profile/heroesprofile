<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Localized hero text. English stays in heroesprofile.heroes.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_translations';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('heroes')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('heroes', function (Blueprint $table) {
            $table->tinyInteger('hero_id');
            $table->string('locale', 8);
            $table->string('name', 100);
            $table->string('title', 255)->nullable();
            $table->string('role', 50)->nullable();
            $table->string('expanded_role', 50)->nullable();
            $table->text('description')->nullable();
            $table->text('lore')->nullable();

            $table->primary(['hero_id', 'locale']);
            $table->index('locale', 'heroes_locale_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `heroes` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('heroes');
    }
};
