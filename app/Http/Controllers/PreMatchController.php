<?php

namespace App\Http\Controllers;

use App\Models\Battletag;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\Prematch;
use App\Rules\PrematchIDValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PreMatchController extends Controller
{
    public function show(Request $request, $prematchID)
    {
        $validationRules = [
            'prematchID' => ['required', 'integer', new PrematchIDValidation],
        ];

        $validator = Validator::make(compact('prematchID'), $validationRules);
        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        return view('prematch')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'prematchid' => $prematchID,
        ]);
    }

    public function getData(Request $request)
    {
        $validationRules = [
            'prematchid' => ['required', 'integer', new PrematchIDValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $prematchID = $request['prematchid'];

        $data = Prematch::select('team', 'battletag', 'blizz_id', 'region')->where('prematch_replayID', $prematchID)->get();

        $rankTiersQM = $this->globalDataService->getRankTiers(1, 10000);
        $rankTiersSL = $this->globalDataService->getRankTiers(5, 10000);
        $rankTiersAR = $this->globalDataService->getRankTiers(6, 10000);

        $playerStats = [];
        $missedPlayers = collect();

        foreach ($data as $player) {
            $key = $player->blizz_id.'|'.$player->region;
            $cached = Cache::get('prematch_player_stats|'.$key);

            if (! is_null($cached)) {
                $playerStats[$key] = $cached;
            } else {
                $missedPlayers->push($player);
            }
        }

        if ($missedPlayers->isNotEmpty()) {
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
                $key = $player->blizz_id.'|'.$player->region;
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

                Cache::put('prematch_player_stats|'.$key, $stats, 21600);
                $playerStats[$key] = $stats;
            }
        }

        // Group the data by team and use the rankTiers variables in the closure
        $groupedData = $data->groupBy('team')->map(function ($teamData, $team) use ($rankTiersQM, $rankTiersSL, $rankTiersAR, $playerStats) {
            return [
                'players' => $teamData->map(function ($player) use ($rankTiersQM, $rankTiersSL, $rankTiersAR, $playerStats) {
                    $stats = $playerStats[$player->blizz_id.'|'.$player->region];

                    return [
                        'battletag' => explode('#', $player->battletag)[0],
                        'blizz_id' => $stats['blizz_id'],
                        'region' => $stats['region'],
                        'account_level' => $stats['account_level'],

                        'qm_mmr' => $stats['qm_mmr'],
                        'qm_rank' => is_null($stats['qm_mmr']) ? null : $this->globalDataService->calculateSubTier($rankTiersQM, $stats['qm_mmr']),
                        'qm_games_played' => $stats['qm_games_played'],
                        'qm_win_rate' => $stats['qm_win_rate'],

                        'sl_mmr' => $stats['sl_mmr'],
                        'sl_rank' => is_null($stats['sl_mmr']) ? null : $this->globalDataService->calculateSubTier($rankTiersSL, $stats['sl_mmr']),
                        'sl_games_played' => $stats['sl_games_played'],
                        'sl_win_rate' => $stats['sl_win_rate'],

                        'ar_mmr' => $stats['ar_mmr'],
                        'ar_rank' => is_null($stats['ar_mmr']) ? null : $this->globalDataService->calculateSubTier($rankTiersAR, $stats['ar_mmr']),
                        'ar_games_played' => $stats['ar_games_played'],
                        'ar_win_rate' => $stats['ar_win_rate'],

                        'top_heroes' => $stats['top_heroes'],
                    ];
                }),
            ];
        });

        $groupedDataWithAverages = $groupedData->map(function ($teamData, $team) use ($rankTiersQM, $rankTiersSL, $rankTiersAR) {
            $totalAccountLevel = $teamData['players']->sum('account_level');
            $totalQMMMR = $teamData['players']->sum('qm_mmr');
            $totalSLMMR = $teamData['players']->sum('sl_mmr');
            $totalARMMR = $teamData['players']->sum('ar_mmr');

            $averageAccountLevel = round($totalAccountLevel / 5);
            $averageQMMMR = round($totalQMMMR / 5);
            $averageSLMMR = round($totalSLMMR / 5);
            $averageARMMR = round($totalARMMR / 5);

            $playerWithHighestAccountLevel = $teamData['players']->sortByDesc('account_level')->first();

            $bestQMRank = $teamData['players']->sortByDesc('qm_mmr')->first();
            $bestSLRank = $teamData['players']->sortByDesc('sl_mmr')->first();
            $bestARRank = $teamData['players']->sortByDesc('ar_mmr')->first();

            return [
                'players' => $teamData['players'],
                'average_account_level' => $averageAccountLevel,
                'average_qm_mmr' => $averageQMMMR,
                'average_qm_rank' => $this->globalDataService->calculateSubTier($rankTiersQM, $averageQMMMR),

                'average_sl_mmr' => $averageSLMMR,
                'average_sl_rank' => $this->globalDataService->calculateSubTier($rankTiersSL, $averageSLMMR),

                'average_ar_mmr' => $averageARMMR,
                'average_ar_rank' => $this->globalDataService->calculateSubTier($rankTiersAR, $averageARMMR),

                'highest_account_level_battletag' => $playerWithHighestAccountLevel ? $playerWithHighestAccountLevel['battletag'] : null,
                'highest_qm_mmr_battletag' => $bestQMRank ? $bestQMRank['battletag'] : null,
                'highest_sl_mmr_battletag' => $bestSLRank ? $bestSLRank['battletag'] : null,
                'highest_ar_mmr_battletag' => $bestARRank ? $bestARRank['battletag'] : null,
            ];
        });

        return $groupedDataWithAverages;
    }
}
