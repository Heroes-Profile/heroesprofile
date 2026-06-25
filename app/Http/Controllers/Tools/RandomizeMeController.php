<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Global\GlobalsInputValidationController;
use App\Models\HeroesDataTalent;
use App\Models\Map;
use App\Models\Replay;
use App\Rules\HeroInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RandomizeMeController extends GlobalsInputValidationController
{
    public function show(Request $request)
    {
        return view('Tools.randomizeMe')
            ->with([
                'heroes' => $this->globalDataService->getHeroes(),
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            ]);
    }

    public function getRandomBuild(Request $request)
    {
        $validationRules = [
            'hero' => ['required', new HeroInputValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $heroName = $request['hero'];
        $heroId = $this->globalDataService->getHeroFilterValue($heroName);

        $rows = Replay::query()
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag')
                    ->where('talents.level_twenty', '!=', 0);
            })
            ->select(
                'talents.level_one', 'talents.level_four', 'talents.level_seven', 'talents.level_ten',
                'talents.level_thirteen', 'talents.level_sixteen', 'talents.level_twenty',
                'replay.game_type', 'replay.region', 'replay.game_map'
            )
            ->where('player.hero', $heroId)
            ->where('replay.game_type', '!=', 0)
            ->orderByDesc('replay.game_date')
            ->limit(1000)
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['build' => null]);
        }

        $talentData = HeroesDataTalent::all()->keyBy('talent_id');
        $gameTypeMap = $this->globalDataService->getGameTypeIDtoString();
        $regionMap = $this->globalDataService->getRegionIDtoString();
        $mapData = Map::all()->keyBy('map_id');

        $row = $rows->random();

        $build = [
            'level_one' => $row->level_one ? $talentData[$row->level_one] ?? null : null,
            'level_four' => $row->level_four ? $talentData[$row->level_four] ?? null : null,
            'level_seven' => $row->level_seven ? $talentData[$row->level_seven] ?? null : null,
            'level_ten' => $row->level_ten ? $talentData[$row->level_ten] ?? null : null,
            'level_thirteen' => $row->level_thirteen ? $talentData[$row->level_thirteen] ?? null : null,
            'level_sixteen' => $row->level_sixteen ? $talentData[$row->level_sixteen] ?? null : null,
            'level_twenty' => $row->level_twenty ? $talentData[$row->level_twenty] ?? null : null,
            'game_type' => $gameTypeMap[$row->game_type]->name ?? null,
            'region' => $regionMap[$row->region] ?? null,
            'game_map' => $mapData[$row->game_map]->name ?? null,
        ];

        return response()->json(['build' => $build]);
    }
}
