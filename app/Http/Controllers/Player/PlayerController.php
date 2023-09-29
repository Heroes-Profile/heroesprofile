<?php

namespace App\Http\Controllers\Player;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Models\PatreonAccount;
use App\Models\Replay;
use App\Models\LaravelProfilePage;
use App\Models\Battletag;
use App\Models\MMRTypeID;
use App\Models\Map;
use App\Models\HeroesDataTalent;

use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataUD;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataAR;

use App\Models\BattlenetAccount;

class PlayerController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {
        ///Add in some rule handling for game type and season

        $data = [
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region
        ];

        $validator = \Validator::make($data, [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }
        $heroUserSettings = null;
        $checkedUser = BattlenetAccount::where("battletag", "like", $battletag . "%")
            ->where("blizz_id", $blizz_id)
            ->where("region", $region)
            ->first();

        if ($checkedUser) {
            $heroUserSettings = $checkedUser->userSettings->where('setting', 'hero')->first();
            $heroUserSettings = $this->globalDataService->getHeroesByID()[$heroUserSettings["value"]];
        }

        return view('Player.player')->with([
                'settingHero' => $heroUserSettings,
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                ]);
    }

    public function getPlayerData(Request $request){
        $data = [
            'blizz_id' => $request["blizz_id"],
            'region' => $request["region"],
            'battletag' => $request["battletag"],
        ];

        $validator = \Validator::make($data, [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'battletag' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }

        $cachedData = LaravelProfilePage::filterByBlizzID($request["blizz_id"])
            ->filterByRegion($request["region"])
            ->where("game_type", $request["game_type"])
            ->where("season", $request["season"])
            ->first();
        

        if(!$cachedData){
            $cachedData = $this->calculateProfile($request["blizz_id"], $request["region"], $request["game_type"], $request["season"]);
        }else{
            $latestReplayID = Replay::select("replay.replayID")
                ->join('player', 'player.replayID', '=', 'replay.replayID')
                ->where("blizz_id", $request["blizz_id"])
                ->where("region", $request["region"])
                //->where("game_type", $request["game_type"]) //fix later
                //->where("season", $request["season"]) //fix later
                ->orderBy("replayID", "DESC")
                ->limit(1)
                ->first()
                ->replayID ?? null;

            if ($latestReplayID && $cachedData->latest_replayID < $latestReplayID) {
                $cachedData = $this->calculateProfile($request["blizz_id"], $request["region"], $request["game_type"], $request["season"], $cachedData);
            }
        }

        return $this->formatCache($cachedData, $request["blizz_id"], $request["region"], $request["battletag"]);
    }

    private function calculateProfile($blizz_id, $region, $game_type, $season, $cachedData = null){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        
        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                     ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('talents', function($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                     ->on('talents.battletag', '=', 'player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->select([
                "replay.replayID AS replayID",
                "replay.game_type AS game_type",
                "replay.game_date as game_date",
                "replay.game_map AS game_map",
                "replay.game_length AS game_length",
                "player.winner AS winner",
                "player.stack_size AS stack_size",
                "player.hero AS hero",
                "player.player_conservative_rating AS player_conservative_rating",
                "player.player_change AS player_change",
                "player.hero_conservative_rating AS hero_conservative_rating",
                "player.hero_change AS hero_change",
                "player.role_conservative_rating AS role_conservative_rating",
                "player.role_change AS role_change",
                "scores.first_to_ten AS first_to_ten",
                "scores.kills AS kills",
                "scores.deaths AS deaths",
                "scores.time_on_fire AS time_on_fire",
                "scores.takedowns AS takedowns",
                "scores.match_award AS match_award",
                "heroes.new_role as role",
                "talents.level_one AS level_one",
                "talents.level_four AS level_four",
                "talents.level_seven AS level_seven",
                "talents.level_ten AS level_ten",
                "talents.level_thirteen AS level_thirteen",
                "talents.level_sixteen AS level_sixteen",
                "talents.level_twenty AS level_twenty"
            ])
            ->where("blizz_id", $blizz_id)
            ->whereNot("game_type", 0)
            ->where("region", $region)
            //->where("replay.replayID", "<=", 46984901) //testing
            ->when($cachedData, function ($query, $cachedData) {
                return $query->where('replay.replayID', '>', $cachedData->latest_replayID);
            })
            //->toSql();
            ->get();


        if ($result->isEmpty()) {
            return $cachedData;
        }

        $wins = $result->where('winner', 1)->count();
        $losses = $result->where('winner', 0)->count();

        $kills = $result->sum('kills');
        $deaths = $result->sum('deaths');
        $takedowns = $result->sum('takedowns');

        $first_to_ten_wins = $result->where('winner', 1)->where("first_to_ten", 1)->count();
        $first_to_ten_losses = $result->where('winner', 0)->where("first_to_ten", 1)->count();

        $second_to_ten_wins = $result->where('winner', 1)->where("first_to_ten", 0)->whereNotNull('first_to_ten')->count();
        $second_to_ten_losses = $result->where('winner', 0)->where("first_to_ten", 0)->whereNotNull('first_to_ten')->count();

        $bruiser_wins = $result->where('winner', 1)->where("role", "Bruiser")->count();
        $bruiser_losses = $result->where('winner', 0)->where("role", "Bruiser")->count();

        $support_wins = $result->where('winner', 1)->where("role", "Support")->count();
        $support_losses = $result->where('winner', 0)->where("role", "Support")->count();

        $ranged_assassin_wins = $result->where('winner', 1)->where("role", "Ranged Assassin")->count();
        $ranged_assassin_losses = $result->where('winner', 0)->where("role", "Ranged Assassin")->count();

        $melee_assassin_wins = $result->where('winner', 1)->where("role", "Melee Assassin")->count();
        $melee_assassin_losses = $result->where('winner', 0)->where("role", "Melee Assassin")->count();

        $healer_wins = $result->where('winner', 1)->where("role", "Healer")->count();
        $healer_losses = $result->where('winner', 0)->where("role", "Healer")->count();

        $tank_wins = $result->where('winner', 1)->where("role", "Tank")->count();
        $tank_losses = $result->where('winner', 0)->where("role", "Tank")->count();

        $total_time_played = $result->sum('game_length');

        $account_level = Battletag::where("blizz_id", $blizz_id)
            ->where("region", $region)
            ->max('account_level');

        //10355545 is the replayID where we started tracking mvp 
        $mvp_games = $result->where('replayID', ">", 10355545)->count();
        $games_mvp = $result->where('match_award', 1)->count();


        $time_on_fire_games = $result->filter(function($item) {
            return !is_null($item->time_on_fire);
        })->count();

        $time_on_fire_total = $result->filter(function($item) {
            return !is_null($item->time_on_fire);
        })->sum('time_on_fire');


        $stack_one_wins = $result->sum(function ($item) {
            return (isset($item->stack_size) && $item->stack_size !== '' && isset($item->winner) && $item->winner !== '' && $item->stack_size == 0 && $item->winner == 1) ? 1 : 0;
        });

        $stack_two_wins = $result->sum(function ($item) {
            return ($item->stack_size == 2 && $item->winner == 1) ? 1 : 0;
        });

        $stack_three_wins = $result->sum(function ($item) {
            return ($item->stack_size == 3 && $item->winner == 1) ? 1 : 0;
        });

        $stack_four_wins = $result->sum(function ($item) {
            return ($item->stack_size == 4 && $item->winner == 1) ? 1 : 0;
        });

        $stack_five_wins = $result->sum(function ($item) {
            return ($item->stack_size == 5 && $item->winner == 1) ? 1 : 0;
        });

        $stack_one_losses = $result->sum(function ($item) {
            return (isset($item->stack_size) && $item->stack_size !== '' && isset($item->winner) && $item->winner !== '' && $item->stack_size == 0 && $item->winner == 0) ? 1 : 0;
        });

        $stack_two_losses = $result->sum(function ($item) {
            return ($item->stack_size == 2 && $item->winner == 0) ? 1 : 0;
        });

        $stack_three_losses = $result->sum(function ($item) {
            return ($item->stack_size == 3 && $item->winner == 0) ? 1 : 0;
        });

        $stack_four_losses = $result->sum(function ($item) {
            return ($item->stack_size == 4 && $item->winner == 0) ? 1 : 0;
        });

        $stack_five_losses = $result->sum(function ($item) {
            return ($item->stack_size == 5 && $item->winner == 0) ? 1 : 0;
        });


        $latest_replayID = $result->max('replayID');


        $matches = $result->sortByDesc('game_date')->take(5);
        $matches = $matches->map(function ($item) {
            unset($item->game_length);
            unset($item->stack_size);
            unset($item->kills);
            unset($item->deaths);
            unset($item->time_on_fire);
            unset($item->takedowns);
            unset($item->match_award);
            unset($item->first_to_ten);
            unset($item->role);
            return $item;
        });

        if($cachedData){
            $existingMatches = json_decode($cachedData->matches);
            $mergedMatches = collect($existingMatches)->merge($matches);
            $matches = $mergedMatches->sortByDesc('game_date')->take(5)->values()->toArray();
        }

        $heroData = $result->groupBy('hero')->map(function($items, $hero) {
            $wins = $items->where('winner', 1)->count();
            $losses = $items->where('winner', 0)->count();
            $games_played = $wins + $losses;
            $latest_game_date = $items->max('game_date');

            return [
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $games_played,
                'game_date' => $latest_game_date
            ];
        });

      if($cachedData){
            $existingHeroData = json_decode($cachedData->hero_data, true);
            foreach ($heroData as $hero => $data) {
                if (isset($existingHeroData[$hero])) {
                    $existingHeroData[$hero]['wins'] += $data['wins'];
                    $existingHeroData[$hero]['losses'] += $data['losses'];
                    $existingHeroData[$hero]['games_played'] += $data['games_played'];
                    $existingHeroData[$hero]['game_date'] = max($existingHeroData[$hero]['game_date'], $data['game_date']);
                } else {
                    $existingHeroData[$hero] = $data;
                }
            }
            $heroData = $existingHeroData;
        }

        $mapData = $result->groupBy('game_map')->map(function($items, $game_map) {
            $wins = $items->where('winner', 1)->count();
            $losses = $items->where('winner', 0)->count();
            $games_played = $wins + $losses;
            $latest_game_date = $items->max('game_date');

            return [
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $games_played,
                'game_date' => $latest_game_date
            ];
        });

        if($cachedData){
            $existingMapData = json_decode($cachedData->map_data, true);
            foreach ($mapData as $map => $data) {
                if (isset($existingMapData[$map])) {
                    $existingMapData[$map]['wins'] += $data['wins'];
                    $existingMapData[$map]['losses'] += $data['losses'];
                    $existingMapData[$map]['games_played'] += $data['games_played'];
                    $existingMapData[$map]['game_date'] = max($existingMapData[$map]['game_date'], $data['game_date']);
                } else {
                    $existingMapData[$map] = $data;
                }
            }
            $mapData = $existingMapData;
        }


        if(!$cachedData){
            $dataToSave = new LaravelProfilePage;
            $dataToSave->blizz_id = $blizz_id;
            $dataToSave->region = $region;
            $dataToSave->game_type = $game_type;
            $dataToSave->season = $season;

            $dataToSave->save();
        }else{
            $dataToSave = $cachedData;
        }
        
        $dataToSave->wins += $wins;
        $dataToSave->losses += $losses;
        $dataToSave->kills += $kills;
        $dataToSave->deaths += $deaths;
        $dataToSave->takedowns += $takedowns;
        $dataToSave->first_to_ten_wins += $first_to_ten_wins;
        $dataToSave->first_to_ten_losses += $first_to_ten_losses;
        $dataToSave->second_to_ten_wins += $second_to_ten_wins;
        $dataToSave->second_to_ten_losses += $second_to_ten_losses;
        $dataToSave->bruiser_wins += $bruiser_wins;
        $dataToSave->bruiser_losses += $bruiser_losses;
        $dataToSave->support_wins += $support_wins;
        $dataToSave->support_losses += $support_losses;
        $dataToSave->ranged_assassin_wins += $ranged_assassin_wins;
        $dataToSave->ranged_assassin_losses += $ranged_assassin_losses;
        $dataToSave->melee_assassin_wins += $melee_assassin_wins;
        $dataToSave->melee_assassin_losses += $melee_assassin_losses;
        $dataToSave->healer_wins += $healer_wins;
        $dataToSave->healer_losses += $healer_losses;
        $dataToSave->tank_wins += $tank_wins;
        $dataToSave->tank_losses += $tank_losses;
        $dataToSave->mvp_games += $mvp_games;
        $dataToSave->games_mvp += $games_mvp;
        $dataToSave->time_on_fire_games += $time_on_fire_games;
        $dataToSave->time_on_fire_total += $time_on_fire_total;
        $dataToSave->stack_one_wins += $stack_one_wins;
        $dataToSave->stack_two_wins += $stack_two_wins;
        $dataToSave->stack_three_wins += $stack_three_wins;
        $dataToSave->stack_four_wins += $stack_four_wins;
        $dataToSave->stack_five_wins += $stack_five_wins;
        $dataToSave->stack_one_losses += $stack_one_losses;
        $dataToSave->stack_two_losses += $stack_two_losses;
        $dataToSave->stack_three_losses += $stack_three_losses;
        $dataToSave->stack_four_losses += $stack_four_losses;
        $dataToSave->stack_five_losses += $stack_five_losses;
        $dataToSave->total_time_played += $total_time_played;


        
        $dataToSave->account_level = $account_level;
        $dataToSave->latest_replayID = $latest_replayID;

        $dataToSave->matches = $matches;
        $dataToSave->hero_data = $heroData;
        $dataToSave->map_data = $mapData;

        $dataToSave->save();

        return $dataToSave;
    }

    private function formatCache($data, $blizz_id, $region, $battletag){
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        $returnData = new \stdClass;
        $returnData->wins = $data->wins;
        $returnData->losses = $data->losses;
        $returnData->first_to_ten_wins = $data->first_to_ten_wins;
        $returnData->first_to_ten_losses = $data->first_to_ten_losses;
        $returnData->first_to_ten_win_rate = ($data->first_to_ten_wins + $data->first_to_ten_losses) > 0 ? round(($data->first_to_ten_wins / ($data->first_to_ten_wins + $data->first_to_ten_losses)) * 100, 2): 0;
        $returnData->second_to_ten_wins = $data->second_to_ten_wins;
        $returnData->second_to_ten_losses = $data->second_to_ten_losses;
        $returnData->second_to_ten_win_rate = ($data->second_to_ten_wins + $data->second_to_ten_losses) > 0 ? round(($data->second_to_ten_wins / ($data->second_to_ten_wins + $data->second_to_ten_losses)) * 100, 2): 0;
        $returnData->kdr = $data->deaths > 0 ? round($data->kills / $data->deaths, 2) : $data->kills;
        $returnData->kda = $data->deaths > 0 ? round($data->takedowns / $data->deaths, 2) : $data->takedowns;
        $returnData->account_level = $data->account_level;
        $returnData->win_rate = ($data->wins + $data->losses) > 0 ? round(($data->wins / ($data->wins + $data->losses)) * 100, 2) : 0;
        $returnData->bruiser_win_rate = ($data->bruiser_wins + $data->bruiser_losses) > 0 ? round(($data->bruiser_wins / ($data->bruiser_wins + $data->bruiser_losses)) * 100, 2) : 0;
        $returnData->support_win_rate = ($data->support_wins + $data->support_losses) > 0 ? round(($data->support_wins / ($data->support_wins + $data->support_losses)) * 100, 2) : 0;
        $returnData->ranged_assassin_win_rate = ($data->ranged_assassin_wins + $data->ranged_assassin_losses) > 0 ? round(($data->ranged_assassin_wins / ($data->ranged_assassin_wins + $data->ranged_assassin_losses)) * 100, 2) : 0;
        $returnData->melee_assassin_win_rate = ($data->melee_assassin_wins + $data->melee_assassin_losses) > 0 ? round(($data->melee_assassin_wins / ($data->melee_assassin_wins + $data->melee_assassin_losses)) * 100, 2) : 0;
        $returnData->healer_win_rate = ($data->healer_wins + $data->healer_losses) > 0 ? round(($data->healer_wins / ($data->healer_wins + $data->healer_losses)) * 100, 2) : 0;
        $returnData->tank_win_rate = ($data->tank_wins + $data->tank_losses) > 0 ? round(($data->tank_wins / ($data->tank_wins + $data->tank_losses)) * 100, 2) : 0;
        $returnData->mvp_rate = $data->mvp_games > 0 ? round(($data->games_mvp / $data->mvp_games) * 100, 2) : 0;

        $totalSeconds = $data->total_time_played;
        $days = floor($totalSeconds / (3600 * 24));
        $hours = floor(($totalSeconds % (3600 * 24)) / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $returnData->total_time_played = "{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds";

        if ($data->time_on_fire_games > 0) {
            $averageTimeOnFireInSeconds = $data->time_on_fire_total / $data->time_on_fire_games;

            $minutes = floor($averageTimeOnFireInSeconds / 60);
            $seconds = $averageTimeOnFireInSeconds % 60;

            $returnData->average_time_on_fire = "{$minutes} minutes, {$seconds} seconds";
        } else {
            $returnData->average_time_on_fire = "0 minutes, 0 seconds";
        }

        //There is an issue with this
        $hero_data = collect(json_decode($data->hero_data, true));
        $top_three_win_rate_heroes = null;
        $gamePlayedThresholds = [20, 15, 10, 5, 0];


        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');


        foreach ($gamePlayedThresholds as $threshold) {
            $filtered_hero_data = $hero_data->filter(function ($item) use ($threshold) {
                return $item['games_played'] >= $threshold;
            });

            $top_three_win_rate_heroes = $filtered_hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag) {
                if ($item['games_played'] > 0) {
                    $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
                } else {
                    $item['win_rate'] = 0;
                }
                $item['hero_id'] = $key;
                $item['hero'] = $heroData[$key];
                $item['blizz_id'] = $blizz_id;
                $item['region'] = $region;
                $item['battletag'] = $battletag;
                $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";

                return $item;
            })->sortByDesc('win_rate')->take(3)->values()->all();

            if (count($top_three_win_rate_heroes) >= 3) {
                break;
            }
        }

        $returnData->heroes_three_highest_win_rate = $top_three_win_rate_heroes;


        $top_three_most_played_heroes = $hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag){
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['hero_id'] = $key;
            $item['hero'] = $heroData[$key];
            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";
            return $item;
        })->sortByDesc('games_played')->take(3)->values()->all();

