<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Rules\BattletagInputProhibitCharacters;

use App\Models\Battletag;
use App\Models\Replay;
use App\Models\Player;
use App\Models\Map;
use App\Services\GlobalDataService;

class BattletagSearchController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){

        return view('searchedBattletagHolding')->with(['userinput' => $request["userinput"], 'type' => $request["type"]]);
    }

    public function battletagSearch(Request $request){
        $request->validate(['userinput' => ['required', 'string', new BattletagInputProhibitCharacters],]);


        $data = $this->searchForSpecificBattletag($request["userinput"]);

        if(!$data->isEmpty()){
            return $data;
        }

        $data = $this->searchForPartialBattletag($request["userinput"]);

        return $data;
    }

    private function searchForSpecificBattletag($input){
        $data = Battletag::select("blizz_id", "battletag", "region", "latest_game")->where("battletag", $input)->get();
        return $data;
    }

    private function searchForPartialBattletag($input){
        $data = Battletag::select("blizz_id", "battletag", "region", "latest_game")->where("battletag", "LIKE", $input . "%")->get();


        $uniqueData = $data->groupBy(function ($item) {
            return $item->blizz_id . '-' . $item->region;
        })->map(function ($group) {
            return $group->sortByDesc('latest_game')->first();
        })->values();
        $uniqueData = $uniqueData->sortByDesc('latest_game')->values()->take(50);


        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $regions = $this->globalDataService->getRegionIDtoString();

        foreach ($uniqueData as $item) {
            $blizzId = $item->blizz_id;
            $battletag = $item->battletag;
            $battletagShort = explode('#', $item->battletag)[0];
            $region = $item->region;
            $regionName = $regions[$item->region];
            $latestGame = $item->latest_game;

            $totalGamesPlayed = $this->getTotalGamesPlayedForPlayer($blizzId, $region);
            $latestMap = $this->getLatestMapPlayedForPlayer($blizzId, $region);
            $latestHero = $this->getLatestHeroPlayedForPlayer($blizzId, $region); 

            $item->totalGamesPlayed = $totalGamesPlayed;
            $item->latestMap = $maps[$latestMap];
            $item->latestHero = $heroData[$latestHero];

            $item->battletagShort = $battletagShort;
            $item->regionName = $regionName;

        }

        return $uniqueData->sortByDesc('totalGamesPlayed')->values();
    }

    private function getTotalGamesPlayedForPlayer($blizzId, $region, $gameType = null){
        $count = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                  ->where('region', $region);
        })
        ->when($gameType, function ($query, $gameType) {
            return $query->where('game_type', $gameType);
        })
        ->where('game_type', '<>', 0) // Exclude custom games
        ->count();

        return $count;
    }

    private function getLatestMapPlayedForPlayer($blizzId, $region, $gameType = null){
        $lastReplayMap = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                  ->where('region', $region);
        })
        ->where('game_type', '<>', 0) // Exclude custom games
        ->orderBy('game_date', 'desc')
        ->value('replay.game_map');

        return $lastReplayMap;
    }

    private function getLatestHeroPlayedForPlayer($blizzId, $region, $gameType = null){
        $latestHero = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                  ->where('region', $region)
                  ->orderBy('game_date', 'desc');
        })
        ->with('players') // Load the players relationship
        ->orderBy('game_date', 'desc')
        ->limit(1)
        ->get();

        if ($latestHero->count() > 0) {
            $latestHeroValue = $latestHero[0]->players[0]->hero;
        } else {
            $latestHeroValue = null;
        }

        return $latestHeroValue;
    }


}