<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroesDataAbilitiesTable extends Migration
{
    public function up()
    {
        Schema::create('heroes_data_abilities', function (Blueprint $table) {
            $table->string('hero_name');
            $table->string('short_name');
            $table->string('attribute_id');
            $table->string('title');
            $table->string('description', 500);
            $table->string('mana_cost');
            $table->string('cooldown');
            $table->string('trait');
            $table->string('name');
            $table->string('hotkey');
            $table->string('icon');
            $table->unique(['hero_name', 'title']);
            $table->index('attribute_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes_data_abilities');
    }
}
