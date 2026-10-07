<?php

namespace App\Services\Api;

use App\Models\Battletag;
use App\Models\GameType;
use App\Models\Prematch;
use Illuminate\Support\Facades\DB;

/**
 * Records the players in a game that is starting.
 *
 * The uploader posts this the moment it parses a battle lobby, before any replay
 * exists, so the pre-match page has something to show while the game is being
 * played.
 */
class PreMatchService
{
    private const CONNECTION = 'heroesprofile';

    /**
     * @param  array<int, mixed>  $players  as the client serialises them
     * @return int|null the pre-match id, or null if no usable player row was found
     */
    public function store(array $players): ?int
    {
        $rows = array_values(array_filter(array_map(fn ($player) => $this->row($player), $players)));

        if ($rows === []) {
            return null;
        }

        // One lookup for the lobby rather than one per player.
        $blizzIds = Battletag::whereIn('battletag', array_column($rows, 'battletag'))
            ->whereIn('region', array_unique(array_column($rows, 'region')))
            ->get(['battletag', 'region', 'blizz_id'])
            ->reverse()
            ->mapWithKeys(fn ($row) => [$row->battletag.'|'.$row->region => $row->blizz_id]);

        foreach ($rows as &$row) {
            $row['blizz_id'] = $blizzIds[$row['battletag'].'|'.$row['region']] ?? null;
        }
        unset($row);

        return DB::connection(self::CONNECTION)->transaction(function () use ($rows) {
            // Locked because the id is derived from the current maximum. Two
            // lobbies starting at the same moment would otherwise read the same
            // maximum and their players would land on one pre-match page.
            $max = DB::connection(self::CONNECTION)
                ->table('prematch')
                ->lockForUpdate()
                ->max('prematch_replayID');

            $prematchReplayID = (int) $max + 1;

            foreach ($rows as $row) {
                Prematch::create(array_merge($row, ['prematch_replayID' => $prematchReplayID]));
            }

            return $prematchReplayID;
        });
    }

    /**
     * Records which mode a pre-match game turned out to be. The lobby the page was
     * made from has no mode; the uploader reads it from the game's first storm save
     * and sends it here, and the page switches its filter to match.
     *
     * Write-once: the first answer for a game stands, so a stray call cannot flip
     * the filter on a page someone is already looking at.
     *
     * @param  string  $mode  a `game_types.no_space_name`, as the client's GameMode enum spells it
     * @return string `set`, `unknown_mode`, `not_found` or `already_set`
     */
    public function setGameMode(int $prematchReplayID, string $mode): string
    {
        // Matchmade modes only: Brawl is -1, which the unsigned column cannot hold,
        // and Custom has no filter to switch to.
        $gameType = GameType::where('no_space_name', $mode)->where('type_id', '>=', 1)->value('type_id');

        if ($gameType === null) {
            return 'unknown_mode';
        }

        $updated = Prematch::where('prematch_replayID', $prematchReplayID)
            ->whereNull('game_type')
            ->update(['game_type' => $gameType]);

        if ($updated > 0) {
            return 'set';
        }

        return Prematch::where('prematch_replayID', $prematchReplayID)->exists() ? 'already_set' : 'not_found';
    }

    /**
     * One player, or null if the payload is missing anything that identifies them.
     *
     * @return array<string, mixed>|null
     */
    private function row(mixed $player): ?array
    {
        if (! is_array($player)) {
            return null;
        }

        $name = $player['Name'] ?? null;
        $tag = $player['BattleTag'] ?? null;
        $region = $player['BattleNetRegionId'] ?? null;
        $team = $player['Team'] ?? null;

        // `BattleNetId` has to be present but is never used: the blizz_id comes
        // from our own battletags rather than from whatever the client claims.
        if (! is_scalar($name) || ! is_scalar($tag) || ($player['BattleNetId'] ?? null) === null
            || ! ctype_digit((string) $region) || ! ctype_digit((string) $team)) {
            return null;
        }

        $battletag = $name.'#'.$tag;

        // The width of `prematch.battletag`.
        if (mb_strlen($battletag) > 45) {
            return null;
        }

        return [
            'battletag' => $battletag,
            'region' => (int) $region,
            'team' => (int) $team,
        ];
    }
}
