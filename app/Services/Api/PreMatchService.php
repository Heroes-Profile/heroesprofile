<?php

namespace App\Services\Api;

use App\Models\Battletag;
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
        $rows = [];

        foreach ($players as $player) {
            $row = $this->row($player);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        if ($rows === []) {
            return null;
        }

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
        if ($name === null || $tag === null || ($player['BattleNetId'] ?? null) === null
            || $region === null || $team === null) {
            return null;
        }

        $battletag = $name.'#'.$tag;

        return [
            'battletag' => $battletag,
            'blizz_id' => Battletag::where('battletag', $battletag)
                ->where('region', (int) $region)
                ->value('blizz_id'),
            'region' => (int) $region,
            'team' => (int) $team,
        ];
    }
}
