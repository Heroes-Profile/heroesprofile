<?php

namespace App\Services\Api;

use App\Models\ReplayFingerprint;
use App\Models\UploadAttempt;
use App\Models\UploadAttemptLog;
use App\Models\UploadedReplayData;
use App\Models\UploaderSourceChange;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Replay ingestion for the desktop and electron uploaders.
 *
 * The response is a frozen wire contract — `fingerprint`, `replayID`, `status`,
 * and nothing else is read. Old uploader builds run indefinitely, so these three
 * fields and the exact `status` strings cannot change.
 *
 * The file is written to the bucket under a scratch name, parsed for its
 * fingerprint alone, and the scratch object deleted. Only a replay nobody has
 * seen before is kept, stored under its fingerprint. The full parse happens later
 * in the BackendServerApplication daemon, not here.
 */
class ReplayUploadService
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    /**
     * The status a caller gets when no fingerprint could be produced. Not a member
     * of the client's `UploadStatus` enum, so it lands as `UploadError` there.
     */
    public const FAILURE_STATUS = 'Failure.  Try again or Contact zemill@heroesprofile.com';

    /**
     * Stands in when there is no fingerprint to report. The client deserializes
     * this field into a Guid, and an empty string throws where a nil one does not.
     */
    private const NIL_FINGERPRINT = '00000000-0000-0000-0000-000000000000';

    private const DISK = 'gcs';

    private const EXTENSION = '.StormReplay';

    /** Uploaders that own a replay's source; anything else defers to them. */
    private const PRIMARY_SOURCES = ['desktop', 'electron'];

    /**
     * Outcomes that settle a file for good. A `Failure` or `Error` row is not one:
     * blocking on those would mean a parser outage permanently cost every replay
     * uploaded during it, from that address, with no way back.
     */
    private const SETTLED = ['Success', 'Duplicate'];

    public function __construct(private readonly ReplayParserClient $parser) {}

    /**
     * @return array<string, mixed> the response body, frozen shape
     */
    public function upload(UploadedFile $file, string $source, string $ip, string $version, string $compiled): array
    {
        $hash = (string) hash_file('sha256', $file->getRealPath());
        $name = $file->getClientOriginalName();
        $size = (int) $file->getSize();

        // Same bytes from the same address as a settled attempt: answer from the
        // row. Nothing reaches the bucket or the parser, which is the point.
        $settled = UploadAttempt::where('ip', $ip)
            ->where('file_hash', $hash)
            ->whereIn('status', self::SETTLED)
            ->first();

        if ($settled !== null) {
            $this->log($ip, $hash, $name, $size, $source, 'Blocked', 'Already submitted');

            return $this->body($settled->fingerprint, $settled->replayID, $settled->status);
        }

        $parsed = $this->fingerprintOf($file);
        $fingerprint = $parsed['fingerprint'] ?? null;

        if (! is_string($fingerprint) || $fingerprint === '') {
            // Same reasoning as the store failure below: the caller is told to try
            // again and nothing else, so the parser's own answer only survives here.
            Log::warning('Replay upload got no fingerprint from the parser', [
                'file_name' => $name,
                'file_size' => $size,
                'source' => $source,
                'ip' => $ip,
                'parser_response' => $parsed,
            ]);

            $this->record($ip, $hash, $name, $size, $source, 'Failure');

            return ['fingerprint' => self::NIL_FINGERPRINT, 'status' => self::FAILURE_STATUS];
        }

        $existing = ReplayFingerprint::where('fingerprint', $fingerprint)->first();

        if ($existing !== null && $existing->replayID !== null) {
            if (in_array($source, self::PRIMARY_SOURCES, true)) {
                $this->recordSourceChange((int) $existing->replayID, $source);
            }

            $this->record($ip, $hash, $name, $size, $source, 'Duplicate', $fingerprint, (int) $existing->replayID);

            return $this->body($fingerprint, $existing->replayID, 'Duplicate');
        }

        $this->store(Storage::disk(self::DISK), $file, $fingerprint.self::EXTENSION);

        $replay = new ReplayFingerprint;
        $replay->fingerprint = $fingerprint;

        try {
            $replay->save();

            UploadedReplayData::create([
                'replayID' => $replay->replayID,
                'uploaded_filename' => $name,
                'uploaded_source' => $source,
                'uploader_version' => $version,
                'uploader_compile_checker' => $compiled,
                'game_date' => $this->gameDate($parsed),
                'ip' => $ip,
            ]);
        } catch (QueryException $e) {
            return $this->afterInsertFailed($e, $fingerprint, $ip, $hash, $name, $size, $source);
        }

        $this->record($ip, $hash, $name, $size, $source, 'Success', $fingerprint, (int) $replay->replayID);

        return $this->body($fingerprint, $replay->replayID, 'Success');
    }

    /**
     * A rejection that never got as far as the bucket. Logged but not recorded as
     * an attempt: there is no outcome to block a retry on.
     */
    public function reject(string $ip, string $source, string $message, ?UploadedFile $file = null): void
    {
        $this->log(
            $ip,
            $file === null ? '' : (string) hash_file('sha256', $file->getRealPath()),
            $file === null ? '' : $file->getClientOriginalName(),
            $file === null ? 0 : (int) $file->getSize(),
            $source,
            'Rejected',
            $message,
        );
    }

    /**
     * Writes to the bucket, refusing to continue if it did not happen.
     *
     * The disk throws on failure, so this only catches a `false` return — which
     * is what the driver does when `throw` is off. Either way the upload stops
     * here rather than at the parser, which can only report the object missing.
     */
    private function store(Filesystem $disk, UploadedFile $file, string $name): void
    {
        if ($disk->putFileAs('', $file, $name) === false) {
            throw new RuntimeException("Could not write [{$name}] to the replay bucket.");
        }
    }

    /**
     * Parses for the fingerprint alone, from a scratch object that is always
     * cleaned up.
     *
     * @return array<string, mixed>
     */
    private function fingerprintOf(UploadedFile $file): array
    {
        $disk = Storage::disk(self::DISK);
        $scratch = Str::uuid().self::EXTENSION;
        $bucket = (string) config('filesystems.disks.'.self::DISK.'.bucket');

        $this->store($disk, $file, $scratch);

        try {
            $parsed = $this->parser->parse($scratch, $bucket, 'fingerprintOnly');

            // The old site retried too, but deleted the object first, so its second
            // attempt parsed something that was no longer there and never once
            // succeeded.
            if (! isset($parsed['fingerprint'])) {
                $parsed = $this->parser->parse($scratch, $bucket, 'fingerprintOnly');
            }
        } finally {
            $disk->delete($scratch);
        }

        return $parsed;
    }

    /**
     * Two uploads of one replay can race between the fingerprint lookup and the
     * insert. The loser reads back the winner's row rather than reporting an error
     * for a replay that is now stored.
     *
     * @return array<string, mixed>
     */
    private function afterInsertFailed(QueryException $e, string $fingerprint, string $ip, string $hash, string $name, int $size, string $source): array
    {
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $replayID = ReplayFingerprint::where('fingerprint', $fingerprint)->value('replayID');

            $this->record($ip, $hash, $name, $size, $source, 'Duplicate', $fingerprint, $replayID === null ? null : (int) $replayID);

            return $this->body($fingerprint, $replayID, 'Duplicate');
        }

        // The frozen response body has no room to say what went wrong, so this
        // report is the only record. The QueryException it wraps carries the SQL
        // and bindings; the wrapper carries which upload it was.
        report(new RuntimeException(
            "Replay upload could not be stored: fingerprint [{$fingerprint}], source [{$source}], ip [{$ip}]",
            0,
            $e
        ));

        $this->record($ip, $hash, $name, $size, $source, 'Error', $fingerprint);

        return $this->body($fingerprint, null, 'Error');
    }

    /**
     * A replay first seen from somewhere else and later uploaded by a client that
     * owns its source.
     */
    private function recordSourceChange(int $replayID, string $source): void
    {
        $extra = UploadedReplayData::where('replayID', $replayID)->first();

        if ($extra === null || in_array($extra->uploaded_source, self::PRIMARY_SOURCES, true)) {
            return;
        }

        $extra->uploaded_source = $source;
        $extra->save();

        UploaderSourceChange::create(['replayID' => $replayID, 'source' => $source]);
    }

    /** @param  array<string, mixed>  $parsed */
    private function gameDate(array $parsed): ?string
    {
        $raw = $parsed['game_date'] ?? null;

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $timestamp = strtotime($raw);

        // The old site wrote 1970-01-01 whenever the parser gave it nothing usable.
        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }

    /** @return array<string, mixed> */
    private function body(?string $fingerprint, int|string|null $replayID, string $status): array
    {
        return [
            'fingerprint' => $fingerprint === null || $fingerprint === '' ? self::NIL_FINGERPRINT : $fingerprint,
            'replayID' => (int) $replayID,
            'status' => $status,
        ];
    }

    private function record(string $ip, string $hash, string $name, int $size, string $source, string $status, ?string $fingerprint = null, ?int $replayID = null): void
    {
        $this->log($ip, $hash, $name, $size, $source, $status);

        UploadAttempt::create([
            'ip' => $ip,
            'file_hash' => $hash,
            'file_name' => $name,
            'file_size' => $size,
            'status' => $status,
            'fingerprint' => $fingerprint,
            'replayID' => $replayID,
        ]);
    }

    private function log(string $ip, string $hash, string $name, int $size, string $source, string $status, ?string $message = null): void
    {
        UploadAttemptLog::create([
            'ip' => $ip,
            'file_hash' => $hash,
            'file_name' => $name,
            'file_size' => $size,
            'source' => $source,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
