<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroesDataTalentsTable extends Migration
{
    public function up()
    {
        Schema::create('heroes_data_talents', function (Blueprint $table) {
            $table->id('talent_id');
            $table->string('hero_name');
            $table->string('short_name');
            $table->string('attribute_id');
            $table->string('title');
            $table->string('talent_name');
            $table->string('description', 500);
            $table->string('status')->default('playable');
            $table->string('hotkey');
            $table->string('cooldown');
            $table->string('mana_cost');
            $table->string('sort');
            $table->integer('level');
            $table->string('icon');
            $table->unsignedBigInteger('required_talent_id')->nullable();

            $table->unique(['hero_name', 'title', 'talent_name'], 'Unique');
            $table->index('hero_name');
            $table->index('attribute_id');
            $table->index(['hero_name', 'title', 'talent_name'], 'heroTitleTalent');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes_data_talents');
    }
}
