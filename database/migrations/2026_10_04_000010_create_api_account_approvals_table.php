<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approvals given from the admin console, and every approval flag granted or
 * removed there, each with the project description the account had at that moment.
 *
 * The terms license the project as described, and the account's own description
 * is edited in place — this is the only record of what was actually approved.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_account_approvals')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_account_approvals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('api_account_id');

            // approval: the Approve button. flag: a Comped Access checkbox changed.
            $table->string('type', 10)->default('flag');

            // Flag rows only. One of ApiAccount::APPROVAL_COLUMNS.
            $table->string('flag', 30)->nullable();

            // Flag rows only. 1 granted, 0 removed.
            $table->boolean('granted')->nullable();

            // Copied from the account at the time, not joined.
            $table->string('project_name', 100)->nullable();
            $table->text('project_description')->nullable();
            $table->timestamp('project_updated_at')->nullable();

            // Admin only. The one column edited after the row is written.
            $table->text('notes')->nullable();

            $table->unsignedInteger('performed_by')->nullable();

            $table->timestamps();

            $table->index(['api_account_id', 'created_at'], 'idx_account_approvals');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_account_approvals');
    }
};
