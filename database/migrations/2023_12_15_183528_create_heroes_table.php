<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeroesTable extends Migration
{
    public function up()
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name')->unique();
            $table->string('short_name');
            $table->string('alt_name')->nullable();
            $table->string('role')->nullable();
            $table->string('new_role')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('release_date')->nullable();
            $table->dateTime('rework_date')->nullable();
            $table->string('last_change_patch_version')->nullable();
            $table->char('attribute_id', 4)->nullable();
            $table->string('build_copy_name')->nullable();

            $table->index('name', 'heroes_name_index');
            $table->index('attribute_id', 'heroes_shortcut_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('heroes');
    }
}
