<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameType;
use App\Models\Map;
use App\Models\PlayerStatsCache;
use App\Rules\DateInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\SeasonInputValidation;
use App\Support\GlobalCacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayerTalentsController extends Controller
{
    // The game types the hero picker's build list has a column for, as on /Global/Talents
    private const BUILDS_ALL_GAME_TYPES = ['qm', 'sl', 'ar'];

    public function show(Request $request, $battletag, $blizz_id, $region)
    {

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ];

        if (request()->has('hero')) {
            $validationRules['hero'] = ['sometimes', 'nullable', new HeroInputValidation];
        }

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region'), $validationRules);

        if ($validator->fails()) {
            if (config('app.env') === 'production') {
                return \Redirect::to('/');
            } else {
                return [
                    'data' => $request->all(),
                    'status' => 'failure to validate inputs',
                ];
            }
        }

        $userinput = $this->globalDataService->getHeroModel($request['hero']);

        return view('Player.talentData')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'playerloadsetting' => $this->globalDataService->getPlayerLoadSettings(),

            'userinput' => $userinput,
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
            'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            'patreon' => $this->globalDataService->checkIfSiteFlair($blizz_id, $region),
            'heroes' => $this->globalDataService->getHeroes(),
            'gametypedefault' => $this->globalDataService->getPlayerGameTypeDefault(),
        ]);
    }

    public function getPlayerTalentData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'game_type' => ['required', new GameTypeInputValidation],
            'hero' => ['required', new HeroInputValidation],
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'start_date' => ['sometimes', 'nullable', new DateInputValidation],
            'end_date' => ['sometimes', 'nullable', new DateInputValidation],
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
        $gameType = GameType::whereIn('short_name', $request['game_type'])->pluck('type_id')->toArray();
        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']]->id;
        $season = $request['season'];
        $game_map = $request['game_map'] ? Map::whereIn('name', $request['game_map'])->pluck('map_id')->toArray() : null;
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

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
            ->tap(function ($query) use ($season, $startDate, $endDate) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season, $startDate, $endDate);
            })
            ->when(! is_null($game_map), function ($query) use ($game_map) {
                return $query->whereIn('game_map', $game_map);
            })
            // ->toSql();
            ->get();

        $talentData = $this->globalDataService->getAllTalentsKeyed();

        $heroData = $this->globalDataService->getHeroes();
        $heroData = $heroData->keyBy('id');

        return [
            'talentData' => $this->getHeroTalentData($result, $talentData),
            'buildData' => $this->globalDataService->talentBuilds($result, $heroData[$hero], $talentData),
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
            $sort = (int) $data['talent']['sort'] - 1;

            if (! array_key_exists($level, $formattedData)) {
                $counter = 0;
                $formattedData[$level] = [];
            }
            $formattedData[$level][] = [
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

    /**
     * Ids of the heroes the player has QM, SL or ARAM games on, all time or in the given
     * season. The Talents hero picker then asks for each one's builds in turn.
     */
    public function getPlayerTalentBuildHeroes(Request $request)
    {
        $validator = Validator::make($request->all(), $this->buildsValidationRules());

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $season = $request['season'];

        $heroIds = DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('blizz_id', $request['blizz_id'])
            ->where('region', $request['region'])
            ->whereIn('game_type', $this->buildsGameTypes()->keys())
            ->tap(function ($query) use ($season) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season);
            })
            ->distinct()
            ->pluck('player.hero')
            ->values();

        return [
            'heroes' => $heroIds,
            'builds' => $this->upToDateHeroBuilds($request, $heroIds) ?: new \stdClass,
        ];
    }

    /**
     * Builds for every hero whose stored counts already include the player's latest game,
     * read in one query, so a repeat visit only asks for the heroes with something new.
     */
    private function upToDateHeroBuilds(Request $request, $heroIds): array
    {
        if (! $this->globalDataService->usesProductionCaching() || $heroIds->isEmpty()) {
            return [];
        }

        $latestReplayId = $this->globalDataService->latestPlayerReplayId(
            $request['blizz_id'],
            $request['region'],
            self::BUILDS_ALL_GAME_TYPES,
            $request['season']
        );

        if (! $latestReplayId) {
            return [];
        }

        $heroes = $this->globalDataService->getHeroes()->keyBy('id');
        $hashes = $heroIds->filter(fn ($heroId) => $heroes->has($heroId))
            ->mapWithKeys(fn ($heroId) => [$this->heroBuildsParamsHash($request, $heroId) => $heroId]);

        $builds = [];

        $rows = PlayerStatsCache::whereIn('params_hash', $hashes->keys())
            ->where('latest_replayID', '>=', $latestReplayId)
            ->get();

        foreach ($rows as $row) {
            $counts = $this->decodeBuildCounts($row->data);

            if ($counts === null) {
                continue;
            }

            $hero = $heroes[$hashes[$row->params_hash]];
            // Heroes with no complete build still count as done: an empty entry, not a re-request
            $builds[$hero->name] = $this->topBuilds($counts, $hero) ?: new \stdClass;
        }

        return $builds;
    }

    /**
     * One hero's most played build per game type, for the Talents hero picker.
     *
     * The cache keeps every build's [games, wins] rather than the answer, so a new game
     * only adds its replays to the counts instead of rescanning the hero. Uploaded
     * replays never change, so counted games never need recounting.
     */
    public function getPlayerTalentBuildHero(Request $request)
    {
        $validator = Validator::make($request->all(), $this->buildsValidationRules() + [
            'hero' => ['required', new HeroInputValidation],
        ]);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        $hero = $this->globalDataService->getHeroes()->keyBy('name')[$request['hero']];

        // Counting stops at this replay, so a game uploaded mid-request is counted once, next time
        $latestReplayId = $this->globalDataService->latestPlayerReplayId(
            $request['blizz_id'],
            $request['region'],
            self::BUILDS_ALL_GAME_TYPES,
            $request['season']
        );

        if (! $latestReplayId) {
            return [];
        }

        if (! $this->globalDataService->usesProductionCaching()) {
            return $this->topBuilds($this->addBuildCounts([], $this->heroBuildRows($request, $hero->id, null, $latestReplayId)), $hero);
        }

        $paramsHash = $this->heroBuildsParamsHash($request, $hero->id);
        $dbCache = PlayerStatsCache::where('params_hash', $paramsHash)->first();
        $counts = $dbCache ? $this->decodeBuildCounts($dbCache->data) : null;

        // X-Player-Cache says which path ran: miss (full count), update (new games added) or hit
        $cacheState = 'hit';

        if ($counts === null) {
            $counts = $this->addBuildCounts([], $this->heroBuildRows($request, $hero->id, null, $latestReplayId));
            $this->storeBuildCounts($request, $paramsHash, $counts, $latestReplayId);
            $cacheState = 'miss';
        } elseif ($latestReplayId > $dbCache->latest_replayID) {
            $counts = $this->addBuildCounts($counts, $this->heroBuildRows($request, $hero->id, $dbCache->latest_replayID, $latestReplayId));
            $this->storeBuildCounts($request, $paramsHash, $counts, $latestReplayId);
            $cacheState = 'update';
        }

        return response()->json($this->topBuilds($counts, $hero))->header('X-Player-Cache', $cacheState);
    }

    private function buildsValidationRules(): array
    {
        return [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'season' => ['sometimes', 'nullable', new SeasonInputValidation],
        ];
    }

    /** type_id => short name, for the game types the build list has a column for */
    private function buildsGameTypes()
    {
        return GameType::whereIn('short_name', self::BUILDS_ALL_GAME_TYPES)->pluck('short_name', 'type_id');
    }

    /** One row per game on the hero: game type, result and the seven talents, for replays in (after, upTo]. */
    private function heroBuildRows(Request $request, int $heroId, ?int $afterReplayId, int $upToReplayId)
    {
        $season = $request['season'];

        return DB::table('replay')
            ->join('player', 'player.replayID', '=', 'replay.replayID')
            ->join('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->select([
                'replay.game_type AS game_type',
                'player.winner AS winner',
                'talents.level_one AS level_one',
                'talents.level_four AS level_four',
                'talents.level_seven AS level_seven',
                'talents.level_ten AS level_ten',
                'talents.level_thirteen AS level_thirteen',
                'talents.level_sixteen AS level_sixteen',
                'talents.level_twenty AS level_twenty',
            ])
            ->where('blizz_id', $request['blizz_id'])
            ->where('region', $request['region'])
            ->where('hero', $heroId)
            ->whereIn('game_type', $this->buildsGameTypes()->keys())
            ->when(! is_null($afterReplayId), function ($query) use ($afterReplayId) {
                return $query->where('replay.replayID', '>', $afterReplayId);
            })
            ->where('replay.replayID', '<=', $upToReplayId)
            ->tap(function ($query) use ($season) {
                $this->globalDataService->applySeasonsOrDateRange($query, $season);
            })
            ->get();
    }

    /**
     * Adds games to the counts: [game type]['t1|t4|t7|t10|t13|t16|t20'] => [games, wins].
     * Builds missing a talent are left out, as talentBuilds() does.
     */
    private function addBuildCounts(array $counts, $rows): array
    {
        $gameTypes = $this->buildsGameTypes();
        $levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        foreach ($rows as $row) {
            $talents = array_map(fn ($level) => (int) $row->$level, $levels);

            if (in_array(0, $talents, true) || ! isset($gameTypes[$row->game_type])) {
                continue;
            }

            $build = &$counts[$gameTypes[$row->game_type]][implode('|', $talents)];
            $build ??= [0, 0];
            $build[0]++;
            $build[1] += $row->winner == 1 ? 1 : 0;
            unset($build);
        }

        return $counts;
    }

    /** The hero's most played build per game type, shaped like talentBuilds() output for the page. */
    private function topBuilds(array $counts, $hero): array
    {
        $talentData = $this->globalDataService->getAllTalentsKeyed();
        $levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        $returnData = [];

        foreach ($counts as $gameType => $builds) {
            $top = null;

            foreach ($builds as $key => [$games, $wins]) {
                $talents = explode('|', $key);

                if (collect($talents)->contains(fn ($talent) => ! $talentData->has($talent))) {
                    continue;
                }
                // Most games, then most wins, then the key, so ties always pick the same build
                if ($top === null || $games > $top[1] || ($games === $top[1] && ($wins > $top[2] || ($wins === $top[2] && strcmp($key, $top[0]) < 0)))) {
                    $top = [$key, $games, $wins, $talents];
                }
            }

            if ($top === null) {
                continue;
            }

            [, $games, $wins, $talents] = $top;
            $build = [
                'wins' => $wins,
                'losses' => $games - $wins,
                'games_played' => $games,
            ];
            foreach ($levels as $index => $level) {
                $build[$level] = $talentData[$talents[$index]];
            }
            $build['win_rate'] = round(($wins / $games) * 100, 2);
            $build['hero'] = $hero;

            $returnData[$gameType] = [$build];
        }

        return $returnData;
    }

    // Counts are stored gzip-compressed and base64'd in the text column
    private function storeBuildCounts(Request $request, string $paramsHash, array $counts, int $latestReplayId): void
    {
        PlayerStatsCache::updateOrCreate(
            ['params_hash' => $paramsHash],
            [
                'blizz_id' => $request['blizz_id'],
                'region' => $request['region'],
                'latest_replayID' => $latestReplayId,
                'data' => base64_encode(gzcompress(json_encode(['v' => 1, 'counts' => $counts]), 6)),
            ]
        );
    }

    /** The stored counts, or null when the row is missing or isn't in this format. */
    private function decodeBuildCounts(?string $data): ?array
    {
        $compressed = $data ? base64_decode($data, true) : false;
        $json = $compressed !== false ? @gzuncompress($compressed) : false;
        $decoded = $json !== false ? json_decode($json, true) : null;

        return is_array($decoded) && ($decoded['v'] ?? null) === 1 ? ($decoded['counts'] ?? []) : null;
    }

    private function heroBuildsParamsHash(Request $request, int $heroId): string
    {
        return hash('sha256', json_encode(GlobalCacheKey::normalize([
            'page' => 'talent_build_counts_hero',
            'blizz_id' => $request['blizz_id'],
            'region' => $request['region'],
            'season' => $request['season'],
            'hero' => $heroId,
        ])));
    }
}
