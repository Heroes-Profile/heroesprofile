<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeroesTranslationsTable extends Migration
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
        $this->schema->create('heroes_translations', function (Blueprint $table) {
          $table->engine = 'InnoDB';
          $table->string('name', 45);
          $table->string('short_name', 45);
          $table->string('translation', 45);
          $table->string('attribute_id', 45);

          $table->primary(['name', 'short_name', 'translation', 'attribute_id'], 'Primary_Index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('heroes_translations');
    }
}
