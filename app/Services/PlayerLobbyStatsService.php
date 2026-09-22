<?php

namespace App\Services;

use App\Models\BannedAccount;
use App\Models\Battletag;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Per-player stats for a lobby: MMR, games and win rate per ranked mode, account
 * level and most-played heroes.
 *
 * Shared by the pre-match page and the Twitch extension, so both show the same
 * numbers from one cache. Each player is cached for six hours, which is what makes
 * a lobby cheap to look up twice.
 */
class PlayerLobbyStatsService
{
    private const CACHE_SECONDS = 21600;

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * @param  Collection<int, object>  $players  each with `blizz_id` and `region`
     * @param  object|null  $viewer  the signed-in main-site user, who may see their own private profile
     * @return array{stats: array<string, array<string, mixed>>, hidden: array<string, bool>} keyed by "blizz_id|region"
     */
    public function forPlayers(Collection $players, ?object $viewer = null): array
    {
        $playerStats = [];
        $missedPlayers = collect();

        // Private and banned players keep their slot and show nothing else — the
        // same rule as their profile pages, owner included.
        $hidden = [];

        foreach ($players as $player) {
            $key = self::key($player);

            if ($this->globalDataService->isRestrictedAccount($player->blizz_id, $player->region)
                && ! ($viewer !== null && ($viewer->blizz_id.'|'.$viewer->region) === $key && ! BannedAccount::where('blizz_id', $player->blizz_id)->where('region', $player->region)->exists())) {
                $hidden[$key] = true;

                continue;
            }

            $cached = Cache::get('prematch_player_stats|'.$key);

            if (! is_null($cached)) {
                $playerStats[$key] = $cached;
            } else {
                $missedPlayers->push($player);
            }
        }

        if ($missedPlayers->isNotEmpty()) {
            $playerStats = array_merge($playerStats, $this->compute($missedPlayers));
        }

        return ['stats' => $playerStats, 'hidden' => $hidden];
    }

    /**
     * Rank tier names for a computed stats row, per mode.
     *
     * @param  array<string, mixed>  $stats
     * @return array{qm_rank: mixed, sl_rank: mixed, ar_rank: mixed}
     */
    public function ranks(array $stats): array
    {
        $tiers = $this->rankTiers();

        return [
            'qm_rank' => is_null($stats['qm_mmr']) ? null : $this->globalDataService->calculateSubTier($tiers['qm'], $stats['qm_mmr']),
            'sl_rank' => is_null($stats['sl_mmr']) ? null : $this->globalDataService->calculateSubTier($tiers['sl'], $stats['sl_mmr']),
            'ar_rank' => is_null($stats['ar_mmr']) ? null : $this->globalDataService->calculateSubTier($tiers['ar'], $stats['ar_mmr']),
        ];
    }

    /** @return array{qm: mixed, sl: mixed, ar: mixed} */
    public function rankTiers(): array
    {
        return [
            'qm' => $this->globalDataService->getRankTiers(1, 10000),
            'sl' => $this->globalDataService->getRankTiers(5, 10000),
            'ar' => $this->globalDataService->getRankTiers(6, 10000),
        ];
    }

    public static function key(object $player): string
    {
        return $player->blizz_id.'|'.$player->region;
    }

