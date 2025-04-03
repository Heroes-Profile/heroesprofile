<?php

namespace App\Http\Controllers;

use App\Models\Award;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Models\ReplayExperienceBreakdownBlob;
use App\Rules\ReplayIDValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SingleMatchController extends Controller
{
    private $esport;

    private $schema;

    public function showWithoutEsport(Request $request, $replayID)
    {
        $validationRules = [
            'replayID' => ['required', 'integer', new ReplayIDValidation],
        ];

        $validator = Validator::make(compact('replayID'), $validationRules);
        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }
        return view('singleMatch')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'esport' => null,
            'replayID' => $replayID,
            'tournament' => null,
        ]);
    }

    public function showWithEsport(Request $request, $esport, $replayID)
    {
        $validationRules = [
            'esport' => 'required|in:NGS,CCL,MastersClash,HeroesInternational',
            'replayID' => 'required|integer',
            'tournament' => 'nullable|in:main,nationscup',
        ];

        $validator = Validator::make(compact('esport', 'replayID'), $validationRules);

        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => compact('esport', 'replayID'),
                    'status' => 'failure to validate inputs',
                ];
            }

        }
        return view('singleMatch')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'esport' => $esport,
            'replayID' => $replayID,
            'tournament' => $request["tournament"],
        ]);
    }

    public function getData(Request $request)
    {
        $validationRules = [
            'esport' => 'nullable|in:NGS,CCL,MastersClash,Other,HeroesInternational',
            'series' => 'nullable|string',
            'tournament' => 'nullable|in:main,nationscup',
            'user' => 'nullable',
            'replayID' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {

                    if (is_null($request->input('esport'))) {
                        $validator = new ReplayIDValidation;
                        if (! $validator->passes($attribute, $value)) {
                            $fail($validator->message());
                        }
                    }
                },
            ],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            if (env('Production')) {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'errors' => $validator->errors()->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        $this->esport = $request['esport'];
        $replayID = $request['replayID'];

        $this->schema = 'heroesprofile';

        $tournament = $request['tournament'];

        if ($this->esport == 'MastersClash') {
            $this->schema .= '_mcl';
        } elseif ($this->esport == 'Other') {
            $this->schema .= '_ml';
        } elseif ($this->esport == 'HeroesInternational') {
            if ($tournament == 'main') {
                $this->schema .= '_hi';
            } elseif ($tournament == 'nationscup') {
                $this->schema .= '_hi_nc';
            }

        } elseif ($this->esport) {
            $this->schema .= '_'.strtolower($this->esport);
        }

        $result = DB::table($this->schema.'.replay')
            ->join($this->schema.'.player', $this->schema.'.player.replayID', '=', $this->schema.'.replay.replayID')
            ->join($this->schema.'.battletags', $this->schema.'.battletags.player_id', '=', $this->schema.'.player.battletag')
            ->join($this->schema.'.scores', function ($join) {
                $join->on($this->schema.'.scores.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.scores.battletag', '=', $this->schema.'.player.battletag');
            })
            ->join($this->schema.'.talents', function ($join) {
                $join->on($this->schema.'.talents.replayID', '=', $this->schema.'.replay.replayID')
                    ->on($this->schema.'.talents.battletag', '=', $this->schema.'.player.battletag');
            })
            ->select([
                $this->schema.'.replay.game_date',
                $this->schema.'.replay.game_map',
                $this->schema.'.replay.game_length',
                $this->schema.'.replay.region',
                $this->schema.'.player.winner',
                $this->schema.'.player.blizz_id',
                $this->schema.'.player.hero',
                $this->schema.'.player.team',
                $this->schema.'.player.hero_level',
                $this->schema.'.battletags.battletag',
                $this->schema.'.scores.level',
                $this->schema.'.scores.kills',
                $this->schema.'.scores.assists',
                $this->schema.'.scores.takedowns',
                $this->schema.'.scores.deaths',
                $this->schema.'.scores.highest_kill_streak',
                $this->schema.'.scores.hero_damage',
                $this->schema.'.scores.siege_damage',
                $this->schema.'.scores.structure_damage',
                $this->schema.'.scores.minion_damage',
                $this->schema.'.scores.creep_damage',
                $this->schema.'.scores.summon_damage',
                $this->schema.'.scores.time_cc_enemy_heroes',
                $this->schema.'.scores.healing',
                $this->schema.'.scores.self_healing',
                $this->schema.'.scores.damage_taken',
                $this->schema.'.scores.experience_contribution',
                $this->schema.'.scores.town_kills',
                $this->schema.'.scores.time_spent_dead',
                $this->schema.'.scores.merc_camp_captures',
                $this->schema.'.scores.watch_tower_captures',
                $this->schema.'.scores.meta_experience',
                $this->schema.'.scores.protection_allies',
                $this->schema.'.scores.silencing_enemies',
                $this->schema.'.scores.rooting_enemies',
                $this->schema.'.scores.stunning_enemies',
                $this->schema.'.scores.clutch_heals',
                $this->schema.'.scores.escapes',
                $this->schema.'.scores.vengeance',
                $this->schema.'.scores.outnumbered_deaths',
                $this->schema.'.scores.teamfight_escapes',
                $this->schema.'.scores.teamfight_healing',
                $this->schema.'.scores.teamfight_damage_taken',
                $this->schema.'.scores.teamfight_hero_damage',
                $this->schema.'.scores.multikill',
                $this->schema.'.scores.physical_damage',
                $this->schema.'.scores.spell_damage',
                $this->schema.'.scores.regen_globes',
                $this->schema.'.scores.first_to_ten',
                $this->schema.'.talents.level_one AS level_one',
                $this->schema.'.talents.level_four AS level_four',
                $this->schema.'.talents.level_seven AS level_seven',
                $this->schema.'.talents.level_ten AS level_ten',
                $this->schema.'.talents.level_thirteen AS level_thirteen',
                $this->schema.'.talents.level_sixteen AS level_sixteen',
                $this->schema.'.talents.level_twenty AS level_twenty',
            ])
            ->when(! $this->esport, function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.game_type',
                    $this->schema.'.replay.date_added',
                    $this->schema.'.player.player_conservative_rating',
                    $this->schema.'.player.player_change',
                    $this->schema.'.player.hero_conservative_rating',
                    $this->schema.'.player.hero_change',
                    $this->schema.'.player.role_conservative_rating',
                    $this->schema.'.player.role_change',
                    $this->schema.'.player.mastery_taunt',
                    $this->schema.'.battletags.account_level',
                    $this->schema.'.scores.match_award',
                    $this->schema.'.scores.time_on_fire',
                    $this->schema.'.player.party',
                ]);
            })
            ->when($this->esport == 'NGS' || $this->esport == 'MastersClash', function ($query) {
                return $query->addSelect([
                    $this->schema.'.player.mastery_tier as mastery_taunt',
                    $this->schema.'.player.team_name',
                    $this->schema.'.replay.first_pick',

                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'HeroesInternational' || $this->esport == 'Other', function ($query) {
                return $query->addSelect([
                    $this->schema.'.player.mastery_tier as mastery_taunt',
                    $this->schema.'.player.team_id',
                    $this->schema.'.replay.first_pick',

                ]);
            })
            ->where($this->schema.'.replay.replayID', $replayID)
            ->orderBy('team', 'ASC')
            // ->toSql();
            ->get();

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $privateAccounts = $this->globalDataService->getPrivateAccounts();
        $user = $request['user'];

        if ($user) {
            $userBlizzId = $user['blizz_id'];
            $userRegion = $user['region'];

            $privateAccounts = $privateAccounts->reject(function ($item) use ($userBlizzId, $userRegion) {
                return $item['blizz_id'] === $userBlizzId && $item['region'] === $userRegion;
            });
        }

        $groupedData = $result->groupBy('replayID')->map(function ($replayGroup) use ($privateAccounts, $result, $talentData, $heroData, $maps, $replayID) {
            $totalSeconds = $replayGroup[0]->game_length - 70;
            $minutes = floor($totalSeconds / 60);
            $seconds = $totalSeconds % 60;
            $timeFormat = "$minutes minutes $seconds seconds";
            $region = $replayGroup[0]->region;

            $team_names = $this->esport ? $this->getTeamNames($result) : null;

            $replayDetails = [
                'region' => $replayGroup[0]->region,
                'downloadable' => ! $this->esport ? $replayGroup[0]->date_added > now()->subWeeks(4) : null,
                'game_type' => ! $this->esport ? $this->globalDataService->getGameTypeIDtoString()[$replayGroup[0]->game_type]['name'] : null,
                'game_date' => $replayGroup[0]->game_date,
                'game_map' => $maps[$replayGroup[0]->game_map],
                'game_length' => $timeFormat,
                'winner' => ($replayGroup[0]->team == 0 && $replayGroup[0]->winner == 1) ? 0 : 1,
                'players' => [],
                'replay_bans' => $this->getReplayBans($replayID, $heroData),
                'draft_order' => $this->esport != 'CCL' && $this->esport != 'MastersClash' && $this->esport != 'HeroesInternational' ? $this->getDraftOrder($replayID, $heroData) : null,
                'experience_breakdown' => $this->getExperienceBreakdown($replayID),
                'team_names' => $team_names,
                'map_bans' => ($this->esport && $this->esport != 'Other') ? $this->getMapBans($replayID, $maps, $team_names) : null,
                'first_pick' => $this->esport ? $replayGroup[0]->first_pick : null,
                'match_games' => $this->esport ? $this->getMatchGames($replayID, $maps) : null,
            ];

            $replayDetails['players'] = $replayGroup->groupBy('team')->map(function ($teamGroup) use ($privateAccounts, $heroData, $talentData, $region) {
                return $teamGroup->map(function ($row) use ($privateAccounts, $heroData, $talentData, $region) {
                    $hero_level_calculated = $row->hero_level;
                    $avg_hero_level = $row->hero_level;

                    if ($row->mastery_taunt == 1) {
                        $hero_level_calculated = '15-25';
                        $avg_hero_level = 20;

                    } elseif ($row->mastery_taunt == 2) {
                        $hero_level_calculated = '25-50';
                        $avg_hero_level = 37.5;

                    } elseif ($row->mastery_taunt == 3) {
                        $hero_level_calculated = '50-75';
                        $avg_hero_level = 62.5;

                    } elseif ($row->mastery_taunt == 4) {
                        $hero_level_calculated = '75-100';
                        $avg_hero_level = 87.5;

                    } elseif ($row->mastery_taunt == 5) {
                        $hero_level_calculated = '100+';
                        $avg_hero_level = 100;

                    }
                    $blizz_id = $row->blizz_id;

                    $containsAccount = $privateAccounts->contains(function ($account) use ($blizz_id, $region) {
                        return $account['blizz_id'] == $blizz_id && $account['region'] == $region;
                    });

                    if ($row->level_one) {
                        if ($row->level_one != 0) {
                            $row->level_one = $talentData->has($row->level_one) ? $talentData[$row->level_one] : null;
                        }
                    }

                    if ($row->level_four) {
                        if ($row->level_four != 0) {
                            $row->level_four = $talentData->has($row->level_four) ? $talentData[$row->level_four] : null;
                        }
                    }

                    if ($row->level_seven) {
                        if ($row->level_seven != 0) {
                            $row->level_seven = $talentData->has($row->level_seven) ? $talentData[$row->level_seven] : null;
                        }
                    }

                    if ($row->level_ten) {
                        if ($row->level_ten != 0) {
                            $row->level_ten = $talentData->has($row->level_ten) ? $talentData[$row->level_ten] : null;
                        }
                    }

                    if ($row->level_thirteen) {
                        if ($row->level_thirteen != 0) {
                            $row->level_thirteen = $talentData->has($row->level_thirteen) ? $talentData[$row->level_thirteen] : null;
                        }
                    }

                    if ($row->level_sixteen) {
                        if ($row->level_sixteen != 0) {
                            $row->level_sixteen = $talentData->has($row->level_sixteen) ? $talentData[$row->level_sixteen] : null;
                        }
                    }

                    if ($row->level_twenty) {
                        if ($row->level_twenty != 0) {
                            $row->level_twenty = $talentData->has($row->level_twenty) ? $talentData[$row->level_twenty] : null;
                        }
                    }

                    $blizz_id = $this->esport ? $row->blizz_id : ($containsAccount ? null : $row->blizz_id);

                    return [
                        'check' => $containsAccount,
                        'region' => $this->esport ? $region : ($containsAccount ? null : $region),
                        'battletag' => $this->esport ? explode('#', $row->battletag)[0] : ($containsAccount ? null : explode('#', $row->battletag)[0]),
                        'blizz_id' => $blizz_id,
                        'hp_owner' => ($row->battletag == 'Zemill#1940' && $region == 1 && $blizz_id == '67280') ? true : false,
                        'winner' => $row->winner,
                        'team' => $row->team,
                        'party' => ! $this->esport ? $row->party : null,
                        'hero' => $heroData[$row->hero],
                        'patreon_subscriber' => ! $this->esport ? $this->globalDataService->checkIfSiteFlair($blizz_id, $region) : null,
                        'match_award' => ! $this->esport ? Award::where('award_id', $row->match_award)->first() : null,
                        'hero_level' => $containsAccount ? null : $hero_level_calculated,
                        'avg_hero_level' => $containsAccount ? null : $avg_hero_level,
                        'account_level' => ($this->esport || $containsAccount) ? null : $row->account_level,
                        'player_conservative_rating' => ($this->esport || $containsAccount) ? null : $row->player_conservative_rating,
                        'player_mmr' => ($this->esport || $containsAccount) ? null : round(1800 + 40 * $row->player_conservative_rating),
                        'player_change' => ($this->esport || $containsAccount) ? null : round($row->player_change, 2),
                        'hero_conservative_rating' => ($this->esport || $containsAccount) ? null : $row->hero_conservative_rating,
                        'hero_mmr' => ($this->esport || $containsAccount) ? null : round(1800 + 40 * $row->hero_conservative_rating),
                        'hero_change' => ($this->esport || $containsAccount) ? null : round($row->hero_change, 2),
                        'role_conservative_rating' => ($this->esport || $containsAccount) ? null : $row->role_conservative_rating,
                        'role_mmr' => ($this->esport || $containsAccount) ? null : round(1800 + 40 * $row->role_conservative_rating),
                        'role_change' => ($this->esport || $containsAccount) ? null : round($row->role_change, 2),
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
                            'match_award' => ! $this->esport ? $row->match_award : null,
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
                            'time_on_fire' => ! $this->esport ? $row->time_on_fire : null,
                        ],
                        'talents' => [
                            'level_one' => $row->level_one,
                            'level_four' => $row->level_four,
                            'level_seven' => $row->level_seven,
                            'level_ten' => $row->level_ten,
                            'level_thirteen' => $row->level_thirteen,
                            'level_sixteen' => $row->level_sixteen,
                            'level_twenty' => $row->level_twenty,
                        ],

                    ];
                });
            })->toArray();
            $replayDetails['players'] = $this->updatePartyData($replayDetails['players']);
            $replayDetails['players'] = $this->calculateHPScore($replayDetails['players']);

            return $replayDetails;
        });
        $groupedData = array_values($groupedData->toArray());

        return $groupedData[0];
    }

    private function updatePartyData(&$playerArray)
    {
        $partyArray = [];
        $partColorArray = ['red', 'blue', 'teal', 'orange', 'brown', 'purple'];
        $colorCounter = 0;

        foreach ($playerArray[0] as &$playerData) {
            if ($playerData['party'] != '' && $playerData['party'] != 0 && ! array_key_exists($playerData['party'], $partyArray)) {
                $partyArray[$playerData['party']] = $partColorArray[$colorCounter];
                $colorCounter++;
            }
            $playerData['party'] = ($playerData['party'] != '' && $playerData['party'] != 0) ? $partyArray[$playerData['party']] : null;
        }

        foreach ($playerArray[1] as &$playerData) {
            if ($playerData['party'] != '' && $playerData['party'] != 0 && ! array_key_exists($playerData['party'], $partyArray)) {
                $partyArray[$playerData['party']] = $partColorArray[$colorCounter];
                $colorCounter++;
            }
            $playerData['party'] = ($playerData['party'] != '' && $playerData['party'] != 0) ? $partyArray[$playerData['party']] : null;
        }

        unset($playerData);

        return $playerArray;
    }

    private function calculateHPScore($playerArray)
    {
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
            $killsArray[$counter] = $playerData['score']['kills'];
            $assistsArray[$counter] = $playerData['score']['assists'];
            $takedownsArray[$counter] = $playerData['score']['takedowns'];
            $deathsArray[$counter] = $playerData['score']['deaths'];
            $timeSpentDeadArray[$counter] = $playerData['score']['time_spent_dead'];
            $experienceArray[$counter] = $playerData['score']['experience_contribution'];
            $siegeArray[$counter] = $playerData['score']['siege_damage'];
            $heroDamageArray[$counter] = $playerData['score']['hero_damage'];
            $healingArray[$counter] = $playerData['score']['healing'];
            $selfHealingArray[$counter] = $playerData['score']['self_healing'];
            $mercsArray[$counter] = $playerData['score']['merc_camp_captures'];
            $watchTowerArray[$counter] = $playerData['score']['watch_tower_captures'];
            $damageTakenArray[$counter] = $playerData['score']['damage_taken'];

            $counter++;
        }

        foreach ($playerArray[1] as $player => $playerData) {
            $killsArray[$counter] = $playerData['score']['kills'];
            $assistsArray[$counter] = $playerData['score']['assists'];
            $takedownsArray[$counter] = $playerData['score']['takedowns'];
            $deathsArray[$counter] = $playerData['score']['deaths'];
            $timeSpentDeadArray[$counter] = $playerData['score']['time_spent_dead'];
            $experienceArray[$counter] = $playerData['score']['experience_contribution'];
            $siegeArray[$counter] = $playerData['score']['siege_damage'];
            $heroDamageArray[$counter] = $playerData['score']['hero_damage'];
            $healingArray[$counter] = $playerData['score']['healing'];
            $selfHealingArray[$counter] = $playerData['score']['self_healing'];
            $mercsArray[$counter] = $playerData['score']['merc_camp_captures'];
            $watchTowerArray[$counter] = $playerData['score']['watch_tower_captures'];
            $damageTakenArray[$counter] = $playerData['score']['damage_taken'];

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
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $killsArray, 'kills');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $assistsArray, 'assists');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $deathsArray, 'deaths');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $timeSpentDeadArray, 'time_spent_dead');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $experienceArray, 'experience_contribution');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $siegeArray, 'siege_damage');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $healingArray, 'healing');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $selfHealingArray, 'self_healing');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $mercsArray, 'merc_camp_captures');
            $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $watchTowerArray, 'watch_tower_captures');

            if ($data['hero']['new_role'] == 'Tank') {
                $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $warrdamageTakenArray, 'damage_taken');
            } else {
                $playerArray[0] = $this->setRankValue($playerArray[0], $data['score'], $player, $damageTakenArray, 'damage_taken');
            }
        }

        foreach ($playerArray[1] as $player => $data) {
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $killsArray, 'kills');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $assistsArray, 'assists');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $deathsArray, 'deaths');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $timeSpentDeadArray, 'time_spent_dead');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $experienceArray, 'experience_contribution');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $siegeArray, 'siege_damage');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $healingArray, 'healing');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $selfHealingArray, 'self_healing');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $mercsArray, 'merc_camp_captures');
            $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $watchTowerArray, 'watch_tower_captures');

            if ($data['hero']['new_role'] == 'Tank') {
                $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $warrdamageTakenArray, 'damage_taken');
            } else {
                $playerArray[1] = $this->setRankValue($playerArray[1], $data['score'], $player, $damageTakenArray, 'damage_taken');
            }
        }

        $finalRankArray = [];
        $incrementValue = 0.01;

        foreach ($playerArray[0] as $player => $data) {
            $totalRank = $data['score']['kills_rank'] +
            $data['score']['assists_rank'] +
            $data['score']['deaths_rank'] +
            $data['score']['time_spent_dead_rank'] +
            $data['score']['experience_contribution_rank'] +
            $data['score']['siege_damage_rank'] +
            $data['score']['healing_rank'] +
            $data['score']['self_healing_rank'] +
            $data['score']['merc_camp_captures_rank'] +
            $data['score']['watch_tower_captures_rank'] +
            $data['score']['damage_taken_rank'];

            $totalRank = ceil($totalRank / 11);

            $playerArray[0][$player]['total_rank'] = $totalRank;
        }

        foreach ($playerArray[1] as $player => $data) {
            $totalRank = $data['score']['kills_rank'] +
            $data['score']['assists_rank'] +
            $data['score']['deaths_rank'] +
            $data['score']['time_spent_dead_rank'] +
            $data['score']['experience_contribution_rank'] +
            $data['score']['siege_damage_rank'] +
            $data['score']['healing_rank'] +
            $data['score']['self_healing_rank'] +
            $data['score']['merc_camp_captures_rank'] +
            $data['score']['watch_tower_captures_rank'] +
            $data['score']['damage_taken_rank'];

            $totalRank = ceil($totalRank / 11);

            $playerArray[1][$player]['total_rank'] = $totalRank;
        }

        return $playerArray;
    }

    private function setRankValue($playerArray, $data, $player, $array, $value)
    {

        foreach ($playerArray as $player => $data) {

            for ($i = 0; $i < count($array); $i++) {

                if ($data['score'][$value] == $array[$i]) {
                    // $data["score"][$value . "_rank"] = ((100 / count($array)) * ($i + 1));

                    $playerArray[$player]['score'][$value.'_rank'] = ((100 / count($array)) * ($i + 1));
                }
            }
        }

        return $playerArray;
    }

    private function getReplayBans($replayID, $heroData)
    {
        $replayBans = DB::table($this->schema.'.replay_bans')
            ->select('team', 'hero')
            ->where('replayID', $replayID)
            ->orderBy('ban_id')
            ->get()
            ->groupBy('team')
            ->map(function ($teamGroup) use ($heroData) {
                return $teamGroup->map(function ($replayBan) use ($heroData) {
                    $replayBan->hero = $heroData[$replayBan->hero] ?? $replayBan->hero;

                    return $replayBan;
                });
            });

        return $replayBans;
    }

    private function getDraftOrder($replayID, $heroData)
    {
        $allZeros = true;

        $replayBans = DB::table($this->schema.'.replay_draft_order')->where('replayID', $replayID)->orderBy('pick_number')->get();
        $modifiedData = $replayBans->map(function ($item) use ($heroData) {

            if ($item->hero != 0) {
                $item->hero = $heroData[$item->hero];
            } else {
                $item->hero = 'No Pick';
            }

            return $item;
        });

        return $replayBans;

    }

    private function getExperienceBreakdown($replayID)
    {
        $data = ReplayExperienceBreakdownBlob::where('replayID', $replayID)->get();

        if ($data->isEmpty()) {
            return null;
        }
        $team_one = $data[0]->data;

        $x_axis_time = [];
        $team_one_values = [];
        $team_two_values = [];

        $team_one_level = [];
        $team_two_level = [];

        foreach ($data[0]->data as $experienceData) {
            $carbon = Carbon::parse($experienceData['TimeSpan']);
            $minutes = $carbon->minute;

            $x_axis_time[$minutes] = $minutes;
            $team_one_values[$minutes] = $experienceData['HeroXP'];
            // $team_one_values[$minutes] = $experienceData["TotalXP"]; //Add in other XP values later
            $team_one_level[$minutes] = $experienceData['TeamLevel'];
        }

        foreach ($data[1]->data as $experienceData) {
            $carbon = Carbon::parse($experienceData['TimeSpan']);
            $minutes = $carbon->minute;

            $x_axis_time[$minutes] = $minutes;
            $team_two_values[$minutes] = $experienceData['HeroXP'];
            // $team_two_values[$minutes] = $experienceData["TotalXP"]; //Add in other XP values later
            $team_two_level[$minutes] = $experienceData['TeamLevel'];
        }

        $team_one_differences = [];
        $team_two_differences = [];

        foreach ($x_axis_time as $minute) {
            $team_one_differences[$minute] = ($team_one_values[$minute] - $team_two_values[$minute]) > 0 ? ($team_one_values[$minute] - $team_two_values[$minute]) : 0;
            $team_two_differences[$minute] = ($team_two_values[$minute] - $team_one_values[$minute]) > 0 ? ($team_two_values[$minute] - $team_one_values[$minute]) : 0;
        }

        return [
            'data' => $data,
            'team_one_values' => $team_one_values,
            'team_two_values' => $team_two_values,
            'team_one_differences' => $team_one_differences,
            'team_two_differences' => $team_two_differences,
            'x_axis_time' => $x_axis_time,
            'team_one_level' => $team_one_level,
            'team_two_level' => $team_two_level,
        ];
    }

    private function getTeamNames($results)
    {
        $team_one_id = null;
        $team_zero_id = null;

        foreach ($results as $row) {

            if ($row->team == 0) {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $team_zero_id = $row->team_name;
                } elseif ($this->esport == 'CCL' || $this->esport == 'Other' || $this->esport == 'HeroesInternational') {
                    $team_zero_id = $row->team_id;
                }
            } else {
                if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
                    $team_one_id = $row->team_name;
                } elseif ($this->esport == 'CCL' || $this->esport == 'Other' || $this->esport == 'HeroesInternational') {
                    $team_one_id = $row->team_id;
                }
            }

            if (! is_null($team_one_id) && ! is_null($team_zero_id)) {
                break;
            }
        }

        return [
            'team_one' => DB::table($this->schema.'.teams')->where('team_id', $team_zero_id)->first(),
            'team_two' => DB::table($this->schema.'.teams')->where('team_id', $team_one_id)->first(),
        ];
    }

    private function getMapBans($replayID, $maps, $team_names)
    {
        $result = DB::table($this->schema.'.replay')
            ->select('season', 'team_0_map_ban', 'team_0_map_ban_2', 'team_1_map_ban', 'team_1_map_ban_2')

            ->when($this->esport == 'NGS', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.division_0',
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                ]);
            })
            ->when($this->esport == 'MastersClash', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'HeroesInternational', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_id',
                    $this->schema.'.replay.team_1_id',
                ]);
            })
            ->where('replayID', $replayID)
            ->first();

        $team_name_0 = null;
        $team_name_1 = null;

        if ($this->esport == 'NGS' || $this->esport == 'MastersClash') {
            $team_name_0 = $result->team_0_name;
            $team_name_1 = $result->team_1_name;
        } elseif ($this->esport == 'CCL' || $this->esport == 'HeroesInternational') {
            $team_name_0 = $result->team_0_id;
            $team_name_1 = $result->team_1_id;
        }

        $team_zero_data = DB::table($this->schema.'.teams')
            ->where('season', $result->season)
            ->when($this->esport == 'NGS', function ($query) use ($result) {
                return $query->where('division', $result->division_0);
            })
            ->where('team_name', $team_name_0)
            ->first();

        $team_one_data = DB::table($this->schema.'.teams')
            ->where('season', $result->season)
            ->when($this->esport == 'NGS', function ($query) use ($result) {
                return $query->where('division', $result->division_0);
            })
            ->where('team_name', $team_name_1)
            ->first();

        if ($team_names['team_one']->team_name == $team_zero_data->team_name) {
            $team_zero_ban_data = [
                'team_data' => $team_zero_data,
                'name' => $team_zero_data->team_name,
                'map_ban_one' => $result->team_0_map_ban != 0 ? $maps[$result->team_0_map_ban] : null,
                'map_ban_two' => $result->team_0_map_ban_2 != 0 ? $maps[$result->team_0_map_ban_2] : null,
            ];
        } else {
            $team_zero_ban_data = [
                'team_data' => $team_one_data,
                'name' => $team_one_data->team_name,
                'map_ban_one' => $result->team_1_map_ban != 0 ? $maps[$result->team_1_map_ban] : null,
                'map_ban_two' => $result->team_1_map_ban_2 != 0 ? $maps[$result->team_1_map_ban_2] : 0,
            ];
        }

        if ($team_names['team_two']->team_name == $team_one_data->team_name) {
            $team_one_ban_data = [
                'team_data' => $team_one_data,
                'name' => $team_one_data->team_name,
                'map_ban_one' => $result->team_1_map_ban != 0 ? $maps[$result->team_1_map_ban] : null,
                'map_ban_two' => $result->team_1_map_ban_2 != 0 ? $maps[$result->team_1_map_ban_2] : null,
            ];
        } else {
            $team_one_ban_data = [
                'team_data' => $team_zero_data,
                'name' => $team_zero_data->team_name,
                'map_ban_one' => $result->team_0_map_ban != 0 ? $maps[$result->team_0_map_ban] : null,
                'map_ban_two' => $result->team_0_map_ban_2 != 0 ? $maps[$result->team_0_map_ban_2] : null,
            ];
        }

        return [
            'team_zero_ban_data' => $team_zero_ban_data,
            'team_one_ban_data' => $team_one_ban_data,
        ];
    }

    private function getMatchGames($replayID, $maps)
    {
        $result = DB::table($this->schema.'.replay')
            ->select('season', 'round')
            ->when($this->esport == 'NGS', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.division_0',
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                ]);
            })
            ->when($this->esport == 'MastersClash', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_name',
                    $this->schema.'.replay.team_1_name',
                ]);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'HeroesInternational', function ($query) {
                return $query->addSelect([
                    $this->schema.'.replay.team_0_id',
                    $this->schema.'.replay.team_1_id',
                ]);
            })
            ->where('replayID', $replayID)
            ->first();

        $matches = DB::table($this->schema.'.replay')
            ->select('replayID', 'round', 'game', 'game_map')
            ->where('season', $result->season)
            ->when($this->esport == 'NGS', function ($query) use ($result) {
                return $query->where('division_0', $result->division_0)->where('team_0_name', $result->team_0_name)->where('team_1_name', $result->team_1_name);
            })
            ->when($this->esport == 'MastersClash', function ($query) use ($result) {
                return $query->where('team_0_name', $result->team_0_name)->where('team_1_name', $result->team_1_name);
            })
            ->when($this->esport == 'CCL' || $this->esport == 'HeroesInternational', function ($query) use ($result) {
                return $query->where('team_0_id', $result->team_0_id)->where('team_1_id', $result->team_1_id);
            })
            ->where('round', $result->round)
            ->orderBy('game_date', 'asc')
            ->get();

        foreach ($matches as $row) {
            $row->game_map = $maps[$row->game_map];
        }

        return $matches;
    }
}
