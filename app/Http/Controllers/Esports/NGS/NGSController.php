<?php

namespace App\Http\Controllers\Esports\NGS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Rules\NGSSeasonInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\BattletagInputProhibitCharacters;

use App\Models\NGS\Team;
use App\Models\NGS\Standing;
use App\Models\NGS\Replay;
use App\Models\NGS\Battletag;
use App\Models\NGS\ReplayBan;

use App\Models\Map;
use App\Models\HeroesDataTalent;

class NGSController extends Controller
{
    public function show(Request $request){
        $defaultseason = Team::max('season');

        return view('Esports.NGS.ngsMain')  
            ->with([
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);

   
    }

    public function getStandingData(Request $request){
        //return response()->json($request->all());

        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];

        $results = Standing::where("season", $season)
            ->when(!is_null($division), function ($query) use ($division) {
                return $query->where('division', $division);
            })
            ->get();

        $groupedResults = $results->groupBy('division');

        $sortedGroupedResults = $groupedResults->map(function ($group) {
            return $group->sortByDesc('points');
        });
        return $sortedGroupedResults;
    }

    public function getDivisionData(Request $request){
        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);

        $results = Replay::where('season', $season)
            ->select('division_0')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('division_0')
            ->get();


        return $results;
    }

    public function getTeamsData(Request $request){
        //return response()->json($request->all());

        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];

        $results = Replay::join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
            ->join('heroesprofile_ngs.teams', 'heroesprofile_ngs.teams.team_id', '=', 'heroesprofile_ngs.player.team_name')
            ->where('heroesprofile_ngs.replay.season', $season)
            ->when(!is_null($division), function ($query) use ($division) {
                return $query->where('heroesprofile_ngs.teams.division', $division);
            })
            ->select('heroesprofile_ngs.teams.team_name', 'heroesprofile_ngs.player.winner', 'heroesprofile_ngs.teams.image', 'heroesprofile_ngs.teams.division')
            ->get();

        $groupedResults = $results->groupBy('team_name')->map(function ($group) {
            $wins = $group->sum('winner');
            $losses = count($group) - $wins;
            $gamesPlayed = $wins + $losses;

            $image = "";
            if (strpos($group[0]['image'], 'https://s3.amazonaws.com/ngs-image-storage/') !== false) {
                $image = explode("https://s3.amazonaws.com/ngs-image-storage/", $group[0]['image']);
                $image = "https://s3.amazonaws.com/ngs-image-storage/" . urlencode($image[1]);


                if(strpos($image, 'undefined') !== false){
                    $image = "/images/NGS/no-image-clipped.png";
                }
            }else{
                $image = $group[0]['image'];
            }

            return [
                'team_name' => $group[0]['team_name'],
                'division' => $group[0]['division'],
                'icon_url' => $image,
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
            ];
        })->sortBy('team_name')->values()->all();
        return $groupedResults;
    }

    public function playerSearch(Request $request){
        //return response()->json($request->all());
        $request->validate(['userinput' => ['required', 'string', new BattletagInputProhibitCharacters],]);
        $input = $request["userinput"];
        $daa = null;
        if (strpos($input, '#') !== false) {
            $data = Battletag::select("blizz_id", "battletag", "region")
                ->where("battletag", $input)
                ->get();
        } else {
            $data = Battletag::select("blizz_id", "battletag", "region")
                ->where("battletag", "LIKE", $input . "#%")
                ->get();
        }

        $returnData = [];
        $counter = 0;
        $uniqueBlizzIDRegion = [];
        foreach($data as $row){

          if(array_key_exists($row["blizz_id"] . "|" . $row["region"], $uniqueBlizzIDRegion)){
               if($row["latest_game"] > $uniqueBlizzIDRegion[$row["blizz_id"] . "|" . $row["region"]]){
                    $returnData[$row["blizz_id"] . "|" . $row["region"]] = $row;
                }
            }else{
                $uniqueBlizzIDRegion[$row["blizz_id"] . "|" . $row["region"]] = $row["latest_game"];
                $returnData[$row["blizz_id"] . "|" . $row["region"]] = $row;
                $counter++;
            }
        }

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $regions = $this->globalDataService->getRegionIDtoString();

        foreach ($returnData as $item) {
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
        usort($returnData, function ($a, $b) {
            return $b->totalGamesPlayed - $a->totalGamesPlayed;
        });

        return array_values($returnData);
    }

    private function getTotalGamesPlayedForPlayer($blizzId, $region){
        $count = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                  ->where('region', $region);
        })
        ->count();

        return $count;
    }

    private function getLatestMapPlayedForPlayer($blizzId, $region){
        $lastReplayMap = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                  ->where('region', $region);
        })
        ->orderBy('game_date', 'desc')
        ->value('replay.game_map');

        return $lastReplayMap;
    }

    private function getLatestHeroPlayedForPlayer($blizzId, $region){
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

    public function getRecentMatchData(Request $request){
        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];

        $results = Replay::select("heroesprofile_ngs.replay.replayID", "hero", "game_map", "team_0_name", "team_1_name", "round", "game", "game_date")
            ->join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
            ->orderBy("game_date", "DESC")
            ->where("season", $season)
            ->when(!is_null($division), function ($query) use ($division) {
                return $query->where('heroesprofile_ngs.teams.division', $division);
            })
            ->get();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $groupedResults = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps){
            return [
                'replayID' => $group[0]['replayID'],
                'game_date' => $group[0]['game_date'],
                'game_map' => $maps[$group[0]['game_map']],
                'game' => $group[0]['game'],
                'round' => $group[0]['round'],
                'team_0_name' => $group[0]['team_0_name'],
                'team_1_name' => $group[0]['team_1_name'],
                'heroes' => [
                    0 => $group[0] && $group[0]['hero'] ? $heroData[$group[0]['hero']] : null,
                    1 => $group[1] && $group[1]['hero'] ? $heroData[$group[1]['hero']] : null,
                    2 => $group[2] && $group[2]['hero'] ? $heroData[$group[2]['hero']] : null,
                    3 => $group[3] && $group[3]['hero'] ? $heroData[$group[3]['hero']] : null,
                    4 => $group[4] && $group[4]['hero'] ? $heroData[$group[4]['hero']] : null,
                    5 => $group[5] && $group[5]['hero'] ? $heroData[$group[5]['hero']] : null,
                    6 => $group[6] && $group[6]['hero'] ? $heroData[$group[6]['hero']] : null,
                    7 => $group[7] && $group[7]['hero'] ? $heroData[$group[7]['hero']] : null,
                    8 => $group[8] && $group[8]['hero'] ? $heroData[$group[8]['hero']] : null,
                    9 => $group[9] && $group[9]['hero'] ? $heroData[$group[9]['hero']] : null,
                ]
            ];
        })->values()->all();



        return $groupedResults;
    }

    public function getOverallHeroStats(Request $request){
        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];


        $heroResults = Replay::select("replay.replayID", "hero", "winner")
            ->join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
            ->where("season", $season)
            ->when(!is_null($division), function ($query) use ($division) {
                return $query->where('heroesprofile_ngs.teams.division', $division);
            })
            ->get();



        $banResults = ReplayBan::whereIn("replayID", $heroResults->pluck('replayID')->toArray())
            ->groupBy("hero")
            ->select("hero")
            ->selectRaw("COUNT(*) as ban_count")
            ->get();

        $totalBanCount = $banResults->sum('ban_count') / 6;

        $banResults = $banResults->pluck('ban_count', 'hero')->toArray();;

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $totalGames = $heroResults->count() / 10;

        $groupedResults = $heroResults->groupBy('hero')->map(function ($group) use ($banResults, $totalBanCount, $heroData, $totalGames){
                $wins = $group->sum('winner');
                $losses = $group->count() - $wins;
                $gamesPlayed = $wins + $losses;

                $bans = array_key_exists($group->first()->hero, $banResults) && $banResults[$group->first()->hero] > 0 ? $banResults[$group->first()->hero] : 0;

                return [
                    'hero' => $heroData[$group->first()->hero],
                    'hero_id' => $group->first()->hero,
                    'wins' => $wins,
                    'losses' => $losses,
                    'games_played' => $gamesPlayed,
                    'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                    'ban_rate' => $bans > 0 ? round(($bans / $totalBanCount) * 100, 2) : 0,
                    'bans' => $bans,
                    'popularity' => round((($gamesPlayed + $bans) / $totalGames) * 100, 2),
                ];
            })
            ->sortByDesc('win_rate')
            ->values()
            ->all();
        return $groupedResults;
    }

    public function getOverallTalentStats(Request $request){
        //return response()->json($request->all());

        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];
        $hero = (new HeroInputValidation())->passes('hero', $request["hero"]);

        $result = DB::table('heroesprofile_ngs.replay')
            ->join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
            ->join('heroesprofile_ngs.talents', function($join) {
                $join->on('heroesprofile_ngs.talents.replayID', '=', 'heroesprofile_ngs.replay.replayID')
                     ->on('heroesprofile_ngs.talents.battletag', '=', 'heroesprofile_ngs.player.battletag');
            })
            ->select([
                "heroesprofile_ngs.player.winner AS winner",
                "heroesprofile_ngs.player.hero AS hero",
                "heroesprofile_ngs.talents.level_one AS level_one",
                "heroesprofile_ngs.talents.level_four AS level_four",
                "heroesprofile_ngs.talents.level_seven AS level_seven",
                "heroesprofile_ngs.talents.level_ten AS level_ten",
                "heroesprofile_ngs.talents.level_thirteen AS level_thirteen",
                "heroesprofile_ngs.talents.level_sixteen AS level_sixteen",
                "heroesprofile_ngs.talents.level_twenty AS level_twenty"
            ])
            ->where("season", $season)
            ->when(!is_null($division), function ($query) use ($division) {
                return $query->where('heroesprofile_ngs.teams.division', $division);
            })
            ->where("hero", $hero)
            //->toSql();
            ->get();
        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        return [
            "talentData" => $this->getHeroTalentData($result, $talentData),
            "buildData" => $this->getHeroTalentBuildData($result, $heroData[$hero], $talentData),
        ];
        
    }

    private function getHeroTalentData($result, $talentData){
        $returnData = [];
        $levelKeys = ["level_one", "level_four", "level_seven", "level_ten", "level_thirteen", "level_sixteen", "level_twenty"];

        $resultCollection = collect($result);



        $resultCollection->each(function ($data) use (&$returnData, $levelKeys, $talentData) {
            foreach ($levelKeys as $levelKey) {
                if ($data->$levelKey == 0 || !$talentData->has($data->$levelKey)) {
                    continue;
                }

                $key = $data->$levelKey;

                if (!isset($returnData[$key])) {
                    $returnData[$key] = ["wins" => 0, "losses" => 0];
                }

                $returnData[$key][$data->winner == 1 ? "wins" : "losses"]++;
                $returnData[$key]["talent"] = $talentData[$data->$levelKey];
            }
        });

        $formattedData = [];

        $levelTotals = [];

        foreach($returnData as $data){
            if(!array_key_exists($data["talent"]["level"], $levelTotals)){
                $levelTotals[$data["talent"]["level"]] = 0;
            }
            $levelTotals[$data["talent"]["level"]] += $data["wins"] + $data["losses"];
        }

        foreach($returnData as $data){
            $level = $data["talent"]["level"];
            $sort = ($data["talent"]["sort"] - 1);

            if(!array_key_exists($level, $formattedData)){
                $formattedData[$level] = [];
            }

            if(!array_key_exists($sort, $formattedData[$level])){
                $formattedData[$level][$sort] = [];
            }



            $formattedData[$level][$sort] = [
                        'win_rate' =>($data["wins"] + $data["losses"]) > 0 ? round(($data["wins"] / ($data["wins"] + $data["losses"])) * 100, 2): 0,
                        'wins' => $data["wins"],
                        'losses' => $data["losses"],
                        'sort' => $data["talent"]["sort"],
                        'popularity' =>  round((($data["losses"] + $data["wins"]) / $levelTotals[$data["talent"]["level"]]) * 100, 2),
                        'games_played' =>  $data["wins"] + $data["losses"],
                        'talentInfo' => $data["talent"]
            ];

        }

   
        return $formattedData;
    }

    private function getHeroTalentBuildData($result, $hero, $talentData){
        $returnData = [];

        foreach($result as $replay){
            $level_one = $replay->level_one;
            $level_four = $replay->level_four;
            $level_seven = $replay->level_seven;
            $level_ten = $replay->level_ten;
            $level_thirteen = $replay->level_thirteen;
            $level_sixteen = $replay->level_sixteen;
            $level_twenty = $replay->level_twenty;

            if($level_one == 0 || $level_four == 0 || $level_seven == 0 || $level_ten == 0 || $level_thirteen == 0 || $level_sixteen == 0 || $level_twenty == 0){
                continue;
            }else if(!$talentData->has($level_one ) || !$talentData->has($level_four ) || !$talentData->has($level_seven ) || !$talentData->has($level_ten ) || !$talentData->has($level_thirteen ) || !$talentData->has($level_sixteen ) || !$talentData->has($level_twenty)){
                continue;
            }
            $key = $level_one . "|" . $level_four . "|" . $level_seven . "|" . $level_ten . "|" . $level_thirteen . "|" . $level_sixteen . "|" . $level_twenty;
            if(!array_key_exists($key, $returnData)){
                $returnData[$key] = [];
                $returnData[$key]["wins"] = 0;
                $returnData[$key]["losses"] = 0;
                $returnData[$key]["games_played"] = 0;

                $returnData[$key]["level_one"] = $replay->level_one;
                $returnData[$key]["level_four"] = $replay->level_four;
                $returnData[$key]["level_seven"] = $replay->level_seven;
                $returnData[$key]["level_ten"] = $replay->level_ten;
                $returnData[$key]["level_thirteen"] = $replay->level_thirteen;
                $returnData[$key]["level_sixteen"] = $replay->level_sixteen;
                $returnData[$key]["level_twenty"] = $replay->level_twenty;
            }

            if($replay->winner == 1){
                $returnData[$key]["wins"]++;
            }else{
                $returnData[$key]["losses"]++;
            }
            $returnData[$key]["games_played"]++;

        }

        usort($returnData, function ($a, $b) {
            return $b['games_played'] - $a['games_played'];
        });

        $returnData = array_slice($returnData, 0, 7);

        foreach($returnData as &$data){
            foreach($result as $replay){
                $level_one = $replay->level_one;
                $level_four = $replay->level_four;
                $level_seven = $replay->level_seven;
                $level_ten = $replay->level_ten;
                $level_thirteen = $replay->level_thirteen;
                $level_sixteen = $replay->level_sixteen;
                $level_twenty = $replay->level_twenty;

                if($data["level_one"] != $level_one || $data["level_four"] != $level_four || $data["level_seven"] != $level_seven || $data["level_ten"] != $level_ten || $level_twenty != 0){
                    continue;
                }

                if($replay->winner == 1){
                    $data["wins"]++;

                }else{
                    $data["losses"]++;
                }
                $data["games_played"]++;
            }
        }
        foreach($returnData as &$data){
            $data["level_one"] = $talentData[$data["level_one"]];
            $data["level_four"] = $talentData[$data["level_four"]];
            $data["level_seven"] = $talentData[$data["level_seven"]];
            $data["level_ten"] = $talentData[$data["level_ten"]];
            $data["level_thirteen"] = $talentData[$data["level_thirteen"]];
            $data["level_sixteen"] = $talentData[$data["level_sixteen"]];
            $data["level_twenty"] = $talentData[$data["level_twenty"]];
            $data["win_rate"] = round(($data["wins"] / $data["games_played"]) * 100, 2);
            $data["hero"] = $hero;
        }

        return $returnData;
    }

}
