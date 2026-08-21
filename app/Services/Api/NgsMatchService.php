<?php

namespace App\Services\Api;

use App\Models\NGS\NGSTeam;
use App\Models\NGS\Player;
use App\Models\NGS\Replay;
use App\Models\NGS\ReplayBan;
use App\Services\GlobalDataService;
use Illuminate\Support\Collection;

/**
 * A single NGS match — every game played between two teams in one round.
 *
 * Rebuilt from the old API's /NGS/Match rather than moved: the original ran a
 * query per replay inside a loop and resolved team names one at a time. Heroes
 * and maps come from the cached global lists instead of being re-read per call.
 *
 * Output is deliberately from the requesting team's perspective — "team" and
 * "enemy" rather than team 0 and 1 — because that is the contract consumers
 * already parse.
 */
class NgsMatchService
{
    /** The old API subtracts draft time from the recorded length. */
    private const DRAFT_SECONDS = 70;

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    public function forTeam(int $season, string $division, string $team, int $round): array
    {
        $heroes = $this->globalDataService->getHeroesByID();
        $maps = $this->globalDataService->getMaps()->keyBy('map_id');

        $replays = Replay::where('season', $season)
            ->where(fn ($query) => $query->where('division_0', $division)->orWhere('division_1', $division))
            ->where(fn ($query) => $query->where('team_0_name', $team)->orWhere('team_1_name', $team))
            ->where('round', $round)
            ->get();

        $teamNames = $this->teamNames($replays);

        $games = [];
        $enemy = null;
        $mapBans = ['team' => [], 'enemy' => []];

        foreach ($replays as $replay) {
            $players = Player::where('replayID', $replay->replayID)->get();

            if ($players->isEmpty()) {
                continue;
            }

            $ours = $players->first(fn ($player) => $this->matches($teamNames[$player->team_name] ?? null, $team));

            // A replay whose players carry no matching team name cannot be
            // described from that team's perspective, so it is skipped rather
            // than reported from the wrong side.
            if ($ours === null) {
                continue;
            }

            $enemySide = (int) $ours->team === 1 ? 0 : 1;
            $enemy ??= $this->enemyName($players, $teamNames, $team);
            $mapBans = $this->mapBans($replay, (int) $ours->team, $maps);

            $games[$replay->game] = [
                'map' => $maps[$replay->game_map]->name ?? null,
                'length' => $replay->game_length - self::DRAFT_SECONDS,
                'winner' => (bool) $ours->winner,
                'team_heroes' => $this->heroNames($players, (int) $ours->team, $heroes),
                'team_bans' => $this->bans($replay->replayID, (int) $ours->team, $heroes),
                'enemy_heroes' => $this->heroNames($players, $enemySide, $heroes),
                'enemy_bans' => $this->bans($replay->replayID, $enemySide, $heroes),
                // Built from the site URL, not the request host: on the API
                // domain url() would point the link at a path that only exists
                // on the website.
                'replay_url' => rtrim(config('app.url'), '/').'/Esports/NGS/Match/Single/'.$replay->replayID,
            ];
        }

        return [
            'season' => $season,
            'division' => $division,
            'team' => $team,
            'team_map_bans' => $mapBans['team'],
            'enemy' => $enemy,
            'enemy_map_bans' => $mapBans['enemy'],
            'round' => $round,
            'total_games' => $replays->count(),
            'match_data' => $games,
        ];
    }

    /** One lookup for every team in the match, rather than one per player row. */
    private function teamNames($replays): array
    {
        $ids = Player::whereIn('replayID', $replays->pluck('replayID'))
            ->distinct()
            ->pluck('team_name');

        return NGSTeam::whereIn('team_id', $ids)->pluck('team_name', 'team_id')->all();
    }

    private function matches(?string $candidate, string $team): bool
    {
        return $candidate !== null && mb_strtolower($candidate) === mb_strtolower($team);
    }

    private function enemyName($players, array $teamNames, string $team): ?string
    {
        foreach ($players as $player) {
            $name = $teamNames[$player->team_name] ?? null;

            if ($name !== null && ! $this->matches($name, $team)) {
                return $name;
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function heroNames($players, int $side, Collection $heroes): array
    {
        return $players->where('team', $side)
            ->map(fn ($player) => $heroes[$player->hero]->name ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    private function bans(int $replayID, int $side, Collection $heroes): array
    {
        return ReplayBan::where('replayID', $replayID)
            ->where('team', $side)
            ->pluck('hero')
            ->map(fn ($hero) => $heroes[$hero]->name ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /** Map bans are recorded per side on the replay, not per player. */
    private function mapBans(Replay $replay, int $side, Collection $maps): array
    {
        $columns = [
            0 => ['team_0_map_ban', 'team_0_map_ban_2'],
            1 => ['team_1_map_ban', 'team_1_map_ban_2'],
        ];

        $names = function (array $pair) use ($replay, $maps) {
            return collect($pair)
                ->map(fn ($column) => $replay->{$column})
                ->filter()
                ->map(fn ($mapId) => $maps[$mapId]->name ?? null)
                ->filter()
                ->values()
                ->all();
        };

        return [
            'team' => $names($columns[$side]),
            'enemy' => $names($columns[$side === 1 ? 0 : 1]),
        ];
    }
}
