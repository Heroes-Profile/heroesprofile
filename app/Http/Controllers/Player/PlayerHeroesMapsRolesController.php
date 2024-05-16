<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataUD;
use App\Models\MMRTypeID;
use App\Models\Replay;
use App\Models\SeasonDate;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerHeroesMapsRolesController extends Controller
{
    public function getData(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'minimumgames' => 'integer',
            'type' => 'required|in:all,single',
            'page' => 'required|in:hero,map,role',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation()],
            'role' => ['sometimes', 'nullable', new RoleInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

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

        $battletag = $request['battletag'];
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $type = $request['type'];

        if ($type == 'all') {
            $game_type = $request['game_type'] ? GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray() : null;
        } else {
            $game_type = $request['game_type'] ? GameType::where('short_name', $request['game_type'])->pluck('type_id')->first() : null;
        }

        $hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $minimum_games = $request['minimumgames'];
        $page = $request['page'];
        $role = $request['role'];
        $game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->first() : null;
        $season = $request['season'];

        $result = Replay::join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->join('heroes', 'heroes.id', '=', 'player.hero')
            ->where('region', $region)
            ->where(function ($query) use ($game_type, $type) {
                if (is_null($game_type)) {
                    $query->whereNot('game_type', 0);
                } else {
                    if ($type == 'all') {
                        $query->whereIn('game_type', $game_type);
                    } else {
                        $query->where('game_type', $game_type);
                    }
                }
            })
            ->where('blizz_id', $blizz_id)
            ->when($type == 'single' && $page == 'hero', function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when($type == 'single' && $page == 'role', function ($query) use ($role) {
                return $query->where('new_role', $role);
            })
            ->when($type == 'single' && $page == 'map', function ($query) use ($game_map) {
                return $query->where('game_map', $game_map);
            })
            ->when(! is_null($season), function ($query) use ($season) {
                $seasonDate = SeasonDate::find($season);
                if ($seasonDate) {
                    return $query->where('game_date', '>=', $seasonDate->start_date)
                        ->where('game_date', '<', $seasonDate->end_date);
                }

                return $query;
            })
            ->select([
                'replay.replayID',
                'hero',
                'hero_level',
                'game_date',
                'game_map',
                'game_type',
                'stack_size',
                'mastery_taunt',
                'new_role',
                'player_conservative_rating',
                'player_change',
                'hero_conservative_rating',
                'hero_change',
                'role_conservative_rating',
                'role_change',
                'winner',
                'kills',
                'assists',
                'takedowns',
                'deaths',
                'highest_kill_streak',
                'hero_damage',
                'siege_damage',
                'structure_damage',
                'minion_damage',
                'creep_damage',
                'summon_damage',
                'time_cc_enemy_heroes',
                'healing',
                'self_healing',
                'damage_taken',
                'experience_contribution',
                'town_kills',
                'time_spent_dead',
                'merc_camp_captures',
                'watch_tower_captures',
                'meta_experience',
                'match_award',
                'protection_allies',
                'silencing_enemies',
                'rooting_enemies',
                'stunning_enemies',
                'clutch_heals',
                'escapes',
                'vengeance',
                'outnumbered_deaths',
                'teamfight_escapes',
                'teamfight_healing',
                'teamfight_damage_taken',
                'teamfight_hero_damage',
                'multikill',
                'physical_damage',
                'spell_damage',
                'regen_globes',
                'first_to_ten',
                'time_on_fire',
                'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty',
            ])
                //->toSql();
            ->get();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $gameTypes = $this->globalDataService->getGameTypeIDtoString();

        $qm_mmr_data = null;
        $ud_mmr_data = null;
        $hl_mmr_data = null;
        $tl_mmr_data = null;
        $sl_mmr_data = null;
        $ar_mmr_data = null;

        $seasonData = $this->globalDataService->getSeasonsData();
        $newSeasonData = null;
        $latestGames = null;
        $mapWinRateFiltered = null;
        $heroWinRateFiltered = null;

        $mapData = null;
        $heroDataStats = null;

        if ($type == 'single') {
            $mapData = $result->groupBy('game_map');
            $heroDataStats = $result->groupBy('hero');

            $filterByType = $hero;
            if ($page == 'role') {
                $filterByType = MMRTypeID::select('mmr_type_id')->filterByName($role)->first()->mmr_type_id;
            }

            $qm_mmr_data = MasterMMRDataQM::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(1)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
            if ($qm_mmr_data) {
                $qm_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(1, $filterByType), $qm_mmr_data->mmr);
            }

            $ud_mmr_data = MasterMMRDataUD::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(2)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
            if ($ud_mmr_data) {
                $ud_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(2, $filterByType), $ud_mmr_data->mmr);
            }

            $hl_mmr_data = MasterMMRDataHL::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(3)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
            if ($hl_mmr_data) {
                $hl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(3, $filterByType), $hl_mmr_data->mmr);
            }

            $tl_mmr_data = MasterMMRDataTL::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(4)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
            if ($tl_mmr_data) {
                $tl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(4, $filterByType), $tl_mmr_data->mmr);
            }

            $sl_mmr_data = MasterMMRDataSL::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(5)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();
            if ($sl_mmr_data) {
                $sl_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(5, $filterByType), $sl_mmr_data->mmr);
            }
            $ar_mmr_data = MasterMMRDataAR::select('conservative_rating', 'win', 'loss')->filterByType($filterByType)->filterByGametype(6)->filterByBlizzID($blizz_id)->filterByRegion($region)->first();

            if ($ar_mmr_data) {
                $ar_mmr_data->rank_tier = $this->globalDataService->calculateSubTier($this->globalDataService->getRankTiers(6, $filterByType), $ar_mmr_data->mmr);
            }

            $latestGames = $result->sortByDesc('game_date')->take(5)->values();

            $latestGames = $latestGames->map(function ($game) use ($heroData, $maps, $talentData, $gameTypes) {
                $game = clone $game;
                unset(
                    $game['hero_level'],
                    $game['stack_size'],
                    $game['mastery_taunt'],
                    $game['kills'],
                    $game['assists'],
                    $game['takedowns'],
                    $game['deaths'],
                    $game['highest_kill_streak'],
                    $game['hero_damage'],
                    $game['siege_damage'],
                    $game['structure_damage'],
                    $game['minion_damage'],
                    $game['creep_damage'],
                    $game['summon_damage'],
                    $game['time_cc_enemy_heroes'],
                    $game['healing'],
                    $game['self_healing'],
                    $game['damage_taken'],
                    $game['experience_contribution'],
                    $game['town_kills'],
                    $game['time_spent_dead'],
                    $game['merc_camp_captures'],
                    $game['watch_tower_captures'],
                    $game['meta_experience'],
                    $game['match_award'],
                    $game['protection_allies'],
                    $game['silencing_enemies'],
                    $game['rooting_enemies'],
                    $game['stunning_enemies'],
                    $game['clutch_heals'],
                    $game['escapes'],
                    $game['vengeance'],
                    $game['outnumbered_deaths'],
                    $game['teamfight_escapes'],
                    $game['teamfight_healing'],
                    $game['teamfight_damage_taken'],
                    $game['teamfight_hero_damage'],
                    $game['multikill'],
                    $game['physical_damage'],
                    $game['spell_damage'],
                    $game['regen_globes'],
                    $game['first_to_ten'],
                    $game['time_on_fire'],
                );

                $game->game_type = $gameTypes[$game->game_type];
                $game->hero = $heroData[$game->hero];
                $game->game_map = $maps[$game->game_map];
                $game->level_one = $game->level_one && isset($talentData[$game->level_one]) ? $talentData[$game->level_one] : null;
                $game->level_four = $game->level_four && isset($talentData[$game->level_four]) ? $talentData[$game->level_four] : null;
                $game->level_seven = $game->level_seven && isset($talentData[$game->level_seven]) ? $talentData[$game->level_seven] : null;
                $game->level_ten = $game->level_ten && isset($talentData[$game->level_ten]) ? $talentData[$game->level_ten] : null;
                $game->level_thirteen = $game->level_thirteen && isset($talentData[$game->level_thirteen]) ? $talentData[$game->level_thirteen] : null;
                $game->level_sixteen = $game->level_sixteen && isset($talentData[$game->level_sixteen]) ? $talentData[$game->level_sixteen] : null;
                $game->level_twenty = $game->level_twenty && isset($talentData[$game->level_twenty]) ? $talentData[$game->level_twenty] : null;

                $game->player_mmr = round(1800 + 40 * $game->player_conservative_rating);
                $game->hero_mmr = round(1800 + 40 * $game->hero_conservative_rating);
                $game->role_mmr = round(1800 + 40 * $game->role_conservative_rating);

                $game->player_change = round($game->player_change, 2);
                $game->hero_change = round($game->hero_change, 2);
                $game->role_change = round($game->role_change, 2);

                return $game;
            });

            $newSeasonData = $result->map(function ($item) use ($seasonData) {
                $gameDate = $item->game_date;
                $season = null;
                $id = null;

                foreach ($seasonData as $seasonItem) {
                    $startDate = $seasonItem->start_date;
                    $endDate = $seasonItem->end_date;

                    if ($gameDate >= $startDate && $gameDate <= $endDate) {
                        $season = $seasonItem->season;
                        $id = $seasonItem->id;
                        break;
                    }
                }

                $seasonData = [
                    'id' => $id,
                    'season' => $season,
                    'year' => $seasonItem->year,
                    'wins' => $item->winner === 1 ? 1 : 0,
                    'losses' => $item->winner === 0 ? 1 : 0,
                    'games_played' => 1,
                ];

                return $seasonData;
            });

            $newSeasonData = $newSeasonData->groupBy('id')->map(function ($group) {
                $wins = $group->sum('wins');
                $losses = $group->sum('losses');
                $gamesPlayed = $group->sum('games_played');

                $winRate = ($wins / $gamesPlayed) * 100;

                return [
                    'id' => $group->first()['id'],
                    'x_label' => $group->first()['year'].' Season '.$group->first()['season'],
                    'season' => $group->first()['season'],
                    'year' => $group->first()['year'],
                    'wins' => $wins,
                    'losses' => $losses,
                    'games_played' => $gamesPlayed,
                    'win_rate' => round($winRate, 2),
                ];
            })->sortBy('id');

            $mapData = $mapData->map(function ($games, $map) use ($battletag, $blizz_id, $region, $maps) {
                $gamesPlayed = $games->count();
                $wins = $games->where('winner', 1)->count();
                $losses = $games->where('winner', 0)->count();
                $winRate = ($gamesPlayed > 0) ? ($wins / $gamesPlayed) * 100 : 0;
                $latestGameDate = $games->max('game_date');

                return [
                    'battletag' => $battletag,
                    'blizz_id' => $blizz_id,
                    'region' => $region,

                    'name' => $maps[$map]['name'],
                    'game_map' => $maps[$map],
                    'games_played' => $gamesPlayed,
                    'latest_game' => $latestGameDate,
                    'wins' => $wins,
                    'losses' => $losses,
                    'win_rate' => round($winRate, 2),
                ];

            });

            $mapWinRateFiltered = $mapData->filter(function ($data) {
                return $data['games_played'] > 5;
            });

            if ($mapWinRateFiltered->count() < 3) {
                $mapWinRateFiltered = $mapData->filter(function ($data) {
                    return $data['games_played'] > 2;
                });
            }

            if ($mapWinRateFiltered->count() < 3) {
                $mapWinRateFiltered = $mapData->filter(function ($data) {
                    return $data['games_played'] > 0;
                });
            }

            $heroDataStats = $heroDataStats->map(function ($games, $hero) use ($battletag, $blizz_id, $region, $heroData) {
                $gamesPlayed = $games->count();
                $wins = $games->where('winner', 1)->count();
                $losses = $games->where('winner', 0)->count();
                $winRate = ($gamesPlayed > 0) ? ($wins / $gamesPlayed) * 100 : 0;
                $latestGameDate = $games->max('game_date');

                return [
                    'battletag' => $battletag,
                    'blizz_id' => $blizz_id,
                    'region' => $region,

                    'name' => $heroData[$hero]['name'],
                    'hero' => $heroData[$hero],
                    'games_played' => $gamesPlayed,
                    'latest_game' => $latestGameDate,
                    'wins' => $wins,
                    'losses' => $losses,
                    'win_rate' => round($winRate, 2),
                ];

            });

            $heroWinRateFiltered = $heroDataStats->filter(function ($data) {
                return $data['games_played'] > 5;
            });

            if ($heroWinRateFiltered->count() < 3) {
                $heroWinRateFiltered = $heroDataStats->filter(function ($data) {
                    return $data['games_played'] > 2;
                });
            }

            if ($heroWinRateFiltered->count() < 3) {
                $heroWinRateFiltered = $heroDataStats->filter(function ($data) {
                    return $data['games_played'] > 0;
                });
            }

        } else {

            if (in_array(1, $game_type)) {
                $qm_mmr_data = MasterMMRDataQM::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_qm.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(1)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }

            if (in_array(2, $game_type)) {

                $ud_mmr_data = MasterMMRDataUD::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_ud.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(2)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }

            if (in_array(3, $game_type)) {

                $hl_mmr_data = MasterMMRDataHL::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_hl.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(3)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }

            if (in_array(4, $game_type)) {

                $tl_mmr_data = MasterMMRDataTL::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_tl.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(4)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }

            if (in_array(5, $game_type)) {

                $sl_mmr_data = MasterMMRDataSL::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_sl.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(5)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }

            if (in_array(6, $game_type)) {

                $ar_mmr_data = MasterMMRDataAR::select('name', 'conservative_rating', 'win', 'loss')
                    ->join('mmr_type_ids', 'mmr_type_ids.mmr_type_id', '=', 'master_mmr_data_ar.type_value')
                    ->whereIn('type_value', function ($query) {
                        $query->select('mmr_type_id')
                            ->from('heroesprofile.mmr_type_ids')
                            ->whereIn('name', function ($subQuery) {
                                $subQuery->select('name')
                                    ->from('heroesprofile.heroes');
                            });
                    })
                    ->filterByGametype(6)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
            }
        }

        $groupBy = '';
        if ($page == 'hero') {
            $groupBy = 'hero';
        } elseif ($page == 'map') {
            $groupBy = 'game_map';
        } elseif ($page == 'role') {
            $groupBy = 'new_role';
        }

        $returnData = $result->groupBy($groupBy)->map(function ($heroStats, $index) use ($type, $battletag, $blizz_id, $region, $heroData, $qm_mmr_data, $ud_mmr_data, $hl_mmr_data, $tl_mmr_data, $sl_mmr_data, $ar_mmr_data, $newSeasonData, $mapData, $mapWinRateFiltered, $latestGames, $page, $heroWinRateFiltered, $heroDataStats, $maps) {
            $deaths = $heroStats->sum('deaths');
            $avg_kills = $heroStats->avg('kills');
            $avg_takedowns = $heroStats->avg('takedowns');

            $wins = $heroStats->where('winner', 1)->count();
            $losses = $heroStats->where('winner', 0)->count();
            $games_played = $wins + $losses;

            $combined_healing = $heroStats->avg('healing') + $heroStats->avg('self_healing');

            $stack_size_one_wins = $heroStats->whereNotNull('stack_size')->where('stack_size', 0)->where('winner', 1)->count();
            $stack_size_one_losses = $heroStats->whereNotNull('stack_size')->where('stack_size', 0)->where('winner', 0)->count();
            $stack_size_one_total = $stack_size_one_wins + $stack_size_one_losses;

            $stack_size_two_wins = $heroStats->where('stack_size', 2)->where('winner', 1)->count();
            $stack_size_two_losses = $heroStats->where('stack_size', 2)->where('winner', 0)->count();
            $stack_size_two_total = $stack_size_two_wins + $stack_size_two_losses;

            $stack_size_three_wins = $heroStats->where('stack_size', 3)->where('winner', 1)->count();
            $stack_size_three_losses = $heroStats->where('stack_size', 3)->where('winner', 0)->count();
            $stack_size_three_total = $stack_size_three_wins + $stack_size_three_losses;

            $stack_size_four_wins = $heroStats->where('stack_size', 4)->where('winner', 1)->count();
            $stack_size_four_losses = $heroStats->where('stack_size', 4)->where('winner', 0)->count();
            $stack_size_four_total = $stack_size_four_wins + $stack_size_four_losses;

            $stack_size_five_wins = $heroStats->where('stack_size', 5)->where('winner', 1)->count();
            $stack_size_five_losses = $heroStats->where('stack_size', 5)->where('winner', 0)->count();
            $stack_size_five_total = $stack_size_five_wins + $stack_size_five_losses;

            if ($page == 'hero') {
                $name = $heroData[$heroStats->pluck('hero')->first()]['name'];
            } elseif ($page == 'map') {
                $name = $maps[$heroStats->pluck('game_map')->first()]['name'];
            } elseif ($page == 'role') {
                $name = $heroStats->pluck('new_role')->first();
            }

            return [
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,

                'name' => $name,
                'hero' => $heroData[$heroStats->pluck('hero')->first()],
                'game_map' => $maps[$heroStats->pluck('game_map')->first()],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $games_played,
                'kda' => $deaths > 0 ? round($heroStats->sum('takedowns') / $deaths, 2) : $heroStats->sum('takedowns'),
                'kdr' => $deaths > 0 ? round($heroStats->sum('kills') / $deaths, 2) : $heroStats->sum('kills'),
                'win_rate' => $games_played > 0 ? round(($wins / $games_played) * 100, 2) : 0,
                'qm_mmr_data' => $type == 'single' ? $qm_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $qm_mmr_data),
                'ud_mmr_data' => $type == 'single' ? $ud_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $ud_mmr_data),
                'hl_mmr_data' => $type == 'single' ? $hl_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $hl_mmr_data),
                'tl_mmr_data' => $type == 'single' ? $tl_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $tl_mmr_data),
                'sl_mmr_data' => $type == 'single' ? $sl_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $sl_mmr_data),
                'ar_mmr_data' => $type == 'single' ? $ar_mmr_data : $this->getHeroDataForAll($heroData[$heroStats->pluck('hero')->first()], $ar_mmr_data),

                'stack_size_one_wins' => $stack_size_one_wins,
                'stack_size_one_losses' => $stack_size_one_losses,
                'stack_size_one_total' => $stack_size_one_total,
                'stack_size_one_win_rate' => $stack_size_one_total > 0 ? round(($stack_size_one_wins / $stack_size_one_total) * 100, 2) : 0,

                'stack_size_two_wins' => $stack_size_two_wins,
                'stack_size_two_losses' => $stack_size_two_losses,
                'stack_size_two_total' => $stack_size_two_total,
                'stack_size_two_win_rate' => $stack_size_two_total > 0 ? round(($stack_size_two_wins / $stack_size_two_total) * 100, 2) : 0,

                'stack_size_three_wins' => $stack_size_three_wins,
                'stack_size_three_losses' => $stack_size_three_losses,
                'stack_size_three_total' => $stack_size_three_total,
                'stack_size_three_win_rate' => $stack_size_three_total > 0 ? round(($stack_size_three_wins / $stack_size_three_total) * 100, 2) : 0,

                'stack_size_four_wins' => $stack_size_four_wins,
                'stack_size_four_losses' => $stack_size_four_losses,
                'stack_size_four_total' => $stack_size_four_total,
                'stack_size_four_win_rate' => $stack_size_four_total > 0 ? round(($stack_size_four_wins / $stack_size_four_total) * 100, 2) : 0,

                'stack_size_five_wins' => $stack_size_five_wins,
                'stack_size_five_losses' => $stack_size_five_losses,
                'stack_size_five_total' => $stack_size_five_total,
                'stack_size_five_win_rate' => $stack_size_five_total > 0 ? round(($stack_size_five_wins / $stack_size_five_total) * 100, 2) : 0,

                'max_kills' => $heroStats->max('kills'),
                'max_assists' => $heroStats->max('assists'),
                'max_takedowns' => $heroStats->max('takedowns'),
                'max_deaths' => $heroStats->max('deaths'),
                'max_highest_kill_streak' => $heroStats->max('highest_kill_streak'),
                'max_hero_damage' => $heroStats->max('hero_damage'),
                'max_siege_damage' => $heroStats->max('siege_damage'),
                'max_structure_damage' => $heroStats->max('structure_damage'),
                'max_minion_damage' => $heroStats->max('minion_damage'),
                'max_creep_damage' => $heroStats->max('creep_damage'),
                'max_summon_damage' => $heroStats->max('summon_damage'),
                'max_time_cc_enemy_heroes' => $heroStats->max('time_cc_enemy_heroes'),
                'max_healing' => $heroStats->max('healing'),
                'max_self_healing' => $heroStats->max('self_healing'),
                'max_damage_taken' => $heroStats->max('damage_taken'),
                'max_experience_contribution' => $heroStats->max('experience_contribution'),
                'max_town_kills' => $heroStats->max('town_kills'),
                'max_time_spent_dead' => $heroStats->max('time_spent_dead'),
                'max_merc_camp_captures' => $heroStats->max('merc_camp_captures'),
                'max_watch_tower_captures' => $heroStats->max('watch_tower_captures'),
                'max_protection_allies' => $heroStats->max('protection_allies'),
                'max_silencing_enemies' => $heroStats->max('silencing_enemies'),
                'max_rooting_enemies' => $heroStats->max('rooting_enemies'),
                'max_stunning_enemies' => $heroStats->max('stunning_enemies'),
                'max_clutch_heals' => $heroStats->max('clutch_heals'),
                'max_escapes' => $heroStats->max('escapes'),
                'max_vengeance' => $heroStats->max('vengeance'),
                'max_outnumbered_deaths' => $heroStats->max('outnumbered_deaths'),
                'max_teamfight_escapes' => $heroStats->max('teamfight_escapes'),
                'max_teamfight_healing' => $heroStats->max('teamfight_healing'),
                'max_teamfight_damage_taken' => $heroStats->max('teamfight_damage_taken'),
                'max_teamfight_hero_damage' => $heroStats->max('teamfight_hero_damage'),
                'max_multikill' => $heroStats->max('multikill'),
                'max_physical_damage' => $heroStats->max('physical_damage'),
                'max_spell_damage' => $heroStats->max('spell_damage'),
                'max_regen_globes' => $heroStats->max('regen_globes'),
                'max_first_to_ten' => $heroStats->max('first_to_ten'),
                'max_time_on_fire' => $heroStats->max('time_on_fire'),

                'combined_healing' => round($combined_healing, 2),
                'avg_assists' => round($avg_takedowns - $avg_kills, 2),
                'avg_kills' => round($avg_kills, 2),
                'avg_assists' => round($heroStats->avg('assists'), 2),
                'avg_takedowns' => round($avg_takedowns, 2),
                'avg_deaths' => round($heroStats->avg('deaths'), 2),
                'avg_highest_kill_streak' => round($heroStats->avg('highest_kill_streak'), 2),
                'avg_hero_damage' => round($heroStats->avg('hero_damage'), 2),
                'avg_siege_damage' => round($heroStats->avg('siege_damage'), 2),
                'avg_structure_damage' => round($heroStats->avg('structure_damage'), 2),
                'avg_minion_damage' => round($heroStats->avg('minion_damage'), 2),
                'avg_creep_damage' => round($heroStats->avg('creep_damage'), 2),
                'avg_summon_damage' => round($heroStats->avg('summon_damage'), 2),
                'avg_time_cc_enemy_heroes' => round($heroStats->avg('time_cc_enemy_heroes'), 2),
                'avg_healing' => round($heroStats->avg('healing'), 2),
                'avg_self_healing' => round($heroStats->avg('self_healing'), 2),
                'avg_damage_taken' => round($heroStats->avg('damage_taken'), 2),
                'avg_experience_contribution' => round($heroStats->avg('experience_contribution'), 2),
                'avg_town_kills' => round($heroStats->avg('town_kills'), 2),
                'avg_time_spent_dead' => round($heroStats->avg('time_spent_dead'), 2),
                'avg_merc_camp_captures' => round($heroStats->avg('merc_camp_captures'), 2),
                'avg_watch_tower_captures' => round($heroStats->avg('watch_tower_captures'), 2),
                'avg_protection_allies' => round($heroStats->avg('protection_allies'), 2),
                'avg_silencing_enemies' => round($heroStats->avg('silencing_enemies'), 2),
                'avg_rooting_enemies' => round($heroStats->avg('rooting_enemies'), 2),
                'avg_stunning_enemies' => round($heroStats->avg('stunning_enemies'), 2),
                'avg_clutch_heals' => round($heroStats->avg('clutch_heals'), 2),
                'avg_escapes' => round($heroStats->avg('escapes'), 2),
                'avg_vengeance' => round($heroStats->avg('vengeance'), 2),
                'avg_outnumbered_deaths' => round($heroStats->avg('outnumbered_deaths'), 2),
                'avg_teamfight_escapes' => round($heroStats->avg('teamfight_escapes'), 2),
                'avg_teamfight_healing' => round($heroStats->avg('teamfight_healing'), 2),
                'avg_teamfight_damage_taken' => round($heroStats->avg('teamfight_damage_taken'), 2),
                'avg_teamfight_hero_damage' => round($heroStats->avg('teamfight_hero_damage'), 2),
                'avg_multikill' => round($heroStats->avg('multikill'), 2),
                'avg_physical_damage' => round($heroStats->avg('physical_damage'), 2),
                'avg_spell_damage' => round($heroStats->avg('spell_damage'), 2),
                'avg_regen_globes' => round($heroStats->avg('regen_globes'), 2),
                'avg_first_to_ten' => round($heroStats->avg('first_to_ten'), 2),
                'avg_time_on_fire' => round($heroStats->avg('time_on_fire'), 2),

                'sum_kills' => $heroStats->sum('kills'),
                'sum_assists' => $heroStats->sum('assists'),
                'sum_takedowns' => $heroStats->sum('takedowns'),
                'sum_deaths' => $heroStats->sum('deaths'),
                'sum_highest_kill_streak' => $heroStats->sum('highest_kill_streak'),
                'sum_hero_damage' => $heroStats->sum('hero_damage'),
                'sum_siege_damage' => $heroStats->sum('siege_damage'),
                'sum_structure_damage' => $heroStats->sum('structure_damage'),
                'sum_minion_damage' => $heroStats->sum('minion_damage'),
                'sum_creep_damage' => $heroStats->sum('creep_damage'),
                'sum_summon_damage' => $heroStats->sum('summon_damage'),
                'sum_time_cc_enemy_heroes' => $heroStats->sum('time_cc_enemy_heroes'),
                'sum_healing' => $heroStats->sum('healing'),
                'sum_self_healing' => $heroStats->sum('self_healing'),
                'sum_damage_taken' => $heroStats->sum('damage_taken'),
                'sum_experience_contribution' => $heroStats->sum('experience_contribution'),
                'sum_town_kills' => $heroStats->sum('town_kills'),
                'sum_time_spent_dead' => $heroStats->sum('time_spent_dead'),
                'sum_merc_camp_captures' => $heroStats->sum('merc_camp_captures'),
                'sum_watch_tower_captures' => $heroStats->sum('watch_tower_captures'),
                'sum_protection_allies' => $heroStats->sum('protection_allies'),
                'sum_silencing_enemies' => $heroStats->sum('silencing_enemies'),
                'sum_rooting_enemies' => $heroStats->sum('rooting_enemies'),
                'sum_stunning_enemies' => $heroStats->sum('stunning_enemies'),
                'sum_clutch_heals' => $heroStats->sum('clutch_heals'),
                'sum_escapes' => $heroStats->sum('escapes'),
                'sum_vengeance' => $heroStats->sum('vengeance'),
                'sum_outnumbered_deaths' => $heroStats->sum('outnumbered_deaths'),
                'sum_teamfight_escapes' => $heroStats->sum('teamfight_escapes'),
                'sum_teamfight_healing' => $heroStats->sum('teamfight_healing'),
                'sum_teamfight_damage_taken' => $heroStats->sum('teamfight_damage_taken'),
                'sum_teamfight_hero_damage' => $heroStats->sum('teamfight_hero_damage'),
                'sum_multikill' => $heroStats->sum('multikill'),
                'sum_physical_damage' => $heroStats->sum('physical_damage'),
                'sum_spell_damage' => $heroStats->sum('spell_damage'),
                'sum_regen_globes' => $heroStats->sum('regen_globes'),
                'sum_first_to_ten' => $heroStats->sum('first_to_ten'),
                'sum_time_on_fire' => $heroStats->sum('time_on_fire'),
                'season_win_rate_data' => $newSeasonData,

                'map_data' => $mapData ? $mapData->sortBy('name')->values() : null,
                'map_data_top_played' => $mapData ? $mapData->sortByDesc('games_played')->values() : null,
                'map_data_top_win_rate' => $mapWinRateFiltered ? $mapWinRateFiltered->sortByDesc('win_rate')->values() : null,
                'map_data_top_latest_played' => $mapData ? $mapData->sortByDesc('latest_game')->values() : null,

                'hero_data_top_played' => $heroDataStats ? $heroDataStats->sortByDesc('games_played')->values() : null,
                'hero_data_top_win_rate' => $heroWinRateFiltered ? $heroWinRateFiltered->sortByDesc('win_rate')->values() : null,
                'hero_data_top_latest_played' => $heroDataStats ? $heroDataStats->sortByDesc('latest_game')->values() : null,
                'hero_data_all_heroes' => $heroDataStats ? $heroDataStats->sortBy('name')->values() : null,

                'latestGames' => $latestGames,
            ];
        })->values()->sortBy('name')->values()->all();

        if ($minimum_games && $minimum_games > 0) {
            $filteredData = array_filter($returnData, function ($item) use ($minimum_games) {
                return $item['games_played'] >= $minimum_games;
            });

            $returnData = array_values($filteredData);
        }

        return $returnData;
    }

    public function getHeroDataForAll($hero, $data)
    {
        if (! is_null($data)) {
            $mmrDataset = $data->where('name', $hero->name)->first();
            if (! is_null($mmrDataset)) {
                return $data->where('name', $hero->name)->first()->mmr;
            }
        }

        return null;
    }

    public function findMatch(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
            'stat' => 'required|string',
            'value' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $battletag = $request['battletag'];
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $type = $request['type'];

        if ($type == 'all') {
            $game_type = $request['game_type'] ? GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray() : null;
        } else {
            $game_type = $request['game_type'] ? GameType::where('short_name', $request['game_type'])->pluck('type_id')->first() : null;
        }

        $hero = $request['hero'] ? $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id : null;
        $minimum_games = $request['minimumgames'];
        $page = $request['page'];
        $role = $request['role'];
        $game_map = $request['game_map'] ? Map::where('name', $request['game_map'])->pluck('map_id')->first() : null;
        $season = $request['season'];
        $stat = $request['stat'];
        $value = $request['value'];
        $stat = str_replace('max_', '', $stat);

        $result = Replay::join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('scores', function ($join) {
                $join->on('scores.replayID', '=', 'replay.replayID')
                    ->on('scores.battletag', '=', 'player.battletag');
            })
            ->where('region', $region)
            ->where(function ($query) use ($game_type, $type) {
                if (is_null($game_type)) {
                    $query->whereNot('game_type', 0);
                } else {
                    if ($type == 'all') {
                        $query->whereIn('game_type', $game_type);
                    } else {
                        $query->where('game_type', $game_type);
                    }
                }
            })
            ->where('blizz_id', $blizz_id)
            ->when($type == 'single' && $page == 'hero', function ($query) use ($hero) {
                return $query->where('hero', $hero);
            })
            ->when($type == 'single' && $page == 'role', function ($query) use ($role) {
                return $query->where('new_role', $role);
            })
            ->when($type == 'single' && $page == 'map', function ($query) use ($game_map) {
                return $query->where('game_map', $game_map);
            })
            ->when(! is_null($season), function ($query) use ($season) {
                $seasonDate = SeasonDate::find($season);
                if ($seasonDate) {
                    return $query->where('game_date', '>=', $seasonDate->start_date)
                        ->where('game_date', '<', $seasonDate->end_date);
                }

                return $query;
            })
            ->where('hero', $hero)
            ->where($stat, $value)
            ->select('replay.replayID')
            //->toSql();
            ->first();

        return $result->replayID;
    }
}
