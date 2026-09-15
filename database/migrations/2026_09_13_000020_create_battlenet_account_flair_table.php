<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Event flair earned by Battle.net accounts, e.g. finding the Xal'atath eye.
 *
 * One row per account per flair. Some flair also grants a stretch of ad-free.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('battlenet_account_flair')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('battlenet_account_flair', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('battlenet_accounts_id');
            $table->string('flair', 50);
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamp('ad_free_until')->nullable();

            $table->unique(['battlenet_accounts_id', 'flair'], 'uniq_account_flair');
            $table->index('flair', 'idx_flair');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('battlenet_account_flair');
    }
};
