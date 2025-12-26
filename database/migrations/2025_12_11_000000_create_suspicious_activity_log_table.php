<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuspiciousActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('heroesprofile_logs.suspicious_activity_log', function (Blueprint $table) {
            $table->id('suspicious_activity_log_id');
            $table->string('ip', 45)->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('path', 500)->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamp('date_time')->useCurrent()->nullable();
            $table->index('ip', 'idx_ip');
            $table->index('date_time', 'idx_date_time');
            $table->index(['ip', 'date_time'], 'idx_ip_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('heroesprofile_logs.suspicious_activity_log');
    }
}



