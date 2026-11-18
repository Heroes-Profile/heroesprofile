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
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->integer('battlenet_user_settings_id', true);
            $table->integer('battlenet_accounts_id')->nullable();
            $table->string('setting')->nullable();
            $table->string('value')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();

            // Indexes
            $table->index(['battlenet_accounts_id', 'setting', 'value'], 'INDEX');
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
