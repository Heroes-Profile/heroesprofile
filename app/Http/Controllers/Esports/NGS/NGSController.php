<?php

namespace App\Http\Controllers\Esports\NGS;

use App\Http\Controllers\Controller;
use App\Models\Map;
use App\Models\NGS\Battletag;
use App\Models\NGS\NGSTeam;
use App\Models\NGS\Replay;
use App\Models\NGS\Standing;
use App\Rules\BattletagInputProhibitCharacters;
use App\Rules\NGSDivisionInputValidation;
use App\Rules\NGSSeasonInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NGSController extends Controller
{
    public function show(Request $request)
    {
        $defaultseason = NGSTeam::max('season');

        return view('Esports.NGS.ngsMain')
            ->with([
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);
    }

    public function getStandingData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new NGSSeasonInputValidation()],
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];
        $division = $request['division'];

        $results = Standing::where('season', $season)
            ->when(! is_null($division), function ($query) use ($division) {
                return $query->where('division', $division);
            })
            ->get();

        $groupedResults = $results->groupBy('division');

        $sortedGroupedResults = $groupedResults->map(function ($group) {
            return $group->sortByDesc('points');
        });

        return $sortedGroupedResults;
    }

    public function getDivisionData(Request $request)
    {
        $validationRules = [
            'season' => ['required', new NGSSeasonInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];

        $results = Replay::where('season', $season)
            ->select('division_0')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('division_0')
            ->get();

        return $results;
    }

    public function getTeamsData(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new NGSSeasonInputValidation()],
            'division' => ['sometimes', 'nullable', new NGSDivisionInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];
        $division = $request['division'];

        $results = Replay::join('heroesprofile_ngs.player', 'heroesprofile_ngs.player.replayID', '=', 'heroesprofile_ngs.replay.replayID')
            ->join('heroesprofile_ngs.teams', 'heroesprofile_ngs.teams.team_id', '=', 'heroesprofile_ngs.player.team_name')
            ->where('heroesprofile_ngs.replay.season', $season)
            ->when(! is_null($division), function ($query) use ($division) {
                return $query->where('heroesprofile_ngs.teams.division', $division);
            })
            ->select('heroesprofile_ngs.teams.team_name', 'heroesprofile_ngs.player.winner', 'heroesprofile_ngs.teams.image', 'heroesprofile_ngs.teams.division')
            ->get();

        $groupedResults = $results->groupBy('team_name')->map(function ($group) {
            $wins = $group->where('winner', 1)->count() / 5;
            $losses = $group->where('winner', 0)->count() / 5;
            $gamesPlayed = $wins + $losses;

            $image = '';
            if (strpos($group[0]['image'], 'https://s3.amazonaws.com/ngs-image-storage/') !== false) {
                $image = explode('https://s3.amazonaws.com/ngs-image-storage/', $group[0]['image']);
                $image = 'https://s3.amazonaws.com/ngs-image-storage/'.urlencode($image[1]);

                if (strpos($image, 'undefined') !== false) {
                    $image = '/images/NGS/no-image-clipped.png';
                }
            } else {
                $image = $group[0]['image'];
            }

            return [
                'team_name' => $group[0]['team_name'],
                'division' => $group[0]['division'],
                'icon_url' => $image,
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => $gamesPlayed > 0 ? round(($wins / $gamesPlayed) * 100, 2) : 0,
            ];
        })->sortBy('team_name')->values()->all();

        return $groupedResults;
    }

    public function playerSearch(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'userinput' => ['required', 'string', new BattletagInputProhibitCharacters],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $input = $request['userinput'];
        $input = str_replace(' ', '', $input);

        $data = null;
        if (strpos($input, '#') !== false) {
            $data = Battletag::select('blizz_id', 'battletag', 'region')
                ->where('battletag', $input)
                ->get();
        } else {
            $data = Battletag::select('blizz_id', 'battletag', 'region')
                ->where('battletag', 'LIKE', $input.'#%')
                ->get();
        }

        $returnData = [];
        $counter = 0;
        $uniqueBlizzIDRegion = [];
        foreach ($data as $row) {

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
            $item->latestMap = $maps[$latestMap];
            $item->latestHero = $heroData[$latestHero];

            $item->battletagShort = $battletagShort;
            $item->regionName = $regionName;

        }
        usort($returnData, function ($a, $b) {
            return $b->totalGamesPlayed - $a->totalGamesPlayed;
        });

        return array_values($returnData);
    }

    private function getTotalGamesPlayedForPlayer($blizzId, $region)
    {
        $count = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                ->where('region', $region);
        })
            ->count();

        return $count;
    }

    private function getLatestMapPlayedForPlayer($blizzId, $region)
    {
        $lastReplayMap = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                ->where('region', $region);
        })
            ->orderBy('game_date', 'desc')
            ->value('replay.game_map');

        return $lastReplayMap;
    }

    private function getLatestHeroPlayedForPlayer($blizzId, $region)
    {
        $latestHero = Replay::whereHas('players', function ($query) use ($blizzId, $region) {
            $query->where('blizz_id', $blizzId)
                ->where('region', $region)
                ->orderBy('game_date', 'desc');
        })
            ->with('players') // Load the players relationship
            ->orderBy('game_date', 'desc')
            ->limit(1)
            ->get();

        if ($latestHero->count() > 0) {
            $latestHeroValue = $latestHero[0]->players[0]->hero;
        } else {
            $latestHeroValue = null;
        }

        return $latestHeroValue;
    }
}
