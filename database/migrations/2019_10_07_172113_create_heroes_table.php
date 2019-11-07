<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile.heroes', function (Blueprint $table) {
          $table->engine = 'InnoDB';

          $table->integer('id')->autoIncrement()->unsigned();
          $table->string('name', 255);
          $table->string('short_name', 32);
          $table->string('alt_name', 45);
          $table->string('role', 32);
          $table->string('new_role', 45);
          $table->string('type', 32);
          $table->dateTime('release_date');
          $table->dateTime('rework_date');
          $table->char('attribute_id');
          $table->unique('name');
          $table->index('attribute_id');

        });

        /*
          `release_date` datetime DEFAULT NULL,
          `rework_date` datetime DEFAULT NULL,
          `attribute_id` char(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `Hero` (`name`),
          KEY `heroes_name_index` (`name`(191)),
          KEY `heroes_shortcut_index` (`attribute_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;

        */

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroes');
    }
}
