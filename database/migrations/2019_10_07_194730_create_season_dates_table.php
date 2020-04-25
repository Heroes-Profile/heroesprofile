<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeasonDatesTable extends Migration
{

    /**
     * The database schema.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection(config('database.default'));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('season_dates', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('id')->unsigned();
          $table->integer('year');
          $table->double('season');
          $table->dateTime('start_date');
          $table->dateTime('end_date');

          $table->primary('id');
        });
        DB::statement("ALTER TABLE season_dates CHANGE COLUMN id id INT(11) NOT NULL AUTO_INCREMENT");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('season_dates');
    }
}
