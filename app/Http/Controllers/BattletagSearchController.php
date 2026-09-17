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

        $matchesAccounts = function ($query, array $extra = []) use ($accounts) {
            $query->where(function ($q) use ($accounts, $extra) {
                foreach ($accounts as $key => $account) {
                    $q->orWhere(function ($w) use ($account, $extra, $key) {
                        $w->where('player.blizz_id', $account->blizz_id)
                            ->where('replay.region', $account->region);

                        if (isset($extra[$key])) {
                            $w->where('replay.game_date', $extra[$key]);
                        }
                    });
                }
            });
        };

        $stats = DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->where('replay.game_type', '<>', 0) // Exclude custom games
            ->tap(fn ($q) => $matchesAccounts($q))
            ->groupBy('player.blizz_id', 'replay.region')
            ->select('player.blizz_id', 'replay.region', DB::raw('COUNT(*) AS games'), DB::raw('MAX(replay.game_date) AS latest'))
            ->get()
            ->keyBy(fn ($row) => $row->blizz_id.'|'.$row->region);

        $latestDates = $stats->map(fn ($row) => $row->latest)->all();

        $latest = DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->where('replay.game_type', '<>', 0)
            ->tap(fn ($q) => $matchesAccounts($q, $latestDates))
            ->select('player.blizz_id', 'replay.region', 'player.hero', 'replay.game_map')
            ->get()
            ->unique(fn ($row) => $row->blizz_id.'|'.$row->region)
            ->keyBy(fn ($row) => $row->blizz_id.'|'.$row->region);

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
