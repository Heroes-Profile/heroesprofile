<?php

namespace App\Http\Controllers\Esports\NGS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\NGSSeasonInputValidation;

use App\Models\NGS\Team;
use App\Models\NGS\Replay;

use App\Models\Map;

class NGSSingleDivisionController extends Controller
{
    public function show(Request $request, $division){
        $defaultseason = Team::max('season');

        return view('Esports.NGS.singleDivision')  
            ->with([
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'division' => $division,
                //'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);

   
    }

    public function getSingleDivisionData(Request $request){
        $season = (new NGSSeasonInputValidation())->passes('season', $request["season"]);
        $division = $request["division"];

        $results = Replay::join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
        ->join('heroesprofile_ngs.scores', function($join) {
            $join->on('heroesprofile_ngs.scores.replayID', '=', 'heroesprofile_ngs.replay.replayID')
                 ->on('heroesprofile_ngs.scores.battletag', '=', 'heroesprofile_ngs.player.battletag');
        })
        ->join('heroesprofile_ngs.teams', 'heroesprofile_ngs.teams.team_id', '=', 'heroesprofile_ngs.player.team_name')
        ->select([
            'replay.replayID',
            'hero',
            'game_date',
            'game_map',
            'game_length',
            'round',
            'game',
            'team_0_name',
            'team_1_name',
            'heroesprofile_ngs.teams.team_name',
            'image',
            'winner',
            'vengeance',
            'escapes',
            'hero_damage',
            'siege_damage',
            'takedowns',
            'kills',
            'assists',
            'healing',
            'self_healing',
            'deaths',
            'time_spent_dead',
            ])
        ->where("heroesprofile_ngs.teams.season", $season)
        ->where("heroesprofile_ngs.teams.division", $division)
        ->get();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');


        $matches = $results->groupBy('replayID')->map(function ($group) use ($heroData, $maps) {
            return [
                'replayID' => $group[0]['replayID'],
                'game_date' => $group[0]['game_date'],
                'game_map' => $maps[$group[0]['game_map']],
                'game' => $group[0]['game'],
                'round' => $group[0]['round'],
                'team_0_name' => $group[0]['team_0_name'],
                'team_1_name' => $group[0]['team_1_name'],
                'winner' => 1,
                'heroes' => [
                    0 => $group[0] && $group[0]['hero'] ? ["hero" => $heroData[$group[0]['hero']]] : null,
                    1 => $group[1] && $group[1]['hero'] ? ["hero" => $heroData[$group[1]['hero']]] : null,
                    2 => $group[2] && $group[2]['hero'] ? ["hero" => $heroData[$group[2]['hero']]] : null,
                    3 => $group[3] && $group[3]['hero'] ? ["hero" => $heroData[$group[3]['hero']]] : null,
                    4 => $group[4] && $group[4]['hero'] ? ["hero" => $heroData[$group[4]['hero']]] : null,
                    5 => $group[5] && $group[5]['hero'] ? ["hero" => $heroData[$group[5]['hero']]] : null,
                    6 => $group[6] && $group[6]['hero'] ? ["hero" => $heroData[$group[6]['hero']]] : null,
                    7 => $group[7] && $group[7]['hero'] ? ["hero" => $heroData[$group[7]['hero']]] : null,
                    8 => $group[8] && $group[8]['hero'] ? ["hero" => $heroData[$group[8]['hero']]] : null,
                    9 => $group[9] && $group[9]['hero'] ? ["hero" => $heroData[$group[9]['hero']]] : null,
                ]
            ];
        })->sortByDesc('game_date')->take(10)->values()->all();


        $teams = $results->groupBy('team_name')->map(function ($group) {
            $wins = $group->sum(function ($item) {
                return $item['winner'] == 1 ? 1 : 0;
            });

            $losses = $group->sum(function ($item) {
                return $item['winner'] == 0 ? 1 : 0;
            });

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
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'image' => $image,
            ];
        })->values()->all();


        $heroes = $results->groupBy('hero')->map(function ($group) use ($heroData) {
            $wins = $group->sum(function ($item) {
                return $item['winner'] == 1 ? 1 : 0;
            });

            $losses = $group->sum(function ($item) {
                return $item['winner'] == 0 ? 1 : 0;
            });

            $gamesPlayed = $wins + $losses;

            return [
                'hero_id' => $group[0]['hero'],
                'hero' => $heroData[$group[0]['hero']],
                'name' => $heroData[$group[0]['hero']]["name"],
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'games_played' => $gamesPlayed,
            ];
        });

        $filteredHeroes = $heroes->filter(function ($hero) {
            return $hero['games_played'] >= 5;
        });

        $heroTopthreeHighestWinRate = $filteredHeroes->sortByDesc('win_rate')->take(3);
        $heroTopthreeLowestWinRate = $filteredHeroes->sortBy('win_rate')->take(3);
        $heroTopthreeMostPlayed = $filteredHeroes->sortByDesc('games_played')->take(3);




        $mapData = $results->groupBy('game_map')->map(function ($group) use ($maps) {
            $wins = $group->sum(function ($item) {
                return $item['winner'] == 1 ? 1 : 0;
            });

            $losses = $group->sum(function ($item) {
                return $item['winner'] == 0 ? 1 : 0;
            });

            $gamesPlayed = $wins + $losses;

            return [
                'map_id' => $group[0]['game_map'],
                'map' => $maps[$group[0]['game_map']],
                'game_map' => $maps[$group[0]['game_map']],
                'name' => $maps[$group[0]['game_map']]["name"],
                'wins' => $wins,
                'losses' => $losses,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
                'games_played' => $gamesPlayed,
            ];
        });

        $filteredMaps = $mapData->filter(function ($map) {
            return $map['games_played'] >= 5;
        });

        $mapTopthreeHighestWinRate = $filteredMaps->sortByDesc('win_rate')->take(3);
        $mapTopthreeLowestWinRate = $filteredMaps->sortBy('win_rate')->take(3);
        $mapTopthreeMostPlayed = $filteredMaps->sortByDesc('games_played')->take(3);




        $totalSeconds = $results->sum('time_spent_dead');
        $days = floor($totalSeconds / (3600 * 24));
        $hours = floor(($totalSeconds % (3600 * 24)) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $time_spent_dead = "{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds";







        return [
            "vengeances" => $results->sum('vengeance'),
            "escapes" => $results->sum('escapes'),
            "hero_damage" => round($results->avg('hero_damage')),
            "siege_damage" => round($results->avg('siege_damage')),
            "healing" => round($results->avg('healing') + $results->avg('self_healing')),
            "time_spent_dead" => $time_spent_dead,
            "takedowns" => $results->sum('takedowns'),
            "kills" => $results->sum('kills'),
            "assists" => $results->sum('assists'),
            "total_games" => $results->count() / 10,
            "length_to_30" => round((($results->avg('game_length') / 60) / 30) * 100),
            "matches" => $matches,
            "teams" => $teams,
            "heroes" => $heroes->sortBy('name')->values(),
            "hero_top_three_most_played" => $heroTopthreeMostPlayed->values(),
            "hero_top_three_highest_win_rate" => $heroTopthreeHighestWinRate->values(),
            "hero_top_three_lowest_win_rate" => $heroTopthreeLowestWinRate->values(),
            "maps" => $mapData->sortBy('name')->values(),
            "map_top_three_most_played" => $mapTopthreeMostPlayed->values(),
            "map_top_three_highest_win_rate" => $mapTopthreeHighestWinRate->values(),
            "map_top_three_lowest_win_rate" => $mapTopthreeLowestWinRate->values(),
        ];
    }
}
