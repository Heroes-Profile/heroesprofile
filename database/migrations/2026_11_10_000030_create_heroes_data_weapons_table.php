<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Basic attacks per hero unit. is_disabled weapons are ones a talent switches on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroes_data_weapons', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('hero_id');
            $table->string('unit_id', 64);
            $table->string('weapon_id', 100);
            $table->boolean('is_disabled');
            $table->decimal('damage', 10, 4);
            $table->decimal('damage_scale', 10, 4);
            $table->decimal('range', 10, 4);
            $table->decimal('period', 10, 4);
            $table->tinyInteger('sort')->unsigned();

            $table->unique(['hero_id', 'unit_id', 'weapon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes_data_weapons');
    }
};
