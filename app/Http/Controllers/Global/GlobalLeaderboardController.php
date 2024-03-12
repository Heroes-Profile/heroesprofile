<?php

namespace App\Http\Controllers\Global;

use App\Models\BattlenetAccount;
use App\Models\HeroesDataTalent;
use App\Models\Leaderboard;
use App\Models\MasterGamesPlayedData;
use App\Models\MasterGamesPlayedDataGroups;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataUD;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RegionInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Rules\TierInputByIDValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GlobalLeaderboardController extends GlobalsInputValidationController
{
    public function show(Request $request)
    {
        return view('Global.Leaderboard.globalLeaderboard')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault('single'),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaultseason' => (string) $this->globalDataService->getDefaultSeason(),
            'weekssincestart' => $this->globalDataService->getWeeksSinceSeasonStart(),
        ]);
    }

    public function getLeaderboardData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new SeasonInputValidation()],
            'game_type' => ['required', new GameTypeInputValidation()],
            'type' => 'required|in:player,hero,role',
            'groupsize' => ['required', new StackSizeInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'region' => ['sometimes', 'nullable', new RegionInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'tierrank' => ['sometimes', 'nullable', new TierInputByIDValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $request['hero'];
        $role = $this->getMMRTypeValue($request['role']);

        $gameType = $this->getGameTypeFilterValues($request['game_type']);
        $season = $request['season'];
        $region = $this->getRegionFilterValues($request['region']);
        $tierrank = $request['tierrank'];
        $type = $request['type'];
        $typeNumber = 0;

        if ($type == 'player') {
            $typeNumber = $this->getMMRTypeValue($request['type']);
        } elseif ($type == 'hero') {
            $typeNumber = $hero;
        } elseif ($type == 'role') {
            $typeNumber = $role;
        }

        $groupsize = -1;
        if ($season < 20) {
            $groupsize = 0;
        } else {
            if ($request['groupsize'] == 'Solo') {
                $groupsize = 1;
            } elseif ($request['groupsize'] == 'Duo') {
                $groupsize = 2;
            } elseif ($request['groupsize'] == '3 Players') {
                $groupsize = 3;
            } elseif ($request['groupsize'] == '4 Players') {
                $groupsize = 4;
            } elseif ($request['groupsize'] == '5 Players') {
                $groupsize = 5;
            } elseif ($request['groupsize'] == 'All') {
                $groupsize = 0;
            }
        }

        $data = Leaderboard::query()
            ->select('rank', 'battletag', 'split_battletag', 'blizz_id', 'region', 'win_rate', 'games_played', 'conservative_rating', 'rating', 'normalized_rating', 'most_played_hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty', 'hero_build_games_played')
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

        $patreonAccounts = BattlenetAccount::has('patreonAccount')->get();

        $blizzIDRegionMapping = [];

        $data = $data->map(function ($item) use ($heroData, $rankTiers, $talentData, $type, $typeNumber, $patreonAccounts, &$blizzIDRegionMapping, $tierrank) {

            if (array_key_exists($item->blizz_id.'|'.$item->region, $blizzIDRegionMapping)) {
                return null;
            } else {
                $blizzIDRegionMapping[$item->blizz_id.'|'.$item->region] = 'found';
            }

            $patreonAccount = $patreonAccounts->where('blizz_id', $item->blizz_id)->where('region', $item->region);

            $item->patreon = is_null($patreonAccount) || empty($patreonAccount) || count($patreonAccount) == 0 ? false : true;
            $item->hp_owner = ($item->blizz_id == 67280 && $item->region == 1) ? true : false;
            $item->mmr = round(1800 + 40 * $item->conservative_rating);
            $item->win_rate = round($item->win_rate, 2);
            $item->rating = round($item->rating, 2);
            $item->most_played_hero = $item->most_played_hero ? $heroData[$item->most_played_hero] : null;
            $item->tier = $this->globalDataService->calculateSubTier($rankTiers, $item->mmr);
            $item->tier_id = $this->globalDataService->calculateTierID($item->tier);

            if ($tierrank && $tierrank != intval($item->tier_id)) {
                return null;
            }

            $item->region_id = $item->region;
            $item->region = $this->globalDataService->getRegionIDtoString()[$item->region];

            $item->level_one = $item->level_one && $item->level_one != 0 ? isset($talentData[$item->level_one]) ? $talentData[$item->level_one] : null : null;
            $item->level_four = $item->level_four && $item->level_four != 0 ? isset($talentData[$item->level_four]) ? $talentData[$item->level_four] : null : null;
            $item->level_seven = $item->level_seven && $item->level_seven != 0 ? isset($talentData[$item->level_seven]) ? $talentData[$item->level_seven] : null : null;
            $item->level_ten = $item->level_ten && $item->level_ten != 0 ? isset($talentData[$item->level_ten]) ? $talentData[$item->level_ten] : null : null;
            $item->level_thirteen = $item->level_thirteen && $item->level_thirteen != 0 ? isset($talentData[$item->level_thirteen]) ? $talentData[$item->level_thirteen] : null : null;
            $item->level_sixteen = $item->level_sixteen && $item->level_sixteen != 0 ? isset($talentData[$item->level_sixteen]) ? $talentData[$item->level_sixteen] : null : null;
            $item->level_twenty = $item->level_twenty && $item->level_twenty != 0 ? isset($talentData[$item->level_twenty]) ? $talentData[$item->level_twenty] : null : null;

            $item->hero = $type == 'hero' ? $heroData[$typeNumber] : null;

            return $item;
        })->filter()->values();

        return $data;
    }

    public function getLeaderboardRating(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new SeasonInputValidation()],
            'game_type' => ['required', new GameTypeInputValidation()],
            'type' => 'required|in:player,hero,role',
            'groupsize' => ['required', new StackSizeInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation()],
            'region' => 'required|integer',
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'blizz_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }
        $blizz_id = $request['blizz_id'];
        $hero = $request['hero'];
        $role = $this->getMMRTypeValue($request['role']);

        $gameType = $this->getGameTypeFilterValues($request['game_type']);
        $season = $request['season'];
        $region = $request['region'];

        $type = $request['type'];
        $typeNumber = 0;

        if ($type == 'player') {
            $typeNumber = $this->getMMRTypeValue($request['type']);
        } elseif ($type == 'hero') {
            $typeNumber = $hero;
        } elseif ($type == 'role') {
            $typeNumber = $role;
        }

        $groupsize = -1;
        if ($request['groupsize'] == 'Solo') {
            $groupsize = 0;
        } elseif ($request['groupsize'] == 'Duo') {
            $groupsize = 2;
        } elseif ($request['groupsize'] == '3 Players') {
            $groupsize = 3;
        } elseif ($request['groupsize'] == '4 Players') {
            $groupsize = 4;
        } elseif ($request['groupsize'] == '5 Players') {
            $groupsize = 5;
        } elseif ($request['groupsize'] == 'All') {
            $groupsize = 0;
        }

        $table = MasterGamesPlayedData::class;
        if ($request['groupsize'] != 'All') {
            $table = MasterGamesPlayedDataGroups::class;
        }

        $playerData = $table::select('win_leaderboard', 'loss_leaderboard', 'games_played_leaderboard')
            ->where('type_value', $typeNumber)
            ->where('stack_size', $groupsize)
            ->where('season', $this->globalDataService->getDefaultSeason())
            ->where('game_type', $gameType)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->first();

        if (! $playerData || empty($playerData)) {
            return ['rating' => 0, 'games_played' => 0];
        }

        $weeksSinceStart = $this->globalDataService->getWeeksSinceSeasonStart();
        $maxGamesPlayed = $table::select('games_played_leaderboard')
            ->where('type_value', $typeNumber)
            ->where('stack_size', $groupsize)
            ->where('season', $this->globalDataService->getDefaultSeason())
            ->where('game_type', $gameType)
            ->orderByDesc('games_played_leaderboard')
            ->limit($weeksSinceStart)
            ->get()
            ->avg('games_played_leaderboard');

        $gamesPlayedForFormula = $playerData->games_played_leaderboard;

        if (! $maxGamesPlayed) {
            $maxGamesPlayed = 0;
        }
        if ($maxGamesPlayed < $playerData->games_played_leaderboard) {
            $gamesPlayedForFormula = $maxGamesPlayed;
        }

        $mmrTable = '';

        if ($gameType == 1) {
            $mmrTable = MasterMMRDataQM::class;
        } elseif ($gameType == 2) {
            $mmrTable = MasterMMRDataUD::class;
        } elseif ($gameType == 3) {
            $mmrTable = MasterMMRDataHL::class;
        } elseif ($gameType == 4) {
            $mmrTable = MasterMMRDataTL::class;
        } elseif ($gameType == 5) {
            $mmrTable = MasterMMRDataSL::class;
        } elseif ($gameType == 6) {
            $mmrTable = MasterMMRDataAR::class;
        }
        $playerMMR = $mmrTable::select('conservative_rating')
            ->where('type_value', $typeNumber)
            ->where('game_type', $gameType)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->first();

        $winRate = $playerData->games_played_leaderboard > 0 ? ($playerData->win_leaderboard / $playerData->games_played_leaderboard) * 100 : 0;
        $rating = $maxGamesPlayed > 0 ? (50 + ($winRate - 50) * ($gamesPlayedForFormula / $maxGamesPlayed)) + ($playerMMR->conservative_rating / 10) : (50 + ($winRate - 50) * ($gamesPlayedForFormula)) + ($playerMMR->conservative_rating / 10);

        return ['rating' => round($rating, 2), 'games_played' => $playerData->games_played_leaderboard];
    }
}
