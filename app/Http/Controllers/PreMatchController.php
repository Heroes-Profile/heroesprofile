<?php

namespace App\Http\Controllers;

use App\Models\Prematch;
use App\Rules\PrematchIDValidation;
use App\Services\PlayerLobbyStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PreMatchController extends Controller
{
    /** A private or banned player: the slot stays, nothing about them does. */
    private const EMPTY_SLOT = [
        'hidden' => true,
        'battletag' => null,
        'blizz_id' => null,
        'region' => null,
        'account_level' => null,
        'qm_mmr' => null,
        'qm_rank' => null,
        'qm_games_played' => null,
        'qm_win_rate' => null,
        'sl_mmr' => null,
        'sl_rank' => null,
        'sl_games_played' => null,
        'sl_win_rate' => null,
        'ar_mmr' => null,
        'ar_rank' => null,
        'ar_games_played' => null,
        'ar_win_rate' => null,
        'top_heroes' => [],
    ];

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

        // Private and banned players keep their slot and show nothing else — the
        // same rule as their profile pages, owner included.
        ['stats' => $playerStats, 'hidden' => $hidden] = app(PlayerLobbyStatsService::class)->forPlayers($data, Auth::user());

        // Group the data by team and use the rankTiers variables in the closure
        $groupedData = $data->groupBy('team')->map(function ($teamData, $team) use ($rankTiersQM, $rankTiersSL, $rankTiersAR, $playerStats, $hidden) {
            return [
                'players' => $teamData->map(function ($player) use ($rankTiersQM, $rankTiersSL, $rankTiersAR, $playerStats, $hidden) {
                    if (isset($hidden[$player->blizz_id.'|'.$player->region])) {
                        return self::EMPTY_SLOT;
                    }

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
            // Only players with a value: hidden slots and unrated players are not zeros.
            $average = function (string $field) use ($teamData) {
                $values = $teamData['players']->pluck($field)->filter(fn ($value) => $value !== null);

                return $values->isEmpty() ? null : round($values->avg());
            };

            $averageAccountLevel = $average('account_level');
            $averageQMMMR = $average('qm_mmr');
            $averageSLMMR = $average('sl_mmr');
            $averageARMMR = $average('ar_mmr');

            $playerWithHighestAccountLevel = $teamData['players']->sortByDesc('account_level')->first();

            $bestQMRank = $teamData['players']->sortByDesc('qm_mmr')->first();
            $bestSLRank = $teamData['players']->sortByDesc('sl_mmr')->first();
            $bestARRank = $teamData['players']->sortByDesc('ar_mmr')->first();

            return [
                'players' => $teamData['players'],
                'average_account_level' => $averageAccountLevel,
                'average_qm_mmr' => $averageQMMMR,
                'average_qm_rank' => $averageQMMMR === null ? null : $this->globalDataService->calculateSubTier($rankTiersQM, $averageQMMMR),

                'average_sl_mmr' => $averageSLMMR,
                'average_sl_rank' => $averageSLMMR === null ? null : $this->globalDataService->calculateSubTier($rankTiersSL, $averageSLMMR),

                'average_ar_mmr' => $averageARMMR,
                'average_ar_rank' => $averageARMMR === null ? null : $this->globalDataService->calculateSubTier($rankTiersAR, $averageARMMR),

                'highest_account_level_battletag' => $playerWithHighestAccountLevel ? $playerWithHighestAccountLevel['battletag'] : null,
                'highest_qm_mmr_battletag' => $bestQMRank ? $bestQMRank['battletag'] : null,
                'highest_sl_mmr_battletag' => $bestSLRank ? $bestSLRank['battletag'] : null,
                'highest_ar_mmr_battletag' => $bestARRank ? $bestARRank['battletag'] : null,
            ];
        });

        return $groupedDataWithAverages;
    }
}
