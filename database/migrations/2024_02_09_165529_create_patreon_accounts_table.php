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
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('patreon_accounts_id', true);
            $table->integer('battlenet_accounts_id')->nullable();
            $table->string('patreon_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->tinyInteger('site_flair')->nullable()->default(0);
            $table->tinyInteger('ad_free')->nullable()->default(0);
            // The pledge amount, so API tier thresholds live in config rather than
            // needing a new boolean and a PatreonSubscriberChecker release per tier.
            // `ad_free` cannot serve: it is one flag set at $5 and up, so a $5 and a
            // $10 supporter are indistinguishable through it.
            $table->integer('currently_entitled_amount_cents')->nullable();
            $table->string('access_token')->nullable();
            $table->string('remember_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->index('battlenet_accounts_id', 'patreon_accounts_battlenet_accounts_id');
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
