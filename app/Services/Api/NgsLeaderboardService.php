<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\DB;

/**
 * NGS stat leaderboards — the highest average or total of one stat, per player.
 *
 * The old API built these by concatenating the requested stat straight into the
 * SQL, so any string in the query parameter reached the database. Here the stat
 * has to be one of a fixed list of columns on `heroesprofile_ngs.scores` before
 * it is used at all.
 */
class NgsLeaderboardService
{
    private const CONNECTION = 'heroesprofile_ngs';

    /** How many players the old API returned. */
    private const LIMIT = 10;

    /**
     * Aggregatable columns on `scores`. `replayID` and `battletag` identify rather
     * than measure, and `spray` is a cosmetic id, so none of them are averageable.
     */
    public const STATS = [
        'level', 'kills', 'assists', 'takedowns', 'deaths', 'highest_kill_streak',
        'hero_damage', 'siege_damage', 'structure_damage', 'minion_damage',
        'creep_damage', 'summon_damage', 'time_cc_enemy_heroes', 'healing',
        'self_healing', 'damage_taken', 'experience_contribution', 'town_kills',
        'time_spent_dead', 'merc_camp_captures', 'watch_tower_captures',
        'meta_experience', 'protection_allies', 'silencing_enemies',
        'rooting_enemies', 'stunning_enemies', 'clutch_heals', 'escapes',
        'vengeance', 'outnumbered_deaths', 'teamfight_escapes', 'teamfight_healing',
        'teamfight_damage_taken', 'teamfight_hero_damage', 'multikill',
        'physical_damage', 'spell_damage', 'regen_globes', 'first_to_ten',
    ];

    public function highestAverage(string $stat, ?int $season = null): array
    {
        return $this->leaderboard('AVG', 'avg_', $stat, $season);
    }

    public function highestTotal(string $stat, ?int $season = null): array
    {
        return $this->leaderboard('SUM', 'total_', $stat, $season);
    }

    /**
     * The column name is safe to interpolate only because it has been checked
     * against self::STATS by the validation rule before reaching here.
     */
    private function leaderboard(string $function, string $prefix, string $stat, ?int $season): array
    {
        if (! in_array($stat, self::STATS, true)) {
            return [];
        }

        $alias = $prefix.$stat;

        return DB::connection(self::CONNECTION)
            ->table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('battletags', function ($join) {
                $join->on('battletags.region', '=', 'replay.region')
                    ->on('battletags.blizz_id', '=', 'player.blizz_id')
                    ->on('battletags.team_id', '=', 'player.team_name');
            })
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->when($season !== null, fn ($query) => $query->where('replay.season', $season))
            ->groupBy('battletags.battletag')
            ->orderByDesc($alias)
            ->limit(self::LIMIT)
            ->get([
                'battletags.battletag',
                DB::raw($function.'(`'.$stat.'`) AS `'.$alias.'`'),
            ])
            ->map(function ($row) use ($alias) {
                // MySQL hands aggregates back as strings through PDO. Consumers
                // doing arithmetic on them should not have to cast first.
                $row->{$alias} = $row->{$alias} === null ? null : $row->{$alias} + 0;

                return $row;
            })
            ->all();
    }
}
