<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Localized ability text, keyed like heroesprofile.heroes_data_abilities (hero_name + link_id).
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_translations';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('abilities')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('abilities', function (Blueprint $table) {
            $table->string('hero_name', 100);
            $table->string('link_id', 150);
            $table->string('locale', 8);
            $table->string('name', 255);
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->text('raw_description')->nullable();

            $table->primary(['hero_name', 'link_id', 'locale']);
            $table->index('locale', 'abilities_locale_index');
        });

        DB::connection(self::CONNECTION)->statement('ALTER TABLE `abilities` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('abilities');
    }
};
