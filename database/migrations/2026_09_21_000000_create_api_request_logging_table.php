<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroesprofile_logs.api_request_logging', function (Blueprint $table) {
            $table->id('api_request_logging_id');
            $table->unsignedBigInteger('api_account_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('method', 10);
            $table->string('page', 500)->nullable();
            $table->longText('parameters')->nullable();
            $table->unsignedSmallInteger('status')->nullable();
            $table->longText('user_agent')->nullable();
            $table->timestamp('date_time')->useCurrent()->nullable();
            $table->index(['api_account_id', 'date_time'], 'account_date_time');
            $table->index('date_time', 'dateTime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_logs.api_request_logging');
    }
};
