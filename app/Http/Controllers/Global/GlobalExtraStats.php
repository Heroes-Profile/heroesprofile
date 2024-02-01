<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RegionInputValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GlobalExtraStats extends Controller
{
    public function show(Request $request)
    {
        return view('Global.Extra.globalExtraStats')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'filters' => $this->globalDataService->getFilterData(),
            ]);
    }

    public function getAccountLevelStats(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes

        //return response()->json($request->all());

        $request->validate([
            'minimumaccountlevel' => 'required|integer',
            'xaxisincrements' => 'required|integer',
        ]);

        $region = (new RegionInputValidation())->passes('region', $request['region']);

        $cacheKey = 'GlobalExtraStatsAccountLevel|'.implode('|', [
            'minimumaccountlevel'.$request['minimumaccountlevel'],
            'xaxisincrements'.$request['xaxisincrements'],
            'region='.implode(',', $region),

        ]);

        $minimumaccountlevel = $request['minimumaccountlevel'];
        $xaxisincrements = $request['xaxisincrements'];
        //return $cacheKey;
        $cache_time = 0;
        $data = Cache::store('database')->remember($cacheKey, $cache_time, function () use ($minimumaccountlevel, $xaxisincrements, $region) {

            $data = DB::table('battletags')
                ->select(DB::raw('FLOOR(account_level / '.$xaxisincrements.') * '.$xaxisincrements.' AS account_level_floor, COUNT(*) AS count'))
                ->when(! empty($region), function ($query) use ($region) {
                    return $query->whereIn('region', $region);
                })
                ->where('account_level', '>=', $minimumaccountlevel)
                ->where('latest_game', '>', Carbon::now()->subYear())
                ->groupBy(DB::raw('FLOOR(account_level / 25) * 25'))
                ->orderBy(DB::raw('FLOOR(account_level / 25) * 25'))
                ->get();

            $data = $data->map(function ($item) {
                return [
                    'accountLevel' => $item->account_level_floor,
                    'total' => $item->count,
                ];
            })->toArray();

            return $data;
        });

        return $data;
    }

    public function getHeroLevelStats(Request $request)
    {
        //return response()->json($request->all());

        $region = (new RegionInputValidation())->passes('region', $request['region']);
        $gameType = (new GameTypeInputValidation())->passes('game_type', $request['game_type']);
        $hero = (new HeroInputByIDValidation())->passes('hero', $request['hero']);

        $cacheKey = 'GlobalExtraStatsHeroLevel|'.implode('|', [
            'hero'.$hero,
            'gameType='.implode(',', $gameType),
            'region='.implode(',', $region),

        ]);
        //return $cacheKey;

        $cache_time = 0;
        $data = Cache::store('database')->remember($cacheKey, $cache_time, function () use ($gameType, $hero, $region) {

            $data = DB::table('heroesprofile.replay')
                ->join('player', 'player.replayID', '=', 'replay.replayID')
                ->select(DB::raw(
                    'IF(hero_level < 20, hero_level, CASE 
                    WHEN mastery_taunt = 1 THEN 25
                    WHEN mastery_taunt = 2 THEN 40
                    WHEN mastery_taunt = 3 THEN 60
                    WHEN mastery_taunt = 4 THEN 80
                    WHEN mastery_taunt = 5 THEN 100
                END) AS hero_level_distribution, COUNT(*) AS count'
                ))
                ->whereIn('region', $region)
                ->whereIn('game_type', $gameType)
                ->where('game_date', '>', Carbon::now()->subYear())
                ->where('hero', $hero)
                ->groupBy(DB::raw('1'))
                ->orderBy(DB::raw('1'))
                ->get();

            $data = $data->map(function ($item) {
                return [
                    'hero_level_distribution' => $item->hero_level_distribution,
                    'total' => $item->count,
                ];
            })->toArray();

            return $data;
        });

        return $data;
    }
}
