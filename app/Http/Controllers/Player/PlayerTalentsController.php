<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Models\SeasonDate;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\SeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerTalentsController extends Controller
{
    public function show(Request $request, $battletag, $blizz_id, $region)
    {

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        if (request()->has('hero')) {
            $validationRules['hero'] = ['sometimes', 'nullable', new HeroInputValidation()];
        }

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region'),
                'status' => 'failure to validate inputs',
            ];
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Player.talentData')->with([
            'userinput' => $userinput,
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
        ]);
    }

    public function getPlayerTalentData(Request $request)
    {
        return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['required', new GameTypeInputValidation()],
            'hero' => ['required', new HeroInputValidation()],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation()],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation()],
            //'fromdate' => ['sometimes', 'nullable', new DateInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $battletag = $request['battletag'];
        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $hero = session('heroes')->keyBy('name')[$request['hero']]->id;
        $season = $request['season'];
        $game_map = $request['game_map'] ? Map::whereIn('name', $request['game_map'])->pluck('map_id')->toArray() : null;
        $fromdate = $request['fromdate'];

        $result = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->select([
                'player.winner AS winner',
                'player.hero AS hero',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
            ])
            ->where('blizz_id', $blizz_id)
            ->where('hero', $hero)
            ->whereIn('game_type', $gameType)
            ->where('region', $region)
            ->when(! is_null($season), function ($query) use ($season) {
                $seasonDate = SeasonDate::find($season);
                if ($seasonDate) {
                    return $query->where('game_date', '>=', $seasonDate->start_date)
                        ->where('game_date', '<', $seasonDate->end_date);
                }

                return $query;
            })
            ->when(! is_null($game_map), function ($query) use ($game_map) {
                return $query->whereIn('game_map', $game_map);
            })
            ->when(! is_null($fromdate), function ($query) use ($fromdate) {
                return $query->where('game_date', '>=', $fromdate);
            })
            //->toSql();
            ->get();
        $talentData = HeroesDataTalent::all();
        $talentData = $talentData->keyBy('talent_id');

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        return [
            'talentData' => $this->getHeroTalentData($result, $talentData),
            'buildData' => $this->getHeroTalentBuildData($result, $heroData[$hero], $talentData),
        ];
    }

    private function getHeroTalentData($result, $talentData)
    {
        $returnData = [];
        $levelKeys = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        $resultCollection = collect($result);

        $resultCollection->each(function ($data) use (&$returnData, $levelKeys, $talentData) {
            foreach ($levelKeys as $levelKey) {
                if ($data->$levelKey == 0 || ! $talentData->has($data->$levelKey)) {
                    continue;
                }

                $key = $data->$levelKey;

                if (! isset($returnData[$key])) {
                    $returnData[$key] = ['wins' => 0, 'losses' => 0];
                }

                $returnData[$key][$data->winner == 1 ? 'wins' : 'losses']++;
                $returnData[$key]['talent'] = $talentData[$data->$levelKey];
            }
        });

        $formattedData = [];

        $levelTotals = [];

        foreach ($returnData as $data) {
            if (! array_key_exists($data['talent']['level'], $levelTotals)) {
                $levelTotals[$data['talent']['level']] = 0;
            }
            $levelTotals[$data['talent']['level']] += $data['wins'] + $data['losses'];
        }

        foreach ($returnData as $data) {
            $level = $data['talent']['level'];
            $sort = ($data['talent']['sort'] - 1);

            if (! array_key_exists($level, $formattedData)) {
                $formattedData[$level] = [];
            }

            if (! array_key_exists($sort, $formattedData[$level])) {
                $formattedData[$level][$sort] = [];
            }

            $formattedData[$level][$sort] = [
                'win_rate' => ($data['wins'] + $data['losses']) > 0 ? round(($data['wins'] / ($data['wins'] + $data['losses'])) * 100, 2) : 0,
                'wins' => $data['wins'],
                'losses' => $data['losses'],
                'sort' => $data['talent']['sort'],
                'popularity' => round((($data['losses'] + $data['wins']) / $levelTotals[$data['talent']['level']]) * 100, 2),
                'games_played' => $data['wins'] + $data['losses'],
                'talentInfo' => $data['talent'],
            ];

        }

        return $formattedData;
    }

    private function getHeroTalentBuildData($result, $hero, $talentData)
    {
        $returnData = [];

        foreach ($result as $replay) {
            $level_one = $replay->level_one;
            $level_four = $replay->level_four;
            $level_seven = $replay->level_seven;
            $level_ten = $replay->level_ten;
            $level_thirteen = $replay->level_thirteen;
            $level_sixteen = $replay->level_sixteen;
            $level_twenty = $replay->level_twenty;

            if ($level_one == 0 || $level_four == 0 || $level_seven == 0 || $level_ten == 0 || $level_thirteen == 0 || $level_sixteen == 0 || $level_twenty == 0) {
                continue;
            } elseif (! $talentData->has($level_one) || ! $talentData->has($level_four) || ! $talentData->has($level_seven) || ! $talentData->has($level_ten) || ! $talentData->has($level_thirteen) || ! $talentData->has($level_sixteen) || ! $talentData->has($level_twenty)) {
                continue;
            }
            $key = $level_one.'|'.$level_four.'|'.$level_seven.'|'.$level_ten.'|'.$level_thirteen.'|'.$level_sixteen.'|'.$level_twenty;
            if (! array_key_exists($key, $returnData)) {
                $returnData[$key] = [];
                $returnData[$key]['wins'] = 0;
                $returnData[$key]['losses'] = 0;
                $returnData[$key]['games_played'] = 0;

                $returnData[$key]['level_one'] = $replay->level_one;
                $returnData[$key]['level_four'] = $replay->level_four;
                $returnData[$key]['level_seven'] = $replay->level_seven;
                $returnData[$key]['level_ten'] = $replay->level_ten;
                $returnData[$key]['level_thirteen'] = $replay->level_thirteen;
                $returnData[$key]['level_sixteen'] = $replay->level_sixteen;
                $returnData[$key]['level_twenty'] = $replay->level_twenty;
            }

            if ($replay->winner == 1) {
                $returnData[$key]['wins']++;
            } else {
                $returnData[$key]['losses']++;
            }
            $returnData[$key]['games_played']++;

        }

        usort($returnData, function ($a, $b) {
            return $b['games_played'] - $a['games_played'];
        });

        $returnData = array_slice($returnData, 0, 7);

        foreach ($returnData as &$data) {
            foreach ($result as $replay) {
                $level_one = $replay->level_one;
                $level_four = $replay->level_four;
                $level_seven = $replay->level_seven;
                $level_ten = $replay->level_ten;
                $level_thirteen = $replay->level_thirteen;
                $level_sixteen = $replay->level_sixteen;
                $level_twenty = $replay->level_twenty;

                if ($data['level_one'] != $level_one || $data['level_four'] != $level_four || $data['level_seven'] != $level_seven || $data['level_ten'] != $level_ten || $level_twenty != 0) {
                    continue;
                }

                if ($replay->winner == 1) {
                    $data['wins']++;

                } else {
                    $data['losses']++;
                }
                $data['games_played']++;
            }
        }
        foreach ($returnData as &$data) {
            $data['level_one'] = $talentData[$data['level_one']];
            $data['level_four'] = $talentData[$data['level_four']];
            $data['level_seven'] = $talentData[$data['level_seven']];
            $data['level_ten'] = $talentData[$data['level_ten']];
            $data['level_thirteen'] = $talentData[$data['level_thirteen']];
            $data['level_sixteen'] = $talentData[$data['level_sixteen']];
            $data['level_twenty'] = $talentData[$data['level_twenty']];
            $data['win_rate'] = round(($data['wins'] / $data['games_played']) * 100, 2);
            $data['hero'] = $hero;
        }

        return $returnData;
    }
}
