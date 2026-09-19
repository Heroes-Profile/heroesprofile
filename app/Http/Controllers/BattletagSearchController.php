<?php

namespace App\Http\Controllers;

use App\Models\Battletag;
use App\Models\Map;
use App\Rules\BattletagInputProhibitCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BattletagSearchController extends Controller
{
    public function show(Request $request, $userinput, $type)
    {
        $validationRules = [
            'userinput' => ['required', 'string', 'max:255', new BattletagInputProhibitCharacters],
            'type' => ['required', 'string', 'in:all,battlenet,alt'],
        ];

        $validator = Validator::make(compact('userinput', 'type'), $validationRules);

        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => compact('userinput', 'type'),
                    'errors' => $validator->errors()->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        return view('searchedBattletagHolding')->with(['userinput' => $userinput, 'type' => $type, 'bladeGlobals' => $this->globalDataService->getBladeGlobals()]);
    }

    public function battletagSearch(Request $request)
    {
        $validator = Validator::make($request->only('userinput'), ['userinput' => ['required', 'string', new BattletagInputProhibitCharacters]]);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $data = $this->searchForBattletag($request['userinput']);

        foreach ($data as $item) {
            unset($item->battletag);
        }

        return $data;
    }

    public function friendFoeSearch(Request $request)
    {
        $validator = Validator::make($request->only(['userinput', 'region']), [
            'userinput' => ['required', 'string', new BattletagInputProhibitCharacters],
            'region' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $data = $this->searchForBattletag($request['userinput'], (int) $request['region']);

        foreach ($data as $item) {
            unset($item->battletag);
        }

        return $data;
    }

    private function searchForBattletag($input, ?int $region = null)
    {
        $rows = Battletag::select('blizz_id', 'battletag', 'region', 'latest_game')
            ->when(
                str_contains($input, '#'),
                fn ($q) => $q->where('battletag', $input),
                fn ($q) => $q->where('battletag', 'LIKE', $input.'#%')
            )
            ->when($region, fn ($q) => $q->where('region', $region))
            ->orderByDesc('latest_game')
            ->limit(500)
            ->get();

        $restricted = $this->globalDataService->restrictedAccountKeys();
        $accounts = [];

        foreach ($rows as $row) {
            $key = $row->blizz_id.'|'.$row->region;

            // Newest first, so the first row seen for an account is its current battletag.
            if (isset($restricted[$key]) || isset($accounts[$key])) {
                continue;
            }

            $accounts[$key] = $row;

            if (count($accounts) === 50) {
                break;
            }
        }

        if ($accounts === []) {
            return [];
        }

        // Separate whereIn lists rather than OR'd (blizz_id, region) pairs, so MySQL can
        // seek player.blizzid_hero. That can match pairs outside $accounts; they are
        // dropped once the rows are keyed.
        $blizzIds = array_values(array_unique(array_map(fn ($a) => $a->blizz_id, $accounts)));
        $regionIds = array_values(array_unique(array_map(fn ($a) => $a->region, $accounts)));
        $rowKey = fn ($row) => $row->blizz_id.'|'.$row->region;

        $stats = DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->whereIn('player.blizz_id', $blizzIds)
            ->whereIn('replay.region', $regionIds)
            ->where('replay.game_type', '<>', 0) // Exclude custom games
            ->groupBy('player.blizz_id', 'replay.region')
            ->select('player.blizz_id', 'replay.region', DB::raw('COUNT(*) AS games'), DB::raw('MAX(replay.game_date) AS latest'))
            ->get()
            ->keyBy($rowKey)
            ->intersectByKeys($accounts);

        $latest = $stats->isEmpty() ? collect() : DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->whereIn('player.blizz_id', $blizzIds)
            ->whereIn('replay.region', $regionIds)
            ->whereIn('replay.game_date', $stats->pluck('latest')->unique()->values()->all())
            ->where('replay.game_type', '<>', 0)
            ->select('player.blizz_id', 'replay.region', 'replay.game_date', 'player.hero', 'replay.game_map')
            ->get()
            ->filter(fn ($row) => isset($stats[$rowKey($row)]) && $row->game_date == $stats[$rowKey($row)]->latest)
            ->unique($rowKey)
            ->keyBy($rowKey);

        $heroData = $this->globalDataService->getHeroes()->keyBy('id');
        $maps = Map::all()->keyBy('map_id');
        $regions = $this->globalDataService->getRegionIDtoString();

        $returnData = [];

        foreach ($accounts as $key => $item) {
            $games = (int) ($stats[$key]->games ?? 0);

            if ($games === 0) {
                continue;
            }

            $item->totalGamesPlayed = $games;
            $item->latestMap = $maps[$latest[$key]->game_map ?? null] ?? null;
            $item->latestHero = $heroData[$latest[$key]->hero ?? null] ?? null;
            $item->battletagShort = explode('#', $item->battletag)[0];
            $item->regionName = $regions[$item->region] ?? null;

            $returnData[] = $item;
        }

        usort($returnData, fn ($a, $b) => $b->totalGamesPlayed - $a->totalGamesPlayed);

        return $returnData;
    }
}
