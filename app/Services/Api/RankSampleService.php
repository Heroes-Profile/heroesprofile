<?php

namespace App\Services\Api;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Loading-screen samples from the uploader's opt-in rank reader (its "Ranks" build), kept while that
 * reader is being built: for each Storm League game, the left and right strips of the loading screen that
 * hold the ten player cards, plus a small JSON describing the game. They land in a private storage folder
 * of their own (the `gcs-rank-samples` disk) that only the maintainers read; nothing here is public.
 *
 * `config('api.rank_samples.collect')` switches collection off: the uploader reads the answer's `collect`
 * and stops sending for the rest of its run, so no app release is needed to end it.
 */
class RankSampleService
{
    public const DISK = 'gcs-rank-samples';

    /** What a frame's file name must look like; anything else is renamed by its position. */
    private const FRAME_NAME = '/^frame-\d{1,2}-(left|right)\.png$/';

    /** Whether samples are wanted at all: switched on, and somewhere to put them. */
    public function collecting(): bool
    {
        return (bool) config('api.rank_samples.collect')
            && filled(config('filesystems.disks.'.self::DISK.'.bucket'));
    }

    /**
     * Stores frames and the game's description under `<date>/<sample id>/`. A game's frames can arrive in
     * several requests (the uploader sends one frame per request, to stay under PHP's post_max_size), all
     * carrying the same `$sampleId`, so they land in one folder; without one, a new id is made. The meta is
     * the same in each request and simply rewritten. The reporter is kept only as a salted hash of the IP,
     * to spot one source flooding the folder.
     *
     * @param  array<string, mixed>  $meta
     * @param  array<int, UploadedFile>  $frames
     * @return string The folder it went into.
     */
    public function store(array $meta, array $frames, string $ip, ?string $sampleId = null): string
    {
        $disk = Storage::disk(self::DISK);
        $id = $sampleId !== null && Str::isUuid($sampleId) ? strtolower($sampleId) : Str::uuid()->toString();
        $folder = now()->utc()->format('Y-m-d').'/'.$id;

        foreach (array_values($frames) as $i => $frame) {
            $name = $frame->getClientOriginalName();
            $disk->putFileAs($folder, $frame, preg_match(self::FRAME_NAME, $name) ? $name : "frame-{$i}.png");
        }

        $disk->put("{$folder}/meta.json", json_encode([
            'received_at' => now()->utc()->toIso8601String(),
            'reporter' => hash_hmac('sha256', $ip, (string) config('app.key')),
            'meta' => $meta,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $folder;
    }
}
