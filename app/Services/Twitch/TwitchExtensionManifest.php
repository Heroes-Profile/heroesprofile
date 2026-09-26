<?php

namespace App\Services\Twitch;

/**
 * What the last Twitch extension release bundled: hero and talent ids, and a hash
 * of every hero and talent image. Written into this repo by the extension's
 * `npm run release`, so the site can tell when it has moved ahead of the extension.
 *
 * Without the file every check here is skipped rather than reporting everything.
 */
class TwitchExtensionManifest
{
    /** @var array<string, mixed>|null */
    private static ?array $manifest = null;

    public function isKnown(): bool
    {
        return $this->version() !== null;
    }

    public function version(): ?string
    {
        $version = $this->manifest()['version'] ?? null;

        return is_string($version) && $version !== '' ? $version : null;
    }

    public function hasHero(int $heroId): bool
    {
        return isset($this->ids('heroes')[$heroId]);
    }

    public function hasTalent(int $talentId): bool
    {
        return isset($this->ids('talents')[$talentId]);
    }

    /** @return array<string, string> path under public/images => sha1 */
    public function images(): array
    {
        $images = $this->manifest()['images'] ?? [];

        return is_array($images) ? $images : [];
    }

    public static function path(): string
    {
        return base_path(config('twitch.extension_manifest_path'));
    }

    /** @return array<int, true> */
    private function ids(string $key): array
    {
        $ids = $this->manifest()[$key] ?? [];

        return is_array($ids) ? array_fill_keys(array_map('intval', $ids), true) : [];
    }

    /** @return array<string, mixed> */
    private function manifest(): array
    {
        if (self::$manifest === null) {
            $path = self::path();
            $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
            self::$manifest = is_array($decoded) ? $decoded : [];
        }

        return self::$manifest;
    }
}
