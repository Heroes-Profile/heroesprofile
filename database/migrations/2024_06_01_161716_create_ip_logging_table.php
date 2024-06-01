<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpLoggingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_logging', function (Blueprint $table) {
            $table->increments('ip_logging_id');
            $table->string('ip', 45)->nullable();
            $table->string('page', 500)->nullable();
            $table->longText('user_agent')->nullable();
            $table->timestamp('date_time')->useCurrent()->nullable();
            $table->primary('ip_logging_id');
            $table->index(['ip', 'page'], 'INDEX');
            $table->index('date_time', 'dateTime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ip_logging');
    }
}
