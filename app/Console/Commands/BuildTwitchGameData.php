<?php

namespace App\Console\Commands;

use App\Services\Twitch\TwitchGameData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Writes the hero and talent data the Twitch extension renders lobbies from.
 *
 * Run after each patch and commit the file. The extension's build copies it into
 * the bundle; the deployed copy on heroesprofile.com is only fetched by viewers
 * whose bundle predates a new hero or talent.
 */
class BuildTwitchGameData extends Command
{
    protected $signature = 'twitch:game-data {--out= : Where to write. Defaults to public/static/twitch/game-data.json.}';

    protected $description = 'Generate the static hero/talent data file used by the Twitch extension';

    public function handle(TwitchGameData $gameData): int
    {
        $path = $this->option('out') ?: public_path(config('twitch.game_data_path'));

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($gameData->export(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $this->info('Wrote '.$path.' ('.number_format(filesize($path) / 1024, 1).' KB)');

        return self::SUCCESS;
    }
}
