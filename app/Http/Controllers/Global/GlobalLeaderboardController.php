<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Rules\SeasonInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RegionInputValidation;
use App\Rules\MMRTypeInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\RoleInputValidation;

use App\Models\Leaderboard;
use App\Models\Hero;
use App\Models\HeroesDataTalent;

class GlobalLeaderboardController extends GlobalsInputValidationController
{
    public function show(Request $request){
        return view('Global.Leaderboard.globalLeaderboard')->with([
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaultseason' => (string)$this->globalDataService->getDefaultSeason(),
        ]);
    }

    public function getLeaderboardData(Request $request){
        //return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new SeasonInputValidation()],
            'game_type' => ['required', new GameTypeInputValidation()],
            'type' => 'required|in:player,hero,role',
            'groupsize' => ['required', new StackSizeInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'region' => ['sometimes', 'nullable', new RegionInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                "data" => $request->all(),
                "status" => "failure to validate inputs"
            ];
        }

        $hero = $request["hero"];
        $role = $this->getMMRTypeValue($request["role"]);

        $gameType = $this->getGameTypeFilterValues($request["game_type"]); 
        $season = $request["season"];
        $region = $this->getRegionFilterValues($request["region"]);

        $type = $request["type"];
        $typeNumber = 0;

        if($type == "player"){
            $typeNumber = $this->getMMRTypeValue($request["type"]);
        }else if($type == "hero"){
            $typeNumber = $hero;
        }else if($type == "role"){
            $typeNumber = $role;
        }

        $groupsize = -1;
        if($season < 20){
            $groupsize = 0;
        }else{
            if($request["groupsize"] == "Solo"){
                $groupsize = 1;
            }else if($request["groupsize"] == "Duo"){
                $groupsize = 2;
            }else if($request["groupsize"] == "3 Players"){
                $groupsize = 3;
            }else if($request["groupsize"] == "4 Players"){
                $groupsize = 4;
            }else if($request["groupsize"] == "5 Players"){
                $groupsize = 5;
            }else if($request["groupsize"] == "All"){
                $groupsize = 0;
            }
        }

        $data = Leaderboard::query()
            ->select('rank', 'split_battletag as battletag', 'blizz_id', 'region', 'win_rate', 'games_played', 'conservative_rating', 'rating', 'normalized_rating', 'most_played_hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty', 'hero_build_games_played')
            ->filterByGameType($gameType)
            ->filterBySeason($season)
            ->filterByType($typeNumber)
            ->filterByStackSize($groupsize)
            ->filterByRegion($region)
            //->toSql();
            ->get();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $rankTiers = $this->globalDataService->getRankTiers($gameType, $typeNumber);

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $data = $data->map(function ($item) use ($gameType, $heroData, $rankTiers, $talentData, $type, $typeNumber) {
            $item->mmr = round(1800 + 40 * $item->conservative_rating); 
            $item->win_rate = round($item->win_rate, 2);
            $item->rating = round($item->rating, 2);
            $item->most_played_hero = $item->most_played_hero ? $heroData[$item->most_played_hero] : null;
            $item->tier = $this->globalDataService->calculateSubTier($rankTiers, $item->mmr);
            $item->region_id = $item->region;
            $item->region = $this->globalDataService->getRegionIDtoString()[$item->region];


            $item->level_one = $item->level_one && $item->level_one != 0 ? $talentData[$item->level_one] : null;
            $item->level_four = $item->level_four && $item->level_four != 0 ? $talentData[$item->level_four] : null;
            $item->level_seven = $item->level_seven && $item->level_seven != 0 ? $talentData[$item->level_seven] : null;
            $item->level_ten = $item->level_ten && $item->level_ten != 0 ? $talentData[$item->level_ten] : null;
            $item->level_thirteen = $item->level_thirteen && $item->level_thirteen != 0 ? $talentData[$item->level_thirteen] : null;
            $item->level_sixteen = $item->level_sixteen && $item->level_sixteen != 0 ? $talentData[$item->level_sixteen] : null;
            $item->level_twenty = $item->level_twenty && $item->level_twenty != 0 ? $talentData[$item->level_twenty] : null;

            $item->hero = $type == "hero" ? $heroData[$typeNumber] : null;

            return $item;
        });
        return $data;
    }


}
