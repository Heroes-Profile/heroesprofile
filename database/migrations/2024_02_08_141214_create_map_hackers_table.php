<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapHackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_hackers', function (Blueprint $table) {
            $table->id('map_hackers_id');
            $table->string('battletag', 255)->nullable();
            $table->tinyInteger('set_opt_out')->default(1);

            $table->unique('battletag', 'UNIQUE');
            $table->index(['battletag', 'set_opt_out'], 'INDEX');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('map_hackers');
    }
}
