<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroesDataTalentsTable extends Migration
{
    public function up()
    {
        Schema::create('heroes_data_talents', function (Blueprint $table) {
            $table->engine = 'InnoDB ROW_FORMAT=COMPRESSED';
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->increments('talent_id');
            $table->string('hero_name', 50);
            $table->string('short_name', 50);
            $table->string('attribute_id', 10);
            $table->string('title', 50);
            $table->string('talent_name', 100);
            $table->text('description');
            $table->string('status', 45)->nullable()->default('playable');
            $table->string('hotkey', 100);
            $table->string('cooldown', 10);
            $table->string('mana_cost', 10);
            $table->string('sort', 10);
            $table->integer('level');
            $table->string('icon', 100);
            $table->integer('required_talent_id')->nullable();
            $table->string('link_id', 150)->nullable();
            $table->string('ability_link_id', 150)->nullable();
            $table->string('short_description')->nullable();
            $table->text('raw_description')->nullable();
            $table->boolean('is_quest')->nullable();

            $table->index('attribute_id', 'attribute_id');
            $table->index(['hero_name', 'title', 'talent_name'], 'heroTitleTalent');
            $table->index('hero_name', 'name');
            $table->unique(['hero_name', 'title', 'talent_name'], 'Unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes_data_talents');
    }
}
