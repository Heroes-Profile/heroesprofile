<?php

namespace App\Http\Controllers;
use App\Services\GlobalDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\HeroesDataTalent;
use App\Models\Map;

class SingleMatchController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request, $replayID)
    {
        $data = [
            'replayID' => $replayID,
        ];

        $validator = \Validator::make($data, [
            'replayID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect('/')->withErrors($validator);
        }

        return view('singleMatch')->with(['replayID' => $replayID]);
    }
    
    public function getData(Request $request){
        $data = [
            'replayID' => $request["replayID"],
        ];

        $validator = \Validator::make($data, [
            'replayID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect('/')->withErrors($validator);
        }

        $replayID = $request["replayID"];

        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('battletags', 'battletags.player_id', '=', 'player.battletag')
            ->join('scores', function($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                     ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('talents', function($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                     ->on('talents.battletag', '=', 'player.battletag');
            })
            ->select([
                "replay.game_type",
                "replay.game_date",
                "replay.game_map",
                "replay.game_length",
                "replay.region",

                "player.winner",
                "player.blizz_id",
                "player.party",
                "player.hero",
                "player.team",
                "player.player_conservative_rating",
                "player.player_change",
                "player.hero_conservative_rating",
                "player.hero_change",
                "player.role_conservative_rating",
                "player.role_change",

                "battletags.battletag",
                "battletags.account_level",

                "scores.level", 
                "scores.kills", 
                "scores.assists", 
                "scores.takedowns", 
                "scores.deaths", 
                "scores.highest_kill_streak", 
                "scores.hero_damage", 
                "scores.siege_damage", 
                "scores.structure_damage", 
                "scores.minion_damage", 
                "scores.creep_damage", 
                "scores.summon_damage", 
                "scores.time_cc_enemy_heroes", 
                "scores.healing", 
                "scores.self_healing", 
                "scores.damage_taken", 
                "scores.experience_contribution", 
                "scores.town_kills", 
                "scores.time_spent_dead", 
                "scores.merc_camp_captures", 
                "scores.watch_tower_captures", 
                "scores.meta_experience", 
                "scores.match_award", 
                "scores.protection_allies", 
                "scores.silencing_enemies", 
                "scores.rooting_enemies", 
                "scores.stunning_enemies", 
                "scores.clutch_heals", 
                "scores.escapes", 
                "scores.vengeance", 
                "scores.outnumbered_deaths", 
                "scores.teamfight_escapes", 
                "scores.teamfight_healing", 
                "scores.teamfight_damage_taken", 
                "scores.teamfight_hero_damage", 
                "scores.multikill", 
                "scores.physical_damage", 
                "scores.spell_damage", 
                "scores.regen_globes", 
                "scores.first_to_ten", 
                "scores.time_on_fire",


                "talents.level_one AS level_one",
                "talents.level_four AS level_four",
                "talents.level_seven AS level_seven",
                "talents.level_ten AS level_ten",
                "talents.level_thirteen AS level_thirteen",
                "talents.level_sixteen AS level_sixteen",
                "talents.level_twenty AS level_twenty"
            ])
            ->where("replay.replayID", $replayID)
            //->toSql();
            ->get();

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $groupedData = $result->groupBy('replayID')->map(function ($replayGroup) use ($talentData, $heroData, $maps) {
            $totalSeconds = $replayGroup[0]->game_length - 70;
            $minutes = floor($totalSeconds / 60);
            $seconds = $totalSeconds % 60;
            $timeFormat = "$minutes minutes $seconds seconds";

            $replayDetails = [
                'region' => $replayGroup[0]->region,
                'game_type' => $this->globalDataService->getGameTypeIDtoString()[$replayGroup[0]->game_type]["name"],
                'game_date' => $replayGroup[0]->game_date,
                'game_map' => $maps[$replayGroup[0]->game_map]["name"],
                'game_length' => $timeFormat,
                'players' => []
            ];

            $replayDetails['players'] = $replayGroup->groupBy('team')->map(function ($teamGroup) use ($heroData) {
                return $teamGroup->map(function ($row) use ($heroData) {
                    return [
                        'battletag' => explode('#', $row->battletag)[0],
                        'blizz_id' => $row->blizz_id,
                        'winner' => $row->winner,
                        'team' => $row->team,
                        'party' => $row->party,
                        'hero' => $heroData[$row->hero],
                        'account_level' => $row->account_level,
                        'player_conservative_rating' => $row->player_conservative_rating,
                        'player_change' => $row->player_change,
                        'hero_conservative_rating' => $row->hero_conservative_rating,
                        'hero_change' => $row->hero_change,
                        'role_conservative_rating' => $row->role_conservative_rating,
                        'role_change' => $row->role_change,
                        // ... (other player data)
                        'score' => [
                            'level' => $row->level,
                            // ... (other score data)
                        ],
                        'talents' => [
                            'level_one' => $row->level_one,
                            // ... (other talents data)
                        ],
                    ];
                });
            })->toArray();

            return $replayDetails;
        });

        $groupedData = array_values($groupedData->toArray());

        return $groupedData[0];
    }


}
