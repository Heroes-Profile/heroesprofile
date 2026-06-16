<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('battletag_not_allowed_download_replays', function (Blueprint $table) {
            $table->integer('battletag_not_allowed_download_replays_id')->autoIncrement();
            $table->string('battletag', 45)->nullable()->collation('utf8mb4_0900_ai_ci');
        });

        DB::statement('ALTER TABLE `battletag_not_allowed_download_replays` ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battletag_not_allowed_download_replays');
    }
};
