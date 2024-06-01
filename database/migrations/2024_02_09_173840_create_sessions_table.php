<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_cache.sessions', function (Blueprint $table) {
            $table->string('id', 700)->collation('utf8mb4_0900_ai_ci');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('ip_address', 45)->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->text('user_agent')->nullable()->collation('utf8mb4_0900_ai_ci');
            $table->longText('payload')->collation('utf8mb4_0900_ai_ci');
            $table->integer('last_activity');
            
            // Unique constraint
            $table->unique('id', 'sessions_id_unique');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
