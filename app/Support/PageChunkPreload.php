<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Preload links for the page's own Vue components. Since the bundle split, each page component is its own chunk,
 * which the browser otherwise only asks for once app.js has run, so the content area sits empty for a moment.
 * Reading the rendered content for the component tags lets the head fetch those chunks alongside app.js.
 */
class PageChunkPreload
{
    private const MAX_COMPONENTS = 6;

    private static ?array $manifest = null;

    public static function links(string $content): string
    {
        // The dev server serves modules itself; nothing to preload
        if (is_file(public_path('hot'))) {
            return '';
        }

        $manifest = self::manifest();
        if (! $manifest) {
            return '';
        }

        // Component file name => manifest key, for the lazily loaded components
        $byName = [];
        foreach ($manifest as $key => $chunk) {
            if (str_starts_with($key, 'resources/js/components/') && str_ends_with($key, '.vue')) {
                $byName[basename($key, '.vue')] = $key;
            }
        }

        // What app.js already brings with it
        $loaded = [];
        self::collect($manifest, 'resources/js/app.js', $loaded);

        preg_match_all('/<([a-z][a-z0-9]*(?:-[a-z0-9]+)+)[\s>]/', $content, $matches);

        $files = [];
        $styles = [];
        $count = 0;
        foreach (array_unique($matches[1]) as $tag) {
            $key = $byName[Str::studly($tag)] ?? null;
            if (! $key || isset($loaded[$key])) {
                continue;
            }

            $keys = [];
            self::collect($manifest, $key, $keys);
            foreach (array_keys($keys) as $chunkKey) {
                if (isset($loaded[$chunkKey])) {
                    continue;
                }
                $files[$manifest[$chunkKey]['file']] = true;
                foreach ($manifest[$chunkKey]['css'] ?? [] as $css) {
                    $styles[$css] = true;
                }
            }

            if (++$count >= self::MAX_COMPONENTS) {
                break;
            }
        }

        $html = '';
        foreach (array_keys($styles) as $css) {
            $html .= '<link rel="stylesheet" href="'.e(asset('build/'.$css)).'">'."\n";
        }
        foreach (array_keys($files) as $file) {
            $html .= '<link rel="modulepreload" href="'.e(asset('build/'.$file)).'">'."\n";
        }

        return $html;
    }

    /** The chunk and every chunk it imports, by manifest key. */
    private static function collect(array $manifest, string $key, array &$keys): void
    {
        if (isset($keys[$key]) || ! isset($manifest[$key])) {
            return;
        }

        $keys[$key] = true;
        foreach ($manifest[$key]['imports'] ?? [] as $import) {
            self::collect($manifest, $import, $keys);
        }
    }

    private static function manifest(): array
    {
        if (self::$manifest === null) {
            $path = public_path('build/manifest.json');
            self::$manifest = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
        }

        return self::$manifest;
    }
}
