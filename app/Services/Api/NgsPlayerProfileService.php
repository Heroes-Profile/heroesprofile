<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;

/**
 * Career aggregates for one NGS player, optionally narrowed to a season or
 * division.
 *
 * A battletag can map to several `player_id` rows — one per team per season — so
 * every match is gathered before aggregating rather than picking one.
 */
class NgsPlayerProfileService
{
    private const CONNECTION = 'heroesprofile_ngs';

    /** Averaged stats, in the order the old API returned them. */
    private const AVERAGES = [
        'highest_kill_streak', 'hero_damage', 'siege_damage', 'structure_damage',
        'minion_damage', 'creep_damage', 'summon_damage', 'time_cc_enemy_heroes',
        'healing', 'self_healing', 'damage_taken', 'experience_contribution',
        'town_kills', 'time_spent_dead', 'merc_camp_captures',
        'watch_tower_captures', 'meta_experience', 'protection_allies',
        'silencing_enemies', 'rooting_enemies', 'stunning_enemies', 'clutch_heals',
        'escapes', 'vengeance', 'outnumbered_deaths', 'teamfight_escapes',
        'teamfight_healing', 'teamfight_damage_taken', 'teamfight_hero_damage',
        'multikill', 'physical_damage', 'spell_damage', 'regen_globes',
    ];

    public function forPlayer(string $battletag, ?int $season = null, ?string $division = null): array
    {
        $playerIds = $this->playerIds($battletag, $season, $division);

        if ($playerIds === []) {
            return [];
        }

        $row = $this->aggregate($playerIds, $season, $division);

        if ($row === null) {
            return [];
        }

        $profile = [
            'wins' => (int) $row->wins,
            'losses' => (int) $row->losses,
            'avg_level' => $this->number($row->avg_level),
            'avg_kills' => $this->number($row->avg_kills),
            'total_kills' => (int) $row->total_kills,
            'avg_assists' => $this->number($row->avg_assists),
            'total_assists' => (int) $row->total_assists,
            'avg_takedowns' => $this->number($row->avg_takedowns),
            'total_takedowns' => (int) $row->total_takedowns,
            'avg_deaths' => $this->number($row->avg_deaths),
            'total_deaths' => (int) $row->total_deaths,
            'kda' => $this->number($row->kda),
        ];

        foreach (self::AVERAGES as $stat) {
            $profile['avg_'.$stat] = $this->number($row->{'avg_'.$stat});
        }

        return $profile;
    }

    /** One battletag can hold several ids — one per team per season. */
    private function playerIds(string $battletag, ?int $season, ?string $division): array
    {
        return DB::connection(self::CONNECTION)
            ->table('battletags')
            ->join('teams', 'teams.team_id', '=', 'battletags.team_id')
            ->where('battletags.battletag', $battletag)
            ->when($season !== null, fn ($query) => $query->where('teams.season', $season))
            ->when($division !== null, fn ($query) => $query->where('teams.division', $division))
            ->pluck('battletags.player_id')
            ->all();
    }

    private function aggregate(array $playerIds, ?int $season, ?string $division): ?object
    {
        $selects = [
            DB::raw('IF(SUM(deaths) = 0, SUM(takedowns), SUM(takedowns) / SUM(deaths)) AS kda'),
            DB::raw('SUM(winner = 1) AS wins'),
            DB::raw('SUM(winner = 0) AS losses'),
            DB::raw('AVG(`level`) AS avg_level'),
            DB::raw('AVG(kills) AS avg_kills'),
            DB::raw('SUM(kills) AS total_kills'),
            DB::raw('AVG(assists) AS avg_assists'),
            DB::raw('SUM(assists) AS total_assists'),
            DB::raw('AVG(takedowns) AS avg_takedowns'),
            DB::raw('SUM(takedowns) AS total_takedowns'),
            DB::raw('AVG(deaths) AS avg_deaths'),
            DB::raw('SUM(deaths) AS total_deaths'),
        ];

        // Column names come from a fixed list, never from the request.
        foreach (self::AVERAGES as $stat) {
            $selects[] = DB::raw('AVG(`'.$stat.'`) AS `avg_'.$stat.'`');
        }

        return DB::connection(self::CONNECTION)
            ->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'player.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->whereIn('player.battletag', $playerIds)
            ->when($season !== null, fn ($query) => $query->where('replay.season', $season))
            ->when($division !== null, fn ($query) => $query->where(
                fn ($q) => $q->where('replay.division_0', $division)->orWhere('replay.division_1', $division)
            ))
            ->first($selects);
    }

    /** Aggregates arrive as strings through PDO. */
    private function number($value): int|float|null
    {
        return $value === null ? null : $value + 0;
    }
}
