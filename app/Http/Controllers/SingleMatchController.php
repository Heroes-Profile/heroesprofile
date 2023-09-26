<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Models\ReplayBan;
use App\Models\ReplayDraftOrder;
use App\Models\ReplayExperienceBreakdownBlob;

class SingleMatchController extends Controller
{
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

        $groupedData = $result->groupBy('replayID')->map(function ($replayGroup) use ($talentData, $heroData, $maps, $replayID) {
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
                'players' => [],
                'replay_bans' => $this->getReplayBans($replayID, $heroData),
                'draft_order' => $this->getDraftOrder($replayID, $heroData),
                'experience_breakdown' => $this->getExperienceBreakdown($replayID),
            ];

            $replayDetails['players'] = $replayGroup->groupBy('team')->map(function ($teamGroup) use ($heroData, $talentData) {
                return $teamGroup->map(function ($row) use ($heroData, $talentData) {
                    return [
                        'battletag' => explode('#', $row->battletag)[0],
                        'blizz_id' => $row->blizz_id,
                        'winner' => $row->winner,
                        'team' => $row->team,
                        'party' => $row->party,
                        'hero' => $heroData[$row->hero],
                        'account_level' => $row->account_level,
                        'player_conservative_rating' => $row->player_conservative_rating,
                        'player_mmr' => round(1800 + 40 * $row->player_conservative_rating),
                        'player_change' => round($row->player_change, 2),
                        'hero_conservative_rating' => $row->hero_conservative_rating,
                        'hero_mmr' => round(1800 + 40 * $row->hero_conservative_rating),
                        'hero_change' => round($row->hero_change, 2),
                        'role_conservative_rating' => $row->role_conservative_rating,
                        'role_mmr' => round(1800 + 40 * $row->role_conservative_rating),
                        'role_change' => round($row->role_change, 2),
                        ///Need to work on Heroes Profile Score
                        'score' => [
                            'level' => $row->level,
                            'takedowns' => $row->takedowns,
                            'kills' => $row->kills,
                            'deaths' => $row->deaths,
                            'siege_damage' => $row->siege_damage,
                            'hero_damage' => $row->hero_damage,
                            'assists' => $row->assists,
                            'highest_kill_streak' => $row->highest_kill_streak,
                            'structure_damage' => $row->structure_damage,
                            'minion_damage' => $row->minion_damage,
                            'creep_damage' => $row->creep_damage,
                            'summon_damage' => $row->summon_damage,
                            'time_cc_enemy_heroes' => $row->time_cc_enemy_heroes,
                            'healing' => $row->healing + $row->self_healing,
                            'damage_taken' => $row->damage_taken,
                            'experience_contribution' => $row->experience_contribution,
                            'town_kills' => $row->town_kills,
                            'time_spent_dead' => $row->time_spent_dead,
                            'merc_camp_captures' => $row->merc_camp_captures,
                            'watch_tower_captures' => $row->watch_tower_captures,
                            'meta_experience' => $row->meta_experience,
                            'match_award' => $row->match_award,
                            'protection_allies' => $row->protection_allies,
                            'silencing_enemies' => $row->silencing_enemies,
                            'rooting_enemies' => $row->rooting_enemies,
                            'stunning_enemies' => $row->stunning_enemies,
                            'clutch_heals' => $row->clutch_heals,
                            'escapes' => $row->escapes,
                            'vengeance' => $row->vengeance,
                            'outnumbered_deaths' => $row->outnumbered_deaths,
                            'teamfight_escapes' => $row->teamfight_escapes,
                            'teamfight_healing' => $row->teamfight_healing,
                            'teamfight_damage_taken' => $row->teamfight_damage_taken,
                            'teamfight_hero_damage' => $row->teamfight_hero_damage,
                            'multikill' => $row->multikill,
                            'physical_damage' => $row->physical_damage,
                            'spell_damage' => $row->spell_damage,
                            'regen_globes' => $row->regen_globes,
                            'first_to_ten' => $row->first_to_ten,
                            'time_on_fire' => $row->time_on_fire,
                        ],
                        'talents' => [
                            'level_one' => $row->level_one ? $talentData[$row->level_one] : null,
                            'level_four' => $row->level_four ? $talentData[$row->level_four] : null,
                            'level_seven' => $row->level_seven ? $talentData[$row->level_seven] : null,
                            'level_ten' => $row->level_ten ? $talentData[$row->level_ten] : null,
                            'level_thirteen' => $row->level_thirteen ? $talentData[$row->level_thirteen] : null,
                            'level_sixteen' => $row->level_sixteen ? $talentData[$row->level_sixteen] : null,
                            'level_twenty' => $row->level_twenty ? $talentData[$row->level_twenty] : null,
                        ],
                    ];
                });
            })->toArray();

            return $replayDetails;
        });

        $groupedData = array_values($groupedData->toArray());

        return $groupedData[0];
    }

    private function getReplayBans($replayID, $heroData){
        return ReplayBan::select("team", "hero")
                        ->where("replayID", $replayID)
                        ->get()
                        ->groupBy('team')
                        ->map(function($teamGroup) use ($heroData) {
                            return $teamGroup->map(function($replayBan) use ($heroData) {
                                $replayBan->hero = $heroData[$replayBan->hero] ?? $replayBan->hero;
                                return $replayBan;
                            });
                        });
    }


    private function getDraftOrder($replayID, $heroData){
        return ReplayDraftOrder::where("replayID", $replayID)->get();
    }

    private function getExperienceBreakdown($replayID){
        return ReplayExperienceBreakdownBlob::where("replayID", $replayID)->get();
    }

}
