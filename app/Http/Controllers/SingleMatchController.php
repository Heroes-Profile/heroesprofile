<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            "player.hero_level",
            "player.mastery_taunt",

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
            $region = $replayGroup[0]->region;
            $replayDetails = [
                'region' => $replayGroup[0]->region,
                'game_type' => $this->globalDataService->getGameTypeIDtoString()[$replayGroup[0]->game_type]["name"],
                'game_date' => $replayGroup[0]->game_date,
                'game_map' => $maps[$replayGroup[0]->game_map]["name"],
                'game_length' => $timeFormat,
                'winner' => ($replayGroup[0]->team == 0 && $replayGroup[0]->winner == 1) ? 0 : 1,
                'players' => [],
                'replay_bans' => $this->getReplayBans($replayID, $heroData),
                'draft_order' => $this->getDraftOrder($replayID, $heroData),
                'experience_breakdown' => $this->getExperienceBreakdown($replayID),
            ];

            $replayDetails['players'] = $replayGroup->groupBy('team')->map(function ($teamGroup) use ($heroData, $talentData, $region) {
                return $teamGroup->map(function ($row) use ($heroData, $talentData, $region) {
                    $hero_level_calculated = $row->hero_level;

                    if($row->mastery_taunt == 1){
                        $hero_level_calculated = "15-25";
                    }else if($row->mastery_taunt == 2){
                        $hero_level_calculated = "25-50";
                    }else if($row->mastery_taunt == 3){
                        $hero_level_calculated = "50-75";
                    }else if($row->mastery_taunt == 4){
                        $hero_level_calculated = "75-100";
                    }else if($row->mastery_taunt == 5){
                        $hero_level_calculated = "100+";
                    }
                    return [
                        'region' => $region,
                        'battletag' => explode('#', $row->battletag)[0],
                        'blizz_id' => $row->blizz_id,
                        'winner' => $row->winner,
                        'team' => $row->team,
                        'party' => $row->party,
                        'hero' => $heroData[$row->hero],
                        'hero_level' => $hero_level_calculated,
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
                            'healing' => $row->healing,
                            'self_healing' => $row->self_healing,
                            'total_healing' => $row->healing + $row->self_healing,
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
                            'level_twenty' => $row->level_twenty ? $talentData[$row->level_twenty] : null, ],
                    ];
                });
            })->toArray();
            $replayDetails['players'] = $this->calculateHPScore($replayDetails['players']);
            return $replayDetails;
        });
        $groupedData = array_values($groupedData->toArray());
        return $groupedData[0];
    }

    private function calculateHPScore($playerArray){
        $killsArray = [];
        $assistsArray = [];
        $takedownsArray = [];
        $deathsArray = [];
        $timeSpentDeadArray = [];
        $experienceArray = [];
        $siegeArray = [];
        $heroDamageArray = [];
        $healingArray = [];
        $selfHealingArray = [];
        $mercsArray = [];
        $watchTowerArray = [];
        $damageTakenArray = [];

        $counter = 0;

        foreach ($playerArray[0] as $player => $playerData) {
            $killsArray[$counter] = $playerData["score"]["kills"];
            $assistsArray[$counter] = $playerData["score"]["assists"];
            $takedownsArray[$counter] = $playerData["score"]["takedowns"];
            $deathsArray[$counter] = $playerData["score"]["deaths"];
            $timeSpentDeadArray[$counter] = $playerData["score"]["time_spent_dead"];
            $experienceArray[$counter] = $playerData["score"]["experience_contribution"];
            $siegeArray[$counter] = $playerData["score"]["siege_damage"];
            $heroDamageArray[$counter] = $playerData["score"]["hero_damage"];
            $healingArray[$counter] = $playerData["score"]["healing"];
            $selfHealingArray[$counter] = $playerData["score"]["self_healing"];
            $mercsArray[$counter] = $playerData["score"]["merc_camp_captures"];
            $watchTowerArray[$counter] = $playerData["score"]["watch_tower_captures"];
            $damageTakenArray[$counter] = $playerData["score"]["damage_taken"];

            $counter++;
        }

        foreach ($playerArray[1] as $player => $playerData) {
            $killsArray[$counter] = $playerData["score"]["kills"];
            $assistsArray[$counter] = $playerData["score"]["assists"];
            $takedownsArray[$counter] = $playerData["score"]["takedowns"];
            $deathsArray[$counter] = $playerData["score"]["deaths"];
            $timeSpentDeadArray[$counter] = $playerData["score"]["time_spent_dead"];
            $experienceArray[$counter] = $playerData["score"]["experience_contribution"];
            $siegeArray[$counter] = $playerData["score"]["siege_damage"];
            $heroDamageArray[$counter] = $playerData["score"]["hero_damage"];
            $healingArray[$counter] = $playerData["score"]["healing"];
            $selfHealingArray[$counter] = $playerData["score"]["self_healing"];
            $mercsArray[$counter] = $playerData["score"]["merc_camp_captures"];
            $watchTowerArray[$counter] = $playerData["score"]["watch_tower_captures"];
            $damageTakenArray[$counter] = $playerData["score"]["damage_taken"];

            $counter++;
        }

        $killsArray = array_unique($killsArray);
        $assistsArray = array_unique($assistsArray);
        $takedownsArray = array_unique($takedownsArray);
        $deathsArray = array_unique($deathsArray);
        $timeSpentDeadArray = array_unique($timeSpentDeadArray);
        $experienceArray = array_unique($experienceArray);
        $siegeArray = array_unique($siegeArray);
        $heroDamageArray = array_unique($heroDamageArray);
        $healingArray = array_unique($healingArray);
        $selfHealingArray = array_unique($selfHealingArray);
        $mercsArray = array_unique($mercsArray);
        $watchTowerArray = array_unique($watchTowerArray);
        $damageTakenArray = array_unique($damageTakenArray);

        $warrdamageTakenArray = $damageTakenArray;

        sort($killsArray);
        sort($assistsArray);
        sort($takedownsArray);
        rsort($deathsArray);
        rsort($timeSpentDeadArray);
        sort($experienceArray);
        sort($siegeArray);
        sort($heroDamageArray);
        sort($healingArray);
        sort($selfHealingArray);
        sort($mercsArray);
        sort($watchTowerArray);
        rsort($damageTakenArray);
        sort($warrdamageTakenArray);

        $highest_kills = max($killsArray);
        $highest_takedowns = max($takedownsArray);
        $lowest_deaths = min($deathsArray);
        $highest_hero_damage = max($heroDamageArray);
        $highest_siege_damage = max($siegeArray);
        $highest_healing = max($healingArray);
        $highest_damage_taken = max($damageTakenArray);
        $highest_experience_contribution = max($experienceArray);


        foreach ($playerArray[0] as $player => $data) {
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $killsArray, "kills");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $assistsArray, "assists");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $deathsArray, "deaths");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $timeSpentDeadArray, "time_spent_dead");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $experienceArray, "experience_contribution");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $siegeArray, "siege_damage");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $healingArray, "healing");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $selfHealingArray, "self_healing");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $mercsArray, "merc_camp_captures");
            $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $watchTowerArray, "watch_tower_captures");

            if ($data["hero"]["new_role"] == "Tank") {
                $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $warrdamageTakenArray, "damage_taken");
            } else {
                $playerArray[0] = $this->setRankValue($playerArray[0], $data["score"], $player, $damageTakenArray, "damage_taken");
            }
        }

        foreach ($playerArray[1] as $player => $data) {
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $killsArray, "kills");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $assistsArray, "assists");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $deathsArray, "deaths");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $timeSpentDeadArray, "time_spent_dead");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $experienceArray, "experience_contribution");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $siegeArray, "siege_damage");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $healingArray, "healing");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $selfHealingArray, "self_healing");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $mercsArray, "merc_camp_captures");
            $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $watchTowerArray, "watch_tower_captures");

            if ($data["hero"]["new_role"] == "Tank") {
                $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $warrdamageTakenArray, "damage_taken");
            } else {
                $playerArray[1] = $this->setRankValue($playerArray[1], $data["score"], $player, $damageTakenArray, "damage_taken");
            }
        }



        $finalRankArray = [];
        $incrementValue = 0.01;

        foreach ($playerArray[0] as $player => $data) {
            $totalRank = $data["score"]["kills_rank"] +
            $data["score"]["assists_rank"] +
            $data["score"]["deaths_rank"] +
            $data["score"]["time_spent_dead_rank"] +
            $data["score"]["experience_contribution_rank"] +
            $data["score"]["siege_damage_rank"] +
            $data["score"]["healing_rank"] +
            $data["score"]["self_healing_rank"] +
            $data["score"]["merc_camp_captures_rank"] +
            $data["score"]["watch_tower_captures_rank"] +
            $data["score"]["damage_taken_rank"];

            $totalRank = ceil($totalRank / 11);

            $playerArray[0][$player]["total_rank"] = $totalRank;
        }

        foreach ($playerArray[1] as $player => $data) {
            $totalRank = $data["score"]["kills_rank"] +
            $data["score"]["assists_rank"] +
            $data["score"]["deaths_rank"] +
            $data["score"]["time_spent_dead_rank"] +
            $data["score"]["experience_contribution_rank"] +
            $data["score"]["siege_damage_rank"] +
            $data["score"]["healing_rank"] +
            $data["score"]["self_healing_rank"] +
            $data["score"]["merc_camp_captures_rank"] +
            $data["score"]["watch_tower_captures_rank"] +
            $data["score"]["damage_taken_rank"];

            $totalRank = ceil($totalRank / 11);

            $playerArray[1][$player]["total_rank"] = $totalRank;
        }

        return $playerArray;
    }
    private function setRankValue($playerArray, $data, $player, $array, $value){


        foreach ($playerArray as $player => $data) {



            for($i = 0; $i < count($array); $i++){

    


                if($data["score"][$value] == $array[$i]){
                    //$data["score"][$value . "_rank"] = ((100 / count($array)) * ($i + 1));

                    $playerArray[$player]["score"][$value . "_rank"] = ((100 / count($array)) * ($i + 1));
                }
            }
        }

        return $playerArray;
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
        $data = ReplayDraftOrder::where("replayID", $replayID)->orderBy("pick_number")->get();
        $modifiedData = $data->map(function ($item) use ($heroData){
            $item->hero = $heroData[$item->hero];
            return $item;
        });

        return $data;
    }

    private function getExperienceBreakdown($replayID){
        $data = ReplayExperienceBreakdownBlob::where("replayID", $replayID)->get();

        $team_one = $data[0]->data;

        $x_axis_time = [];
        $team_one_values = [];
        $team_two_values = [];

        $team_one_level = [];
        $team_two_level = [];

        foreach($data[0]->data as $experienceData){
            $carbon = Carbon::parse($experienceData["TimeSpan"]);
            $minutes = $carbon->minute;


            $x_axis_time[$minutes] = $minutes;
            $team_one_values[$minutes] = $experienceData["HeroXP"];
                //$team_one_values[$minutes] = $experienceData["TotalXP"]; //Add in other XP values later
            $team_one_level[$minutes] = $experienceData["TeamLevel"];
        }

        foreach($data[1]->data as $experienceData){
            $carbon = Carbon::parse($experienceData["TimeSpan"]);
            $minutes = $carbon->minute;


            $x_axis_time[$minutes] = $minutes;
            $team_two_values[$minutes] = $experienceData["HeroXP"];
                //$team_two_values[$minutes] = $experienceData["TotalXP"]; //Add in other XP values later
            $team_two_level[$minutes] = $experienceData["TeamLevel"];
        }

        $team_one_differences = [];
        $team_two_differences = [];

        foreach($x_axis_time as $minute){
            $team_one_differences[$minute] = ($team_one_values[$minute] - $team_two_values[$minute]) > 0 ? ($team_one_values[$minute] - $team_two_values[$minute]) : 0;
            $team_two_differences[$minute] = ($team_two_values[$minute] - $team_one_values[$minute]) > 0 ? ($team_two_values[$minute] - $team_one_values[$minute]) : 0;
        }

        return [
            "data" => $data,
            "team_one_values" => $team_one_values,
            "team_two_values" => $team_two_values,
            "team_one_differences" => $team_one_differences,
            "team_two_differences" => $team_two_differences,
            "x_axis_time" => $x_axis_time,
            "team_one_level" => $team_one_level,
            "team_two_level" => $team_two_level,
        ];
    }
}
