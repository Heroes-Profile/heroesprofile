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
            $table->increments('battlenet_accounts_id');
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

            // Cursor for the API privacy change feed. `updated_at` moves for token
            // refreshes and flair changes too, so it cannot serve as one.
            $table->timestamp('private_changed_at')->nullable();

            // Grants ad-free and site flair regardless of Patreon status.
            // Read by CheckIfPatreonSupporter and listed in BattlenetAccount::$fillable.
            $table->tinyInteger('flair_adfree_override')->default(0);

            // Indexes
            $table->index('battlenet_id');
            $table->index('private_changed_at');
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
