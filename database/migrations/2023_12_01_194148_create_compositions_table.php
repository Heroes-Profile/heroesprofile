<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compositions', function (Blueprint $table) {
            $table->id('composition_id');
            $table->unsignedBigInteger('role_one')->nullable();
            $table->unsignedBigInteger('role_two')->nullable();
            $table->unsignedBigInteger('role_three')->nullable();
            $table->unsignedBigInteger('role_four')->nullable();
            $table->unsignedBigInteger('role_five')->nullable();

            // Indexes
            $table->unique(['role_one', 'role_two', 'role_three', 'role_four', 'role_five'], 'Unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compositions');
    }
}
