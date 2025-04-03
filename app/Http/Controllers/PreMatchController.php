<?php

namespace App\Http\Controllers;

use App\Models\Battletag;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\Prematch;
use App\Rules\PrematchIDValidation;
use Illuminate\Http\Request;
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
            if (env('Production')) {
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

        // Group the data by team and use the rankTiers variables in the closure
        $groupedData = $data->groupBy('team')->map(function ($teamData, $team) use ($rankTiersQM, $rankTiersSL, $rankTiersAR) {
            return [
                'players' => $teamData->map(function ($player) use ($rankTiersQM, $rankTiersSL, $rankTiersAR) {
                    $qm_mmr = round(1800 + (MasterMMRDataQM::where('type_value', 10000)
                        ->where('game_type', 1)
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->value('conservative_rating') * 40));

                    $sl_mmr = round(1800 + (MasterMMRDataSL::where('type_value', 10000)
                        ->where('game_type', 5)
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->value('conservative_rating') * 40));

                    $ar_mmr = round(1800 + (MasterMMRDataAR::where('type_value', 10000)
                        ->where('game_type', 6)
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->value('conservative_rating') * 40));

                    $qm_data = DB::table('replay')
                        ->join('player', 'player.replayID', '=', 'replay.replayID')
                        ->select([
                            'replay.replayID AS replayID',
                            'player.winner AS winner',
                            'player.hero AS hero',
                        ])
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->where('game_type', 1)
                        ->get();
                    $qm_wins = $qm_data->where('winner', 1)->count();
                    $qm_losses = $qm_data->where('winner', 0)->count();
                    $qm_games_played = $qm_wins + $qm_losses;

                    $qm_topHeroes = $qm_data->groupBy('hero')
                        ->map(function ($heroGames, $hero) {
                            return [
                                'hero' => $hero,
                                'count' => $heroGames->count(),
                            ];
                        })
                        ->sortByDesc('count')
                        ->take(3)
                        ->values();

                    $sl_data = DB::table('replay')
                        ->join('player', 'player.replayID', '=', 'replay.replayID')
                        ->select([
                            'replay.replayID AS replayID',
                            'player.winner AS winner',
                            'player.hero AS hero',
                        ])
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->where('game_type', 5)
                        ->get();
                    $sl_wins = $sl_data->where('winner', 1)->count();
                    $sl_losses = $sl_data->where('winner', 0)->count();
                    $sl_games_played = $sl_wins + $sl_losses;

                    $sl_topHeroes = $sl_data->groupBy('hero')
                        ->map(function ($heroGames, $hero) {
                            return [
                                'hero' => $hero,
                                'count' => $heroGames->count(),
                            ];
                        })
                        ->sortByDesc('count')
                        ->take(3)
                        ->values();

                    $ar_data = DB::table('replay')
                        ->join('player', 'player.replayID', '=', 'replay.replayID')
                        ->select([
                            'replay.replayID AS replayID',
                            'player.winner AS winner',
                            'player.hero AS hero',
                        ])
                        ->where('blizz_id', $player->blizz_id)
                        ->where('region', $player->region)
                        ->where('game_type', 6)
                        ->get();
                    $ar_wins = $ar_data->where('winner', 1)->count();
                    $ar_losses = $ar_data->where('winner', 0)->count();
                    $ar_games_played = $ar_wins + $ar_losses;

                    $ar_topHeroes = $ar_data->groupBy('hero')
                        ->map(function ($heroGames, $hero) {
                            return [
                                'hero' => $hero,
                                'count' => $heroGames->count(),
                            ];
                        })
                        ->sortByDesc('count')
                        ->take(3)
                        ->values();

                    $combinedTopHeroes = collect([$qm_topHeroes, $sl_topHeroes, $ar_topHeroes])
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
                        ->values();

                    $heroData = $this->globalDataService->getHeroes();
                    $heroData = $heroData->keyBy('id');

                    $combinedTopHeroes = $combinedTopHeroes->map(function ($data) use ($heroData) {
                        return [
                            'hero' => $heroData[$data['hero']],
                            'count' => $data['count'],
                        ];
                    });

                    return [
                        'battletag' => explode('#', $player->battletag)[0],
                        'blizz_id' => $player->blizz_id,
                        'region' => $player->region,
                        'account_level' => Battletag::select('account_level')
                            ->where('blizz_id', $player->blizz_id)
                            ->where('region', $player->region)
                            ->orderBy('latest_game', 'desc')
                            ->limit(1)
                            ->value('account_level'),

                        'qm_mmr' => $qm_mmr == 1800 ? null : $qm_mmr,
                        'qm_rank' => $qm_mmr == 1800 ? null : $this->globalDataService->calculateSubTier($rankTiersQM, $qm_mmr),
                        'qm_games_played' => $qm_games_played,
                        'qm_win_rate' => $qm_games_played > 0 ? round(($qm_wins / $qm_games_played) * 100, 2) : null,

                        'sl_mmr' => $sl_mmr == 1800 ? null : $sl_mmr,
                        'sl_rank' => $sl_mmr == 1800 ? null : $this->globalDataService->calculateSubTier($rankTiersSL, $sl_mmr),
                        'sl_games_played' => $sl_games_played,
                        'sl_win_rate' => $sl_games_played > 0 ? round(($sl_wins / $sl_games_played) * 100, 2) : null,

                        'ar_mmr' => $ar_mmr == 1800 ? null : $ar_mmr,
                        'ar_rank' => $ar_mmr == 1800 ? null : $this->globalDataService->calculateSubTier($rankTiersAR, $ar_mmr),
                        'ar_games_played' => $ar_games_played,
                        'ar_win_rate' => $ar_games_played > 0 ? round(($ar_wins / $ar_games_played) * 100, 2) : null,

                        'top_heroes' => $combinedTopHeroes,
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
            $bestSLRank = $teamData['players']->sortByDesc('slmmr')->first();
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
