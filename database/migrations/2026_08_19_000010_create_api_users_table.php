<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * API customer accounts. Mirrors the existing production table, which predates any
 * migration — much of it is leftover Spark scaffolding (two-factor, teams, billing).
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('users')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();

            // Non-null default in production; new accounts arrive pre-verified.
            $table->timestamp('email_verified_at')->nullable()->default('2024-10-09 22:34:00');

            $table->string('password');
            $table->rememberToken();
            $table->text('photo_url')->nullable();

            $table->tinyInteger('uses_two_factor_auth')->default(0);
            $table->string('authy_id')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('two_factor_reset_code', 100)->nullable();

            $table->integer('current_team_id')->nullable();

            // Cashier 12 column names.
            $table->string('stripe_id')->nullable();
            $table->string('current_billing_plan')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_country')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_address_line_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_zip', 25)->nullable();
            $table->string('billing_country', 2)->nullable();
            $table->string('vat_id', 50)->nullable();
            $table->text('extra_billing_information')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('last_read_announcements_at')->nullable();

            $table->string('terms_version_accepted')->nullable();
            $table->dateTime('terms_accepted_at')->nullable();
            $table->timestamps();

            // Comped access, granted by hand alongside Stripe subscriptions.
            $table->tinyInteger('d_approved')->default(0);
            $table->tinyInteger('p_approved')->default(0);
            $table->tinyInteger('n_approved')->default(0);
            $table->tinyInteger('n_upload_approved')->default(0);
            $table->tinyInteger('h_approved')->default(0);
            $table->tinyInteger('c_upload_approved')->default(0);
            $table->tinyInteger('hi_upload_approved')->default(0);
            $table->tinyInteger('nut_upload_approved')->default(0);
            $table->tinyInteger('m_upload_approved')->default(0);
            $table->tinyInteger('ml_upload_approved')->default(0);

            $table->tinyInteger('twitch_extension')->default(0);
            $table->tinyInteger('battlenet')->default(0);
            $table->string('timezone', 45)->nullable();
            $table->string('promotion_days', 45)->nullable();

            // Gates live data on the public API. Always starts at 0.
            $table->boolean('migrated')->default(false);
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('users');
    }
};
