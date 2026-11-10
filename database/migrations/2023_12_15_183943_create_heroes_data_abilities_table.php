<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroesDataAbilitiesTable extends Migration
{
    public function up()
    {
        Schema::create('heroes_data_abilities', function (Blueprint $table) {
            $table->string('hero_name', 100);
            $table->string('short_name', 100);
            $table->string('attribute_id', 10);
            $table->string('title', 50);
            $table->string('description', 500);
            $table->string('mana_cost', 10);
            $table->string('cooldown', 10);
            $table->string('trait', 5);
            $table->string('name');
            $table->string('hotkey', 5);
            $table->string('icon', 500);
            $table->string('link_id', 150)->nullable();
            $table->string('unit_id', 64)->nullable();
            $table->string('ability_type', 20)->nullable();
            $table->string('parent_link_id', 150)->nullable();
            $table->string('short_description')->nullable();
            $table->text('raw_description')->nullable();
            $table->smallInteger('charges')->unsigned()->nullable();
            $table->decimal('charge_cooldown', 6, 2)->nullable();
            $table->tinyInteger('sort')->unsigned()->nullable();
            $table->unique(['hero_name', 'link_id']);
            $table->index('attribute_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes_data_abilities');
    }
}
