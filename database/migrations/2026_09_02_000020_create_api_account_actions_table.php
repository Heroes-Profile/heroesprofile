<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every enforcement action taken against an API account, and why.
 *
 * Rows are written once and never removed. The point of the table is to be able to
 * show, months later, that notice was given before access was withdrawn — a
 * suspension with no warning behind it is our word against the customer's.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('api_account_actions')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('api_account_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('api_account_id');

            // warn, suspend, terminate, reinstate.
            $table->string('action', 20);

            // What the customer is told. Required for every action but reinstate.
            $table->text('reason')->nullable();

            // Never shown to the customer: the evidence behind the action — URLs,
            // call volumes, what was said where.
            $table->text('notes')->nullable();

            // Warnings only. Stated in the email and the banner, and used by the
            // console to list what has gone unanswered. Nothing escalates on its own.
            $table->date('respond_by')->nullable();

            // Warnings only, set when they dismiss the banner. This timestamp is the
            // whole evidentiary value of the warning rung.
            $table->timestamp('acknowledged_at')->nullable();

            // The admin account that acted. Null means it was not a person.
            $table->unsignedInteger('performed_by')->nullable();

            $table->timestamps();

            $table->index(['api_account_id', 'created_at'], 'idx_account_history');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('api_account_actions');
    }
};