        $returnData->heroes_three_most_played = $top_three_most_played_heroes;

        $top_three_latest_played_heroes = $hero_data->map(function ($item, $key) use ($heroData, $blizz_id, $region, $battletag) {
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['hero_id'] = $key;
            $item['hero'] = $heroData[$key];
            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";

            return $item;
        })->sortByDesc('game_date')->take(3)->values()->all();

        $returnData->heroes_three_latest_played = $top_three_latest_played_heroes;

        $type = MMRTypeID::select("mmr_type_id")->filterByName("player")->first()->mmr_type_id;

        $qm_mmr_data = MasterMMRDataQM::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(1)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($qm_mmr_data){
            $qm_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(1, 10000), $qm_mmr_data->mmr);
        }

        $ud_mmr_data = MasterMMRDataUD::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(2)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($ud_mmr_data){
            $ud_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(2, 10000), $ud_mmr_data->mmr);
        }

        $hl_mmr_data = MasterMMRDataHL::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(3)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($hl_mmr_data){
            $hl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(3, 10000), $hl_mmr_data->mmr);
        }

        $tl_mmr_data = MasterMMRDataTL::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(4)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($tl_mmr_data){
            $tl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(4, 10000), $tl_mmr_data->mmr);
        }

        $sl_mmr_data = MasterMMRDataSL::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(5)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($sl_mmr_data){
            $sl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(5, 10000), $sl_mmr_data->mmr);
        }
        $ar_mmr_data = MasterMMRDataAR::select("conservative_rating", "win", "loss")->filterByType(10000)->filterByGametype(6)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
        if($ar_mmr_data){
            $ar_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(6, 10000), $ar_mmr_data->mmr);
        }

        $returnData->qm_mmr_data = $qm_mmr_data;
        $returnData->ud_mmr_data = $ud_mmr_data;
        $returnData->hl_mmr_data = $hl_mmr_data;
        $returnData->tl_mmr_data = $tl_mmr_data;
        $returnData->sl_mmr_data = $sl_mmr_data;
        $returnData->ar_mmr_data = $ar_mmr_data;


        $map_data = collect(json_decode($data->map_data, true));
        $top_three_win_rate_maps = null;

        foreach ($gamePlayedThresholds as $threshold) {
            $filtered_map_data = $map_data->filter(function ($item) use ($threshold) {
                return $item['games_played'] >= $threshold;
            });

            $top_three_win_rate_maps = $filtered_map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag){
                if ($item['games_played'] > 0) {
                    $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
                } else {
                    $item['win_rate'] = 0;
                }
                $item['map_id'] = $key;
                $item['game_map'] = $maps[$key];

                $item['blizz_id'] = $blizz_id;
                $item['region'] = $region;
                $item['battletag'] = $battletag;
                $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";

                return $item;
            })->sortByDesc('win_rate')->take(3)->values()->all();

            if (count($top_three_win_rate_maps) >= 3) {
                break;
            }
        }

        $returnData->maps_three_highest_win_rate = $top_three_win_rate_maps;


        $top_three_most_played_maps = $map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag){
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['map_id'] = $key;
            $item['game_map'] = $maps[$key];
            
            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";

            return $item;
        })->sortByDesc('games_played')->take(3)->values()->all();

        $returnData->maps_three_most_played = $top_three_most_played_maps;

        $top_three_latest_played_maps = $map_data->map(function ($item, $key) use ($maps, $blizz_id, $region, $battletag){
            if ($item['games_played'] > 0) {
                $item['win_rate'] = round(($item['wins'] / $item['games_played']) * 100, 2);
            } else {
                $item['win_rate'] = 0;
            }

            $item['map_id'] = $key;
            $item['game_map'] = $maps[$key];

            $item['blizz_id'] = $blizz_id;
            $item['region'] = $region;
            $item['battletag'] = $battletag;
            $item['hovertext'] = $item['win_rate'] . "% win rate over " . $item['games_played'] . " games";

            return $item;
        })->sortByDesc('game_date')->take(3)->values()->all();

        $returnData->maps_three_latest_played = $top_three_latest_played_maps;


        $returnData->stack_one_wins = $data->stack_one_wins;
        $returnData->stack_one_losses = $data->stack_one_losses;
        $returnData->stack_one_win_rate = ($data->stack_one_wins + $data->stack_one_losses) > 0 ? round(($data->stack_one_wins / ($data->stack_one_wins + $data->stack_one_losses)) * 100, 2) : 0;
        
        $returnData->stack_two_wins = $data->stack_two_wins;
        $returnData->stack_two_losses = $data->stack_two_losses;
        $returnData->stack_two_win_rate = ($data->stack_two_wins + $data->stack_two_losses) > 0 ? round(($data->stack_two_wins / ($data->stack_two_wins + $data->stack_two_losses)) * 100, 2) : 0;

        $returnData->stack_three_wins = $data->stack_three_wins;
        $returnData->stack_three_losses = $data->stack_three_losses;
        $returnData->stack_three_win_rate = ($data->stack_three_wins + $data->stack_three_losses) > 0 ? round(($data->stack_three_wins / ($data->stack_three_wins + $data->stack_three_losses)) * 100, 2) : 0;

        $returnData->stack_four_wins = $data->stack_four_wins;
        $returnData->stack_four_losses = $data->stack_four_losses;
        $returnData->stack_four_win_rate = ($data->stack_four_wins + $data->stack_four_losses) > 0 ? round(($data->stack_four_wins / ($data->stack_four_wins + $data->stack_four_losses)) * 100, 2) : 0;

        $returnData->stack_five_wins = $data->stack_five_wins;
        $returnData->stack_five_losses = $data->stack_five_losses;
        $returnData->stack_five_win_rate = ($data->stack_five_wins + $data->stack_five_losses) > 0 ? round(($data->stack_five_wins / ($data->stack_five_wins + $data->stack_five_losses)) * 100, 2) : 0;

  
        $matches = collect(json_decode($data->matches, true));

        $returnData->matchData = $matches->sortByDesc('game_date')->map(function($match) use ($maps, $heroData, $talentData){
            $match["game_type"] = $this->globalDataService->getGameTypeIDtoString()[$match["game_type"]];
            $match["game_map"] = $maps[$match["game_map"]];
            $match['hero'] = $heroData[$match['hero']];
            $match['level_one'] = $match['level_one'] ? $talentData[$match['level_one']] : null;
            $match['level_four'] = $match['level_four'] ? $talentData[$match['level_four']] : null;
            $match['level_seven'] = $match['level_seven'] ? $talentData[$match['level_seven']] : null;
            $match['level_ten'] = $match['level_ten'] ? $talentData[$match['level_ten']] : null;
            $match['level_thirteen'] = $match['level_thirteen'] ? $talentData[$match['level_thirteen']] : null;
            $match['level_sixteen'] = $match['level_sixteen'] ? $talentData[$match['level_sixteen']] : null;
            $match['level_twenty'] = $match['level_twenty'] ? $talentData[$match['level_twenty']] : null;
            
            $match['player_conservative_rating'] = round($match['player_conservative_rating'], 2);
            $match['player_change'] = round($match['player_change'], 2);
            $match['hero_conservative_rating'] = round($match['hero_conservative_rating'], 2);
            $match['hero_change'] = round($match['hero_change'], 2);
            $match['role_conservative_rating'] = round($match['role_conservative_rating'], 2);
            $match['role_change'] = round($match['role_change'], 2);

            return $match;
        })->values();

        return $returnData;
    }
}