    /**
     * @param  Collection<int, object>  $missedPlayers
     * @return array<string, array<string, mixed>>
     */
    private function compute(Collection $missedPlayers): array
    {
        $playerStats = [];

        $blizzIds = $missedPlayers->pluck('blizz_id')->unique()->values()->all();
        $regions = $missedPlayers->pluck('region')->unique()->values()->all();

        $historyStats = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select([
                'player.blizz_id AS blizz_id',
                'replay.region AS region',
                'replay.game_type AS game_type',
                'player.hero AS hero',
                DB::raw('SUM(player.winner = 1) AS wins'),
                DB::raw('SUM(player.winner = 0) AS losses'),
                DB::raw('COUNT(*) AS games'),
            ])
            ->whereIn('player.blizz_id', $blizzIds)
            ->whereIn('replay.region', $regions)
            ->whereIn('replay.game_type', [1, 5, 6])
            ->groupBy('player.blizz_id', 'replay.region', 'replay.game_type', 'player.hero')
            ->get()
            ->groupBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            });

        $qmMMRData = MasterMMRDataQM::select('blizz_id', 'region', 'conservative_rating')
            ->where('type_value', 10000)
            ->where('game_type', 1)
            ->whereIn('blizz_id', $blizzIds)
            ->whereIn('region', $regions)
            ->get()
            ->keyBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            });

        $slMMRData = MasterMMRDataSL::select('blizz_id', 'region', 'conservative_rating')
            ->where('type_value', 10000)
            ->where('game_type', 5)
            ->whereIn('blizz_id', $blizzIds)
            ->whereIn('region', $regions)
            ->get()
            ->keyBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            });

        $arMMRData = MasterMMRDataAR::select('blizz_id', 'region', 'conservative_rating')
            ->where('type_value', 10000)
            ->where('game_type', 6)
            ->whereIn('blizz_id', $blizzIds)
            ->whereIn('region', $regions)
            ->get()
            ->keyBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            });

        $accountLevels = Battletag::select('blizz_id', 'region', 'account_level', 'latest_game')
            ->whereIn('blizz_id', $blizzIds)
            ->whereIn('region', $regions)
            ->get()
            ->groupBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            })
            ->map(function ($rows) {
                return $rows->sortByDesc('latest_game')->first()->account_level;
            });

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');

        foreach ($missedPlayers as $player) {
            $key = self::key($player);
            $playerHistory = $historyStats->get($key, collect());

            $modeStats = [];
            foreach ([1 => 'qm', 5 => 'sl', 6 => 'ar'] as $gameType => $prefix) {
                $modeRows = $playerHistory->where('game_type', $gameType);
                $wins = (int) $modeRows->sum('wins');
                $losses = (int) $modeRows->sum('losses');
                $gamesPlayed = $wins + $losses;

                $modeStats[$prefix] = [
                    'games_played' => $gamesPlayed,
                    'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : null,
                    'top_heroes' => $modeRows->map(function ($row) {
                        return [
                            'hero' => $row->hero,
                            'count' => (int) $row->games,
                        ];
                    })
                        ->sortByDesc('count')
                        ->take(3)
                        ->values(),
                ];
            }

            $combinedTopHeroes = collect([$modeStats['qm']['top_heroes'], $modeStats['sl']['top_heroes'], $modeStats['ar']['top_heroes']])
                ->flatten(1)
                ->groupBy('hero')
                ->map(function ($heroGames, $hero) {
                    return [
                        'hero' => $hero,
                        'count' => $heroGames->sum('count'),
                    ];
                })
                ->sortByDesc('count')
                ->take(3)
                ->values()
                ->map(function ($data) use ($heroData) {
                    return [
                        'hero' => $heroData[$data['hero']],
                        'count' => $data['count'],
                    ];
                });

            $qmMMRRow = $qmMMRData->get($key);
            $slMMRRow = $slMMRData->get($key);
            $arMMRRow = $arMMRData->get($key);

            $qm_mmr = $qmMMRRow ? round(1800 + ($qmMMRRow->conservative_rating * 40)) : 1800;
            $sl_mmr = $slMMRRow ? round(1800 + ($slMMRRow->conservative_rating * 40)) : 1800;
            $ar_mmr = $arMMRRow ? round(1800 + ($arMMRRow->conservative_rating * 40)) : 1800;

            $stats = [
                'blizz_id' => $player->blizz_id,
                'region' => $player->region,
                'account_level' => $accountLevels->get($key),

                'qm_mmr' => $qm_mmr == 1800 ? null : $qm_mmr,
                'qm_games_played' => $modeStats['qm']['games_played'],
                'qm_win_rate' => $modeStats['qm']['win_rate'],

                'sl_mmr' => $sl_mmr == 1800 ? null : $sl_mmr,
                'sl_games_played' => $modeStats['sl']['games_played'],
                'sl_win_rate' => $modeStats['sl']['win_rate'],

                'ar_mmr' => $ar_mmr == 1800 ? null : $ar_mmr,
                'ar_games_played' => $modeStats['ar']['games_played'],
                'ar_win_rate' => $modeStats['ar']['win_rate'],

                'top_heroes' => $combinedTopHeroes,
            ];

            Cache::put('prematch_player_stats|'.$key, $stats, self::CACHE_SECONDS);
            $playerStats[$key] = $stats;
        }

        return $playerStats;
    }
}
