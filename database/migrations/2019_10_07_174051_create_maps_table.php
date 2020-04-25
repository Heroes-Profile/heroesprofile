<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMapsTable extends Migration
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
        $this->schema->create('maps', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->integer('map_id')->unsigned();
          $table->string('name', 255);
          $table->string('short_name', 255);
          $table->string('type', 255);

          $table->primary(['map_id', 'name']);
        });
        DB::statement("ALTER TABLE maps CHANGE COLUMN map_id map_id INT(11) NOT NULL AUTO_INCREMENT");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('maps');
    }
}
