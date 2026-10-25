<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\BattletagRealRank;
use App\Models\SeasonDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * A player's real Storm League rank, as the uploader's rank reader read it off loading screens
 * (battletags_real_ranks, see RealRankService): their latest rank and that season's rank periods.
 */
class PlayerRealRankController extends Controller
{
    private const LEAGUES = [1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 6 => 'master'];

    public function getData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $blizz_id = (int) $request['blizz_id'];
        $region = (int) $request['region'];

        $latest = BattletagRealRank::where('blizz_id', $blizz_id)->where('region', $region)
            ->orderByDesc('last_game_date')->first();

        $season = null;
        $periods = [];
        if ($latest) {
            $season = $latest->season ? SeasonDate::find($latest->season) : null;
            $periods = BattletagRealRank::where('blizz_id', $blizz_id)->where('region', $region)
                ->where('season', $latest->season)
                // Oldest first, so the profile reads left to right like a timeline
                ->orderBy('last_game_date')
                ->get()
                ->map(fn ($period) => $this->format($period))
                ->all();
        }

        return [
            'current' => $latest ? $this->format($latest) : null,
            'season' => $season ? $season->year.' Season '.(float) $season->season : null,
            'periods' => $periods,
            // False when the latest rank read is from an earlier season, e.g. early in a new season
            'current_season' => $latest !== null && $latest->season === (int) $this->globalDataService->getDefaultSeason(),
        ];
    }

    private function format(BattletagRealRank $period): array
    {
        $league = $period->unranked ? 'unranked' : ($period->grandmaster ? 'grandmaster' : (self::LEAGUES[$period->league_tier] ?? null));

        return [
            'league' => $league,
            'division' => $period->division,
            'ladder_position' => $period->ladder_position,
            'first_game_date' => $period->first_game_date?->toDateTimeString(),
            'last_game_date' => $period->last_game_date?->toDateTimeString(),
            'read_count' => $period->read_count,
        ];
    }
}
