<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattlenetAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battlenet_accounts', function (Blueprint $table) {
            $table->id('battlenet_accounts_id');
            $table->unsignedBigInteger('battlenet_id')->nullable();
            $table->string('battletag')->unique();
            $table->unsignedBigInteger('blizz_id')->nullable();
            $table->string('region')->nullable();
            $table->string('battlenet_access_token')->nullable();
            $table->string('remember_token')->nullable();
            $table->tinyInteger('patreon')->nullable();
            $table->timestamps();
            $table->longText('response')->nullable();
            $table->tinyInteger('private')->nullable();

            // Indexes
            $table->index('battlenet_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('battlenet_accounts');
    }
}
