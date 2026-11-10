<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Base stats per hero unit: the main unit plus any extra units (Lost Vikings, Chen's spirits, Misha...).
 * *_scale columns are the per-level rate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroes_data_units', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('hero_id');
            $table->string('unit_id', 64);
            $table->string('name', 100);
            $table->boolean('is_main');
            $table->tinyInteger('sort')->unsigned();
            $table->decimal('life', 10, 4)->nullable();
            $table->decimal('life_scale', 10, 4)->nullable();
            $table->decimal('life_regen', 10, 4)->nullable();
            $table->decimal('life_regen_scale', 10, 4)->nullable();
            $table->decimal('shield', 10, 4)->nullable();
            $table->decimal('shield_scale', 10, 4)->nullable();
            $table->decimal('shield_regen', 10, 4)->nullable();
            $table->decimal('shield_regen_delay', 10, 4)->nullable();
            $table->string('energy_type', 32)->nullable();
            $table->decimal('energy', 10, 4)->nullable();
            $table->decimal('energy_regen', 10, 4)->nullable();
            $table->decimal('move_speed', 10, 4)->nullable();
            $table->decimal('sight', 10, 4)->nullable();

            $table->unique(['hero_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes_data_units');
    }
};
