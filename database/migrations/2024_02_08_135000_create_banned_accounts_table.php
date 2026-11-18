<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannedAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banned_accounts', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('banned_accounts_id', true);
            $table->string('battletag', 45)->nullable();
            $table->integer('blizz_id')->nullable();
            $table->integer('region')->nullable();
            $table->longText('notes')->nullable();
            $table->dateTime('date_banned')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banned_accounts');
    }
}
