<?php

namespace App\Http\Controllers\Global;
use App\Services\GlobalDataService;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\GameTypeInputValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\MMRTypeInputValidation;
use App\Rules\StackSizeInputValidation;

use App\Models\Leaderboard;

class GlobalLeaderboardController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Leaderboard.globalLeaderboard');
    }

    public function getLeaderboardData(Request $request){
        $gameType = (new GameTypeInputValidation())->passes('gameType', [$request["gameType"]]);
        $season = (new SeasonInputValidation())->passes('season', $request["season"]);
        $type = (new MMRTypeInputValidation())->passes('type', $request["type"]);
        $stackSize = (new StackSizeInputValidation())->passes('stackSize', $request["stackSize"]);

        $data = Leaderboard::query()
            ->select('rank', 'split_battletag as battletag', 'blizz_id', 'region', 'win_rate', 'games_played', 'conservative_rating', 'rating', 'normalized_rating', 'most_played_hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty', 'hero_build_games_played')
            ->filterByGameType($gameType)
            ->filterBySeason($season)
            ->filterByType($type)
            ->filterByStackSize($stackSize)
            //->toSql();
            ->get();
        return $data;
    }
}
