<?php

namespace App\Services\Api;

use App\Services\GlobalDataService;
use Illuminate\Support\Facades\DB;

/**
 * How a hero performed in one NGS division and season, optionally narrowed to a
 * single player.
 *
 * Note `player.battletag` stores a player id rather than a battletag string, so
 * a caller's battletag is resolved through `battletags` first.
 */
class NgsHeroStatService
{
    private const CONNECTION = 'heroesprofile_ngs';

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    public function forHero(int $season, string $division, string $hero, ?string $battletag = null): array
    {
        $heroId = $this->globalDataService->getHeroes()->firstWhere('name', $hero)?->id;

        if ($heroId === null) {
            return [];
        }

        $playerId = $battletag === null ? null : $this->playerId($battletag, $season, $division);

        $games = $this->games($season, $division, $heroId, $playerId);
        $totals = $this->totals($games);

        $bans = $this->bans($season, $division, $heroId);

        $played = $totals['games_played'];

        return [
            'division_total_games' => $this->divisionGames($season, $division),
            'games_played' => $played,
            'kills' => $totals['kills'],
            'takedowns' => $totals['takedowns'],
            'assists' => $totals['assists'],
            'deaths' => $totals['deaths'],
            'wins' => $totals['wins'],
            'losses' => $totals['losses'],
            // Mean of each game's KDA, which is not the same as the overall KDA
            // below. Both are reported, as the old API did.
            'average_kda' => $played > 0 ? $totals['kda_sum'] / $played : 0,
            'bans' => $bans,
            'participation' => $played + $bans,
            'kda' => $totals['deaths'] > 0
                ? $totals['takedowns'] / $totals['deaths']
                : $totals['takedowns'],
            'average_kills' => $played > 0 ? $totals['kills'] / $played : 0,
            'average_takedowns' => $played > 0 ? $totals['takedowns'] / $played : 0,
            'average_assists' => $played > 0 ? $totals['assists'] / $played : 0,
            'average_deaths' => $played > 0 ? $totals['deaths'] / $played : 0,
        ];
    }

    private function playerId(string $battletag, int $season, string $division): ?int
    {
        return DB::connection(self::CONNECTION)
            ->table('battletags')
            ->join('teams', 'teams.team_id', '=', 'battletags.team_id')
            ->where('teams.season', $season)
            ->where('teams.division', $division)
            ->where('battletags.battletag', $battletag)
            ->value('battletags.player_id');
    }

    private function inDivision($query, int $season, string $division)
    {
        return $query->where('replay.season', $season)
            ->where(fn ($q) => $q->where('replay.division_0', $division)
                ->orWhere('replay.division_1', $division));
    }

    private function divisionGames(int $season, string $division): int
    {
        return $this->inDivision(
            DB::connection(self::CONNECTION)->table('replay'),
            $season,
            $division
        )->count();
    }

    private function games(int $season, string $division, int $heroId, ?int $playerId)
    {
        $query = DB::connection(self::CONNECTION)
            ->table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->where('player.hero', $heroId)
            ->when($playerId !== null, fn ($q) => $q->where('player.battletag', $playerId));

        return $this->inDivision($query, $season, $division)
            ->get(['player.winner', 'scores.kills', 'scores.takedowns', 'scores.assists', 'scores.deaths']);
    }

    /** @return array<string, int|float> */
    private function totals($games): array
    {
        $totals = [
            'games_played' => 0, 'kills' => 0, 'takedowns' => 0, 'assists' => 0,
            'deaths' => 0, 'wins' => 0, 'losses' => 0, 'kda_sum' => 0,
        ];

        foreach ($games as $game) {
            $totals['games_played']++;
            $totals['kills'] += $game->kills;
            $totals['takedowns'] += $game->takedowns;
            $totals['assists'] += $game->assists;
            $totals['deaths'] += $game->deaths;
            $totals['kda_sum'] += $game->deaths != 0
                ? $game->takedowns / $game->deaths
                : $game->takedowns;

            $game->winner == 1 ? $totals['wins']++ : $totals['losses']++;
        }

        return $totals;
    }

    private function bans(int $season, string $division, int $heroId): int
    {
        $replays = $this->inDivision(
            DB::connection(self::CONNECTION)->table('replay'),
            $season,
            $division
        )->select('replay.replayID');

        return DB::connection(self::CONNECTION)
            ->table('replay_bans')
            ->whereIn('replayID', $replays)
            ->where('hero', $heroId)
            ->count();
    }
}
