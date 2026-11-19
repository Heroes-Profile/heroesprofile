<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroesprofile_logs.help_chat_misses', function (Blueprint $table) {
            $table->collation = 'utf8mb4_0900_ai_ci';

            $table->bigIncrements('help_chat_miss_id');
            $table->unsignedBigInteger('battlenet_accounts_id')->nullable();
            $table->string('question', 500);
            // no_faq_match, or asked_anyway after an FAQ answer
            $table->string('reason', 20);
            $table->string('faq_question')->nullable();
            $table->text('answer')->nullable();
            $table->timestamp('date_time')->nullable()->useCurrent();

            $table->index('date_time', 'dateTime');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroesprofile_logs.help_chat_misses');
    }
};
