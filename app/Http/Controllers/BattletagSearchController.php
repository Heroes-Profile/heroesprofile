<?php

namespace App\Http\Controllers;

use App\Models\BannedAccount;
use App\Models\Battletag;
use App\Models\Map;
use App\Models\Replay;
use App\Rules\BattletagInputProhibitCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BattletagSearchController extends Controller
{
    public function show(Request $request)
    {

        return view('searchedBattletagHolding')->with(['userinput' => $request['userinput'], 'type' => $request['type'], 'bladeGlobals' => $this->globalDataService->getBladeGlobals()]);
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

        return $data;
    }

    private function searchForBattletag($input)
    {
        if (strpos($input, '#') !== false) {
            $data = Battletag::select('blizz_id', 'battletag', 'region', 'latest_game')
                ->where('battletag', $input)
                ->get();
        } else {
            $data = Battletag::select('blizz_id', 'battletag', 'region', 'latest_game')
                ->where('battletag', 'LIKE', $input.'#%')
                ->get();
        }

        $returnData = [];
        $counter = 0;
        $uniqueBlizzIDRegion = [];

        $privateAccounts = $this->globalDataService->getPrivateAccounts();
        $bannedAccounts = BannedAccount::get();

        foreach ($data as $row) {
            $blizz_id = $row['blizz_id'];
            $region = $row['region'];

            $containsAccount = $privateAccounts->contains(function ($account) use ($blizz_id, $region) {
                return $account['blizz_id'] == $blizz_id && $account['region'] == $region;
            });
            $existingBan = $bannedAccounts->contains(function ($account) use ($blizz_id, $region) {
                return $account['blizz_id'] == $blizz_id && $account['region'] == $region;
            });

            if (! $containsAccount && ! $existingBan) {

                if (array_key_exists($row['blizz_id'].'|'.$row['region'], $uniqueBlizzIDRegion)) {
                    if ($row['latest_game'] > $uniqueBlizzIDRegion[$row['blizz_id'].'|'.$row['region']]) {
                        $returnData[$row['blizz_id'].'|'.$row['region']] = $row;
                    }
                } else {
                    $uniqueBlizzIDRegion[$row['blizz_id'].'|'.$row['region']] = $row['latest_game'];
                    $returnData[$row['blizz_id'].'|'.$row['region']] = $row;
                    $counter++;
                }
            }

            if ($counter == 50) {
                break;
            }
        }

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        $maps = Map::all();
        $maps = $maps->keyBy('map_id');

        $regions = $this->globalDataService->getRegionIDtoString();

        foreach ($returnData as $item) {
            $blizzId = $item->blizz_id;
            $battletag = $item->battletag;
            $battletagShort = explode('#', $item->battletag)[0];
            $region = $item->region;
            $regionName = $regions[$item->region];
            $latestGame = $item->latest_game;

            $totalGamesPlayed = $this->getTotalGamesPlayedForPlayer($blizzId, $region);
            $latestMap = $this->getLatestMapPlayedForPlayer($blizzId, $region);
            $latestHero = $this->getLatestHeroPlayedForPlayer($blizzId, $region);

            $item->totalGamesPlayed = $totalGamesPlayed;
            $item->latestMap = $latestMap ? $maps[$latestMap] : null;
            $item->latestHero = $latestHero ? $heroData[$latestHero] : null;

            $item->battletagShort = $battletagShort;
            $item->regionName = $regionName;

        }

        $returnData = array_filter($returnData, function ($item) {
            return $item->totalGamesPlayed > 0;
        });

        usort($returnData, function ($a, $b) {
            return $b->totalGamesPlayed - $a->totalGamesPlayed;
        });

        return $returnData;
    }

    private function getTotalGamesPlayedForPlayer($blizzId, $region, $gameType = null)
    {
        $count = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                ->where('region', $region);
        })
            ->when($gameType, function ($query, $gameType) {
                return $query->where('game_type', $gameType);
            })
            ->where('game_type', '<>', 0) // Exclude custom games
            ->count();

        return $count;
    }

    private function getLatestMapPlayedForPlayer($blizzId, $region, $gameType = null)
    {
        $lastReplayMap = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                ->where('region', $region);
        })
            ->where('game_type', '<>', 0) // Exclude custom games
            ->orderBy('game_date', 'desc')
            ->value('replay.game_map');

        return $lastReplayMap;
    }

    private function getLatestHeroPlayedForPlayer($blizzId, $region, $gameType = null)
    {
        $latestHero = Replay::select('hero')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('blizz_id', $blizzId)
            ->where('region', $region)
            ->orderBy('game_date', 'desc')
            ->first();

        if ($latestHero) {
            return $latestHero->hero;
        }

        return null;
    }
}
