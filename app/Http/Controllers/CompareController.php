<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\SeasonDate;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompareController extends Controller
{
    public function show(Request $request, $hero = null)
    {
        if (! is_null($hero)) {
            $validationRules = [
                'hero' => ['required', new HeroInputValidation()],
            ];

            $validator = Validator::make(['hero' => $hero], $validationRules);

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
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('compare')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'userinput' => $userinput,
                'filters' => $this->globalDataService->getFilterData(),
                'gametypedefault' => $this->globalDataService->getGameTypeDefault('single'),
            ]);
    }

    public function getData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'hero' => ['required', new HeroInputValidation()],
            'game_type' => ['required', new GameTypeInputValidation()],
            'season' => ['required', new SeasonInputValidation()],
        ];

        $players = [
            'player1', 'player2', 'player3', 'player4', 'player5',
        ];

        foreach ($players as $player) {
            if (! is_null($request[$player])) {
                $validationRules[$player.'.battletag'] = 'required|string';
                $validationRules[$player.'.blizz_id'] = 'required|integer';
                $validationRules[$player.'.region'] = 'required|integer';
            }
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id;
        $game_type = GameType::where('short_name', $request['game_type'])->pluck('type_id')->first();

        $season = $request['season'];
        $seasonDate = null;
        if ($season != 'All') {
            $seasonDate = SeasonDate::find($season);
        }

        $returnData = [];
        $maxValues = [];
        foreach ($players as $player) {
            if (! is_null($request[$player])) {

                $result = DB::table('replay')
                    ->join('player', 'player.replayID', '=', 'replay.replayID')
                    ->join('scores', function ($join) {
                        $join->on('scores.replayID', '=', 'replay.replayID')
                            ->on('scores.battletag', '=', 'player.battletag');
                    })
                    ->select([
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
                    ->where('blizz_id', $request[$player]['blizz_id'])
                    ->where('region', $request[$player]['region'])
                    ->where('game_type', $game_type)
                    ->where('hero', $hero)
                    ->when($season != 'All', function ($query) use ($seasonDate) {
                        return $query->where('game_date', '>=', $seasonDate->start_date)->where('game_date', '<', $seasonDate->end_date);
                    })
                    //->toSql();
                    ->get();

                $statistics = [
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
                ];

                $columnStatistics = [];
                foreach ($statistics as $column) {

                    $winCount = $result->where('winner', 1)->count();
                    $lossCount = $result->where('winner', 0)->count();

                    $gamesPlayed = $winCount + $lossCount;

                    $winRate = $gamesPlayed > 0 ? ($winCount / $gamesPlayed) * 100 : 0;

                    $averageValue = $result->avg($column);

                    $maxValue = $averageValue;

                    if ($maxValue > ($maxValues[$column] ?? 0)) {
                        $maxValues[$column] = round($maxValue, 2);
                    }

                    $columnStatistics[$column] = [
                        'avg_value' => round($averageValue, 2),
                    ];
                }
                $returnData[$request[$player]['battletag']] = [
                    'battletag_short' => $request[$player]['battletag_short'],
                    'blizz_id' => $request[$player]['blizz_id'],
                    'region' => $request[$player]['region'],
                    'region_name' => $request[$player]['region_name'],
                    'wins' => $winCount,
                    'losses' => $lossCount,
                    'games_played' => $gamesPlayed,
                    'win_rate' => $winRate,
                    'averages' => $columnStatistics,
                ];
            }
        }
        foreach ($returnData as &$playerData) {
            foreach ($playerData['averages'] as $stat => &$statData) {
                $maxValue = $maxValues[$stat] ?? 0;
                $averageValue = $statData['avg_value'];

                $scaledValue = $maxValue > 0 ? ($averageValue / $maxValue) * 100 : 0;

                $statData['scaled_value'] = round($scaledValue, 2);
            }
        }

        return $returnData;
    }
}
