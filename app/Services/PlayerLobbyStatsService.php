<?php

namespace App\Services;

use App\Models\BannedAccount;
use App\Models\Battletag;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use Carbon\Carbon;
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

    /** Recent form is read right before a game, so it can't sit for six hours. */
    private const RECENT_GAMES_CACHE_SECONDS = 900;

    private const RECENT_GAMES_PER_MODE = 10;

    /** No index serves "latest games for a player", so the scan is bounded by date instead. */
    private const RECENT_GAMES_DAYS = 90;

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

        $hidden = $this->hiddenKeys($players, $viewer);

        foreach ($players as $player) {
            $key = self::key($player);

            if (isset($hidden[$key])) {
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
     * Private and banned players keep their slot and show nothing else — the
     * same rule as their profile pages, owner included.
     *
     * @param  Collection<int, object>  $players
     * @return array<string, bool> keyed by "blizz_id|region"
     */
    public function hiddenKeys(Collection $players, ?object $viewer = null): array
    {
        $hidden = [];

        foreach ($players as $player) {
            $key = self::key($player);

            if ($this->globalDataService->isRestrictedAccount($player->blizz_id, $player->region)
                && ! ($viewer !== null && ($viewer->blizz_id.'|'.$viewer->region) === $key && ! BannedAccount::where('blizz_id', $player->blizz_id)->where('region', $player->region)->exists())) {
                $hidden[$key] = true;
            }
        }

        return $hidden;
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

    /**
     * Last ten games per ranked mode, newest first. Pre-match page only — the
     * Twitch extension doesn't show recent form.
     *
     * @param  Collection<int, object>  $players  each with `blizz_id` and `region`, hidden players already removed
     * @return array<string, array{qm: array, sl: array, ar: array}> keyed by "blizz_id|region"
     */
    public function recentGames(Collection $players): array
    {
        $recentGames = [];
        $missedPlayers = collect();

        foreach ($players as $player) {
            $key = self::key($player);
            $cached = Cache::get('prematch_recent_games|'.$key);

            if (! is_null($cached)) {
                $recentGames[$key] = $cached;
            } else {
                $missedPlayers->push($player);
            }
        }

        if ($missedPlayers->isEmpty()) {
            return $recentGames;
        }

        $blizzIds = $missedPlayers->pluck('blizz_id')->unique()->values()->all();
        $regions = $missedPlayers->pluck('region')->unique()->values()->all();
        $startReplayID = $this->recentWindowStartReplayID();

        $ranked = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->select([
                'player.blizz_id AS blizz_id',
                'replay.region AS region',
                'replay.game_type AS game_type',
                'replay.replayID AS replayID',
                'replay.game_date AS game_date',
                'replay.game_map AS game_map',
                'player.hero AS hero',
                'player.winner AS winner',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY player.blizz_id, replay.region, replay.game_type ORDER BY replay.replayID DESC) AS row_num'),
            ])
            ->whereIn('player.blizz_id', $blizzIds)
            ->whereIn('replay.region', $regions)
            ->whereIn('replay.game_type', [1, 5, 6])
            ->where('replay.replayID', '>=', $startReplayID)
            ->where('player.replayID', '>=', $startReplayID);

        $rows = DB::query()
            ->fromSub($ranked, 'ranked')
            ->where('row_num', '<=', self::RECENT_GAMES_PER_MODE)
            ->orderByDesc('replayID')
            ->get()
            ->groupBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            });

        $heroes = $this->globalDataService->getHeroesByID();
        $maps = $this->globalDataService->getAllMapsKeyed();

        foreach ($missedPlayers as $player) {
            $key = self::key($player);
            $playerRows = $rows->get($key, collect());

            $games = [];
            foreach ([1 => 'qm', 5 => 'sl', 6 => 'ar'] as $gameType => $prefix) {
                $games[$prefix] = $playerRows->where('game_type', $gameType)
                    ->map(function ($row) use ($heroes, $maps) {
                        $hero = $heroes->get($row->hero);
                        $map = $maps->get($row->game_map);

                        return [
                            'replayID' => $row->replayID,
                            'game_date' => $row->game_date,
                            'winner' => (int) $row->winner === 1,
                            'hero' => $hero ? ['id' => $hero->id, 'name' => $hero->name, 'short_name' => $hero->short_name] : null,
                            'game_map' => $map ? ['name' => $map->name, 'sanitized_map_name' => $map->sanitized_map_name] : null,
                        ];
                    })
                    ->values()
                    ->all();
            }

            Cache::put('prematch_recent_games|'.$key, $games, self::RECENT_GAMES_CACHE_SECONDS);
            $recentGames[$key] = $games;
        }

        return $recentGames;
    }

    public static function key(object $player): string
    {
        return $player->blizz_id.'|'.$player->region;
    }

    /**
     * Lowest replayID among games in the recent window. Taken from the window's
     * first week only: later games are uploaded later, so their replayIDs are higher.
     */
    private function recentWindowStartReplayID(): int
    {
        $windowStart = now()->subDays(self::RECENT_GAMES_DAYS)->toDateString();

        return (int) Cache::remember('prematch_recent_window_start_replay|'.$windowStart, 86400, function () use ($windowStart) {
            return DB::table('replay')
                ->where('game_date', '>=', $windowStart)
                ->where('game_date', '<', Carbon::parse($windowStart)->addDays(7)->toDateString())
                ->min('replayID') ?? 0;
        });
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

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');

        // Lifetime totals (10000) and per-hero rows, the same source as the profile pages.
        $typeValues = array_merge([10000], $heroData->keys()->all());

        $mmrData = [];
        foreach ([1 => MasterMMRDataQM::class, 5 => MasterMMRDataSL::class, 6 => MasterMMRDataAR::class] as $gameType => $model) {
            $mmrData[$gameType] = $model::select('type_value', 'blizz_id', 'region', 'conservative_rating', 'win', 'loss')
                ->whereIn('type_value', $typeValues)
                ->where('game_type', $gameType)
                ->whereIn('blizz_id', $blizzIds)
                ->whereIn('region', $regions)
                ->get()
                ->groupBy(function ($row) {
                    return $row->blizz_id.'|'.$row->region;
                });
        }

        $latestBattletags = Battletag::select('blizz_id', 'region', 'account_level', 'latest_game')
            ->whereIn('blizz_id', $blizzIds)
            ->whereIn('region', $regions)
            ->get()
            ->groupBy(function ($row) {
                return $row->blizz_id.'|'.$row->region;
            })
            ->map(function ($rows) {
                return $rows->sortByDesc('latest_game')->first();
            });

        foreach ($missedPlayers as $player) {
            $key = self::key($player);

            $modeStats = [];
            foreach ([1 => 'qm', 5 => 'sl', 6 => 'ar'] as $gameType => $prefix) {
                $modeRows = $mmrData[$gameType]->get($key, collect());
                $totalRow = $modeRows->firstWhere('type_value', 10000);
                $gamesPlayed = $totalRow ? $totalRow->win + $totalRow->loss : 0;
                $mmr = $totalRow ? round(1800 + ($totalRow->conservative_rating * 40)) : 1800;

                $modeStats[$prefix] = [
                    'mmr' => $mmr == 1800 ? null : $mmr,
                    'games_played' => $gamesPlayed,
                    'win_rate' => $gamesPlayed > 0 ? round(($totalRow->win / $gamesPlayed) * 100, 2) : null,
                    'top_heroes' => $modeRows->where('type_value', '!=', 10000)
                        ->map(function ($row) {
                            return [
                                'hero' => $row->type_value,
                                'count' => $row->win + $row->loss,
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

            $stats = [
                'blizz_id' => $player->blizz_id,
                'region' => $player->region,
                'account_level' => $latestBattletags->get($key)?->account_level,
                'last_played' => $latestBattletags->get($key)?->latest_game,

                'qm_mmr' => $modeStats['qm']['mmr'],
                'qm_games_played' => $modeStats['qm']['games_played'],
                'qm_win_rate' => $modeStats['qm']['win_rate'],

                'sl_mmr' => $modeStats['sl']['mmr'],
                'sl_games_played' => $modeStats['sl']['games_played'],
                'sl_win_rate' => $modeStats['sl']['win_rate'],

                'ar_mmr' => $modeStats['ar']['mmr'],
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
