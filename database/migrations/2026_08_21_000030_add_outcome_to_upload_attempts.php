<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records what an upload attempt resolved to, so a blocked resend can be answered
 * from the row instead of going back to the parser.
 *
 * Additive: the old API site names its columns explicitly when it inserts, so it
 * keeps writing rows with both of these null. Those rows, and any written before
 * this runs, answer with a nil fingerprint and replayID 0.
 */
return new class extends Migration
{
    private const CONNECTION = 'heroesprofile_api';

    public function up(): void
    {
        $schema = Schema::connection(self::CONNECTION);

        if (! $schema->hasColumn('upload_attempts', 'fingerprint')) {
            $schema->table('upload_attempts', function (Blueprint $table) {
                // Declared as `replay_fingerprints.fingerprint` is. Narrowing it to
                // a GUID's 36 would reject a longer value here — and this insert
                // runs after the replay is already stored, so the upload would fail
                // having done the work.
                $table->string('fingerprint', 45)->nullable()->after('status');
                $table->integer('replayID')->nullable()->after('fingerprint');
            });
        }
    }

    public function down(): void
    {
        Schema::connection(self::CONNECTION)->table('upload_attempts', function (Blueprint $table) {
            $table->dropColumn(['fingerprint', 'replayID']);
        });
    }
};
