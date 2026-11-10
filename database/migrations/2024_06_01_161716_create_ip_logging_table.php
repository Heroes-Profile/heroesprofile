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
        Schema::create('heroesprofile_logs.ip_logging', function (Blueprint $table) {
            $table->id('ip_logging_id');
            $table->string('ip', 45)->nullable();
            $table->string('page', 500)->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('sec_ch_ua', 255)->nullable();
            $table->string('sec_ch_ua_mobile', 10)->nullable();
            $table->string('sec_ch_ua_platform', 50)->nullable();
            $table->string('sec_fetch_site', 30)->nullable();
            $table->string('sec_fetch_mode', 30)->nullable();
            $table->string('sec_fetch_dest', 30)->nullable();
            $table->string('accept_language', 255)->nullable();
            $table->string('accept_encoding', 255)->nullable();
            $table->string('referer', 500)->nullable();
            $table->timestamp('date_time')->useCurrent()->nullable();
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
        Schema::dropIfExists('heroesprofile_logs.ip_logging');
    }
}
