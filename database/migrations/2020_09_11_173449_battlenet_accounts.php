<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BattlenetAccounts extends Migration
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
    $this->schema->create('battlenet_accounts', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->integer('battlenet_accounts_id')->autoIncrement();
      $table->integer('battlenet_id');
      $table->string('battletag', 255);
      $table->string('region', 255);
      $table->string('battlenet_access_token', 255);
      $table->string('remember_token', 255);
      $table->timestamps();

      $table->primary('battlenet_accounts_id');
      $table->unique('battletag', 'battlenet_accounts_unique');
      $table->index('battlenet_id', 'battlenet_accounts_index');

    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    $this->schema->dropIfExists('battlenet_accounts');
  }
}
