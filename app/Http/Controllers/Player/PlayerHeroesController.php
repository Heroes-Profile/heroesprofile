<?php

namespace App\Http\Controllers\Player;
use App\Services\GlobalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Rules\GameTypeInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;

use App\Models\Replay;

class PlayerHeroesController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function showAll(Request $request, $battletag, $blizz_id, $region)
    {
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.Heroes.allHeroesData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData()
                ]);

    }
    public function showSingle(Request $request, $battletag, $blizz_id, $region){
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.Heroes.singleHeroData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData()
                ]);
    }

    public function getHeroAllData(Request $request){
        //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'minimumgames' => 'integer',
        ]);

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);
        $role = (new RoleInputValidation())->passes('role', $request["role"]);
        $minimum_games = $request["minimumgames"];


        if(count($gameType) == 6){
            $gameType = [];
        }

        $result = Replay::join('player', 'player.replayID', '=', 'replay.replayID')
                ->join('scores', function($join) {
                    $join->on('scores.replayID', '=', 'replay.replayID')
                         ->on('scores.battletag', '=', 'player.battletag');
                })
                ->where('region', $region)
               ->when(!empty($gameType), function ($query) use ($gameType) {
                        return $query->whereIn("game_type", $gameType);
                    })
                ->where('blizz_id', $blizz_id)
                ->select([
                    'hero',
                    'hero_level',
                    'mastery_taunt',
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
                    ])
                ->get();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $returnData = $result->groupBy('hero')->map(function($heroStats) use ($heroData){
            $deaths = $heroStats->sum('deaths');
            $avg_kills = $heroStats->avg('kills');
            $avg_takedowns = $heroStats->avg('takedowns');

            $wins = $heroStats->where('winner', 1)->count();
            $losses = $heroStats->where('winner', 0)->count();
            $games_played = $wins + $losses;

            $combined_healing = $heroStats->avg('healing') + $heroStats->avg('self_healing');

            return [
                "name" => $heroData[$heroStats->pluck('hero')->first()]["name"],
                "hero" => $heroData[$heroStats->pluck('hero')->first()],
                'wins' => $wins,
                'losses' => $losses,
                'kda' => $deaths > 0 ? round($heroStats->sum('takedowns') / $deaths, 2) : $heroStats->sum('takedowns'),
                'kdr' => $deaths > 0 ? round($heroStats->sum('kills') / $deaths, 2) : $heroStats->sum('kills'),
                'win_rate' => $games_played > 0 ? round(($wins / $games_played) * 100, 2) : 0,


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
            ];
        })->values()->sortBy('name')->values()->all();
        return $returnData;
    }
}
