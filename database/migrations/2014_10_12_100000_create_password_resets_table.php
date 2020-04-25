<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
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
        $this->schema->create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('password_resets');
    }
}
