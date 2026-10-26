<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The public sample page's data: one account's analytics frozen as JSON files in
 * resources/analytics-sample, written by `php artisan analytics:capture-sample`. Nothing
 * here queries anything.
 */
class AnalyticsSampleService
{
    public const DIRECTORY = 'analytics-sample';

    /** @return array{captured_at: string, battletag: string, status: array, maps: array<string>, replay_id: int|null}|null */
    public function meta(): ?array
    {
        return $this->read('meta');
    }

    // $map is null for all maps, otherwise one of config('analytics.sample.maps')
    public function tab(string $tab, ?string $map): ?array
    {
        return $this->read($this->tabFile($tab, $map));
    }

    public function ability(int $heroId, string $abilityId, ?string $map): ?array
    {
        return $this->read($this->abilityFile($heroId, $abilityId, $map));
    }

    public function replay(): ?array
    {
        return $this->read('replay');
    }

    public function tabFile(string $tab, ?string $map): string
    {
        return $tab.($map === null ? '' : '--'.Str::slug($map));
    }

    public function abilityFile(int $heroId, string $abilityId, ?string $map): string
    {
        return 'ability--'.$heroId.'--'.$abilityId.($map === null ? '' : '--'.Str::slug($map));
    }

    public function write(string $file, array $data): void
    {
        File::ensureDirectoryExists(resource_path(self::DIRECTORY));
        File::put($this->path($file), json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    // Every file but the ones just written, so a recapture leaves nothing stale behind
    public function removeExcept(array $files): int
    {
        $keep = array_map(fn ($file) => $file.'.json', $files);
        $removed = 0;
        foreach (File::glob(resource_path(self::DIRECTORY).'/*.json') as $path) {
            if (! in_array(basename($path), $keep, true)) {
                File::delete($path);
                $removed++;
            }
        }

        return $removed;
    }

    private function read(string $file): ?array
    {
        $path = $this->path($file);
        if (! File::exists($path)) {
            return null;
        }
        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : null;
    }

    private function path(string $file): string
    {
        return resource_path(self::DIRECTORY.'/'.$file.'.json');
    }
}
