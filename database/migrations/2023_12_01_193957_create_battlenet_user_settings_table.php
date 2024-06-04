<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattlenetUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battlenet_user_settings', function (Blueprint $table) {
            $table->id('battlenet_user_settings_id');
            $table->unsignedBigInteger('battlenet_accounts_id')->nullable();
            $table->string('setting')->nullable();
            $table->string('value')->nullable();

            // Indexes
            $table->index(['battlenet_accounts_id', 'setting', 'value'], 'battlenet_user_settings_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('battlenet_user_settings');
    }
}
