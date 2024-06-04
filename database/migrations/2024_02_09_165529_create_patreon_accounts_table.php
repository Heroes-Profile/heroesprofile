<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatreonAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patreon_accounts', function (Blueprint $table) {
            $table->id('patreon_accounts_id');
            $table->unsignedBigInteger('battlenet_accounts_id')->nullable();
            $table->string('patreon_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('site_flair')->default(0);
            $table->tinyInteger('ad_free')->default(0);
            $table->string('access_token')->nullable();
            $table->string('remember_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamps(); // Add this line if you want timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patreon_accounts');
    }
}
