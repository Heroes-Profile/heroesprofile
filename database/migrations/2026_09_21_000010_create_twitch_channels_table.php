<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per Twitch channel using the extension.
 *
 * Rows are never deleted. Unlinking clears `user_id`, and the trial columns stay,
 * which is what makes the free month once per channel rather than once per link.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        if (Schema::connection(self::CONNECTION)->hasTable('twitch_channels')) {
            return;
        }

        Schema::connection(self::CONNECTION)->create('twitch_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();

            $table->string('twitch_user_id', 32);
            $table->string('twitch_login', 64);
            $table->string('twitch_display_name', 64)->nullable();

            // The streamer's own player, so their team is always shown first.
            $table->unsignedBigInteger('blizz_id')->nullable();
            $table->unsignedTinyInteger('region')->nullable();
            $table->string('battletag', 45)->nullable();

            $table->string('uploader_key_hash', 64)->nullable();
            $table->string('uploader_key_last4', 4)->nullable();
            $table->timestamp('uploader_last_seen_at')->nullable();

            $table->unsignedSmallInteger('delay_seconds')->default(0);
            $table->boolean('show_stats')->default(true);

            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('comped_until')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();

            $table->boolean('listing_opt_in')->default(false);
            $table->unsignedSmallInteger('listing_terms_version')->nullable();
            $table->timestamp('listing_terms_accepted_at')->nullable();
            $table->timestamp('listing_hidden_at')->nullable();
            $table->string('listing_hidden_reason')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('twitch_user_id', 'twitch_user_id');
            $table->unique('uploader_key_hash', 'uploader_key_hash');
            $table->unique('user_id', 'user_id');
        });
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->dropIfExists('twitch_channels');
    }
};
