<?php

namespace App\Console\Commands;

use App\Services\Twitch\TwitchExtensionManifest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Warns when hero or talent images here are new or changed since the last Twitch
 * extension release, so it is not forgotten after a patch. Warnings only: the
 * extension shows placeholders until then, and Twitch review is not ours to wait on.
 */
class CheckTwitchExtension extends Command
{
    protected $signature = 'twitch:check-extension';

    protected $description = 'Warn about hero and talent images the released Twitch extension does not have';

    public function handle(TwitchExtensionManifest $manifest): int
    {
        if (! $manifest->isKnown()) {
            $this->warnLine('No Twitch extension manifest at '.config('twitch.extension_manifest_path').'. Run `npm run release` in the extension and commit it.');

            return self::SUCCESS;
        }

        $released = $manifest->images();
        $added = [];
        $changed = [];

        foreach (['heroes', 'talents'] as $folder) {
            $dir = public_path('images/'.$folder);

            if (! File::isDirectory($dir)) {
                continue;
            }

            foreach (File::allFiles($dir) as $file) {
                if ($file->getExtension() === 'php') {
                    continue;
                }

                $key = $folder.'/'.str_replace('\\', '/', $file->getRelativePathname());
                $hash = sha1_file($file->getPathname());

                if (! isset($released[$key])) {
                    $added[] = $key;
                } elseif ($released[$key] !== $hash) {
                    $changed[] = $key;
                }
            }
        }

        foreach ($added as $key) {
            $this->warnLine('Not in Twitch extension '.$manifest->version().': public/images/'.$key);
        }

        foreach ($changed as $key) {
            $this->warnLine('Changed since Twitch extension '.$manifest->version().': public/images/'.$key);
        }

        if ($added === [] && $changed === []) {
            $this->info('Twitch extension '.$manifest->version().' has every hero and talent image.');
        } else {
            $this->warnLine(count($added).' new and '.count($changed).' changed images. Run `npm run release` in the extension, commit the manifest, and upload the zip to Twitch.');
        }

        return self::SUCCESS;
    }

    /** A GitHub Actions annotation in CI, a plain warning anywhere else. */
    private function warnLine(string $message): void
    {
        getenv('GITHUB_ACTIONS') ? $this->line('::warning::'.$message) : $this->warn($message);
    }
}
