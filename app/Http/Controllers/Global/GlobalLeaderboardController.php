<?php

namespace App\Http\Controllers\Global;
use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\SingleGameTypeInputValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\MMRTypeInputValidation;
use App\Rules\StackSizeInputValidation;

use App\Models\Leaderboard;
use App\Models\Hero;

class GlobalLeaderboardController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Leaderboard.globalLeaderboard')->with([
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
            'defaultseason' => (string)$this->globalDataService->getDefaultSeason(),
        ]);
    }

    public function getLeaderboardData(Request $request){
        //return response()->json($request->all());

        $gameType = (new SingleGameTypeInputValidation())->passes('gameType', $request["gameType"]);
        $season = (new SeasonInputValidation())->passes('season', $request["season"]);
        $type = (new MMRTypeInputValidation())->passes('type', $request["type"]);
        $groupsize = (new StackSizeInputValidation())->passes('groupsize', $request["groupsize"]);

        $data = Leaderboard::query()
            ->select('rank', 'split_battletag as battletag', 'blizz_id', 'region', 'win_rate', 'games_played', 'conservative_rating', 'rating', 'normalized_rating', 'most_played_hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty', 'hero_build_games_played')
            ->filterByGameType($gameType)
            ->filterBySeason($season)
            ->filterByType($type)
            ->filterByStackSize($groupsize)
            //->toSql();
            ->get();

        $heroData = Hero::all();
        $heroData = $heroData->keyBy('id');

        $rankTiers = $this->globalDataService->getRankTiers($gameType, $type);

        $data = $data->map(function ($item) use ($gameType, $heroData, $rankTiers) {
            $item->conservative_rating = round(1800 + 40 *$item->conservative_rating); 
            $item->win_rate = round($item->win_rate, 2);
            $item->rating = round($item->rating, 2);
            $item->most_played_hero = $item->most_played_hero ? $heroData[$item->most_played_hero] : null;
            $item->tier = $this->globalDataService->calculateSubTier($rankTiers, $item->conservative_rating);

            return $item;
        });
        return $data;
    }


}
