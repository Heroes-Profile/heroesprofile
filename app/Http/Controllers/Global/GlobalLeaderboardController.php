<?php

namespace App\Http\Controllers\Global;

use App\Models\BannedLeaderboardAccounts;
use App\Models\BattlenetAccount;
use App\Models\BattletagRealRank;
use App\Models\HeroesDataTalent;
use App\Models\Leaderboard;
use App\Models\MasterGamesPlayedData;
use App\Models\MasterGamesPlayedDataGroups;
use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataUD;
use App\Models\MatchPredictionPlayerStat;
use App\Models\MatchPredictionSeason;
use App\Models\SeasonDate;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RegionInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Rules\TierInputByIDValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GlobalLeaderboardController extends GlobalsInputValidationController
{
    private $rankModifier = 0;

    private const STORM_LEAGUE = 5;

    // Match Prediction's games requirement, relaxed like CalculateLeaderboards' groups
    private const PREDICTION_START_GAMES = 20;

    private const PREDICTION_MIN_PLAYERS = 50;

    private const PREDICTION_MIN_GAMES = 5;

    private const REAL_RANK_LEAGUES = [1 => 'bronze', 2 => 'silver', 3 => 'gold', 4 => 'platinum', 5 => 'diamond', 6 => 'master'];

    private const REAL_RANK_LEAGUE_SIZE = 500;

    public function show(Request $request)
    {
        return view('Global.Leaderboard.globalLeaderboard')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault('single'),
            'advancedfiltering' => $this->globalDataService->getAdvancedFilterShowDefault(),
            'defaultseason' => $this->globalDataService->getDefaultSeason(),
            'defaultpredictionseason' => (string) $this->globalDataService->getDefaultMatchPredictionSeason(),
            'weekssincestart' => $this->globalDataService->getWeeksSinceSeasonStart(),
            'matchpredictionweekssincestart' => $this->globalDataService->matchPredictionGetWeeksSinceSeasonStart(),
            'urlparameters' => $request->all(),
        ]);
    }

    public function showRemoved(Request $request)
    {
        return view('Global.Leaderboard.globalLeaderboardRemoved')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }

    public function getLeaderboardData(Request $request)
    {
        // return response()->json($request->all());

        $validationRules = [
            'season' => ['required', new SeasonInputValidation],
            'game_type' => ['required', new GameTypeInputValidation],
            'type' => 'required|in:match prediction,player,hero,role,sl rank',
            'groupsize' => ['required', new StackSizeInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'tierrank' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'league' => 'sometimes|nullable|in:grandmaster,master,diamond,platinum,gold,silver,bronze',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        if ($request['type'] == 'sl rank') {
            return $this->getRealRankLeaderboard($request['season'], $this->globalDataService->getRegionFilterValues($request['region']), $request['league']);
        }

        $hero = $request['hero'];
        $role = $this->globalDataService->getMMRTypeValue($request['role']);

        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);

        $season = $request['season'];
        $region = $this->globalDataService->getRegionFilterValues($request['region']);
        $tierrank = $request['tierrank'];
        $type = $request['type'];
        $typeNumber = 0;

        if ($type != 'match prediction') {
            if ($type == 'player') {
                $typeNumber = $this->globalDataService->getMMRTypeValue($request['type']);
            } elseif ($type == 'hero') {
                $typeNumber = $hero;
            } elseif ($type == 'role') {
                $typeNumber = $role;
            }

            $groupsize = -1;
            if ($season < 20) {
                $groupsize = 0;
            } else {
                if ($request['groupsize'] == 'Solo') {
                    $groupsize = 1;
                } elseif ($request['groupsize'] == 'Duo') {
                    $groupsize = 2;
                } elseif ($request['groupsize'] == '3 Players') {
                    $groupsize = 3;
                } elseif ($request['groupsize'] == '4 Players') {
                    $groupsize = 4;
                } elseif ($request['groupsize'] == '5 Players') {
                    $groupsize = 5;
                } elseif ($request['groupsize'] == 'All') {
                    $groupsize = 0;
                }
            }

            $data = Leaderboard::query()
                ->select('rank', 'leaderboard_group', 'min_games_required', 'battletag', 'split_battletag', 'blizz_id', 'region', 'win_rate', 'games_played', 'conservative_rating', 'rating', 'normalized_rating', 'most_played_hero', 'level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty', 'hero_build_games_played')
                ->filterByGameType($gameType)
                ->filterBySeason($season)
                ->filterByType($typeNumber)
                ->filterByStackSize($groupsize)
                ->filterByRegion($region)
                // Rank order keeps each group together and makes the rank shift for hidden accounts below correct
                ->orderBy('rank')
                // ->toSql();
                ->get();

            $heroData = $this->globalDataService->getHeroes();
            $heroData = $heroData->keyBy('id');

            $rankTiers = $this->globalDataService->getRankTiers($gameType, $typeNumber);

            $talentData = Cache::remember('heroes_data_talents_keyed', 3600, function () {
                return HeroesDataTalent::all()->keyBy('talent_id');
            });

            // Same rule as checkIfSiteFlair: only Patreon accounts with site flair enabled.
            $patreonAccounts = BattlenetAccount::without(['patreonAccount', 'userSettings'])
                ->whereHas('patreonAccount', fn ($query) => $query->where('site_flair', 1))
                ->get(['blizz_id', 'region'])
                ->keyBy(fn ($a) => $a->blizz_id.'|'.$a->region);
            $bannedLeaderboardAccounts = BannedLeaderboardAccounts::where('season', $season)->get()->keyBy(fn ($b) => $b->blizz_id.'|'.$b->region);
            $authenticatedUser = Auth::user();

            $blizzIDRegionMapping = [];
            $data = $data->map(function ($item) use ($heroData, $rankTiers, $talentData, $type, $typeNumber, $patreonAccounts, &$blizzIDRegionMapping, $tierrank, $bannedLeaderboardAccounts, $authenticatedUser) {
                $key = $item->blizz_id.'|'.$item->region;

                if (array_key_exists($key, $blizzIDRegionMapping)) {
                    return null;
                }
                $blizzIDRegionMapping[$key] = 'found';

                $patreonAccount = $patreonAccounts->get($key);

                if ($bannedLeaderboardAccounts->has($key)) {
                    $this->rankModifier++;

                    return null;
                }

                if ($this->globalDataService->isHiddenFrom($item->blizz_id, $item->region, $authenticatedUser)) {
                    $this->rankModifier++;

                    return null;
                }

                $item->patreon = ! is_null($patreonAccount) && ! $this->globalDataService->isFlairHidden('patreon', $item->blizz_id, $item->region);
                $item->hp_owner = $this->globalDataService->showOwnerFlair($item->blizz_id, $item->region);
                $item->mmr = round(1800 + 40 * $item->conservative_rating);
                $item->win_rate = round($item->win_rate, 2);
                $item->rating = round($item->rating, 2);
                $item->most_played_hero = $item->most_played_hero ? $heroData[$item->most_played_hero] : null;
                $item->tier = $this->globalDataService->calculateSubTier($rankTiers, $item->mmr);
                $item->tier_id = $this->globalDataService->calculateTierID($item->tier);

                if ($tierrank && $tierrank != intval($item->tier_id)) {
                    return null;
                }

                $item->region_id = $item->region;
                $item->region = $this->globalDataService->getRegionIDtoString()[$item->region];

                $item->rank = $item->rank - $this->rankModifier;

                $item->level_one = $item->level_one && $item->level_one != 0 ? isset($talentData[$item->level_one]) ? $talentData[$item->level_one] : null : null;
                $item->level_four = $item->level_four && $item->level_four != 0 ? isset($talentData[$item->level_four]) ? $talentData[$item->level_four] : null : null;
                $item->level_seven = $item->level_seven && $item->level_seven != 0 ? isset($talentData[$item->level_seven]) ? $talentData[$item->level_seven] : null : null;
                $item->level_ten = $item->level_ten && $item->level_ten != 0 ? isset($talentData[$item->level_ten]) ? $talentData[$item->level_ten] : null : null;
                $item->level_thirteen = $item->level_thirteen && $item->level_thirteen != 0 ? isset($talentData[$item->level_thirteen]) ? $talentData[$item->level_thirteen] : null : null;
                $item->level_sixteen = $item->level_sixteen && $item->level_sixteen != 0 ? isset($talentData[$item->level_sixteen]) ? $talentData[$item->level_sixteen] : null : null;
                $item->level_twenty = $item->level_twenty && $item->level_twenty != 0 ? isset($talentData[$item->level_twenty]) ? $talentData[$item->level_twenty] : null : null;

                $item->hero = $type == 'hero' ? $heroData[$typeNumber] : null;

                return $item;
            })->filter()->values();
        } else {
            $leaderboard = MatchPredictionPlayerStat::select('match_prediction_player_stats.battlenet_accounts_id', 'win', 'loss', 'games_played', 'win_rate', 'battletag', 'blizz_id', 'region')
                ->join('battlenet_accounts', 'battlenet_accounts.battlenet_accounts_id', '=', 'match_prediction_player_stats.battlenet_accounts_id')
                ->where('season', $season)
                ->where('game_type', $gameType)
                ->get();

            $leaderboard->each(function ($item) {
                $item->rating = $item->win_rate;
            });

            $authenticatedUser = Auth::user();
            $players = $leaderboard->reject(fn ($item) => $this->globalDataService->isHiddenFrom($item->blizz_id, $item->region, $authenticatedUser))->values();

            // Like CalculateLeaderboards: start at 20 predictions and, while fewer than 50 players qualify, lower it
            // by one per week of the season down to 5. Each step is a group; a higher group always ranks above a lower one.
            $predictionSeason = MatchPredictionSeason::find($season);
            $weeks = 1;
            if ($predictionSeason) {
                $end = $predictionSeason->end_date ? Carbon::parse($predictionSeason->end_date) : Carbon::now();
                $weeks = max(1, (int) floor(Carbon::parse($predictionSeason->start_date)->diffInDays($end) / 7));
            }

            $requirements = [];
            $requirement = self::PREDICTION_START_GAMES;
            while (true) {
                $requirements[] = $requirement;
                $qualifying = $players->where('games_played', '>=', $requirement)->count();
                if ($qualifying >= self::PREDICTION_MIN_PLAYERS || $requirement <= self::PREDICTION_MIN_GAMES) {
                    break;
                }
                $requirement = max($requirement - $weeks, self::PREDICTION_MIN_GAMES);
            }

            $ranked = $players->map(function ($item) use ($requirements) {
                $group = collect($requirements)->search(fn ($required) => $item->games_played >= $required);
                if ($group === false) {
                    return null;
                }
                $item->leaderboard_group = $group;
                $item->min_games_required = $requirements[$group];

                return $item;
            })->filter()
                ->sort(fn ($a, $b) => [$a->leaderboard_group, $b->rating] <=> [$b->leaderboard_group, $a->rating])
                ->values();

            $counter = 1;

            $ranked->each(function ($item) use (&$counter) {
                $item->rank = $counter;
                $item->split_battletag = explode('#', $item->battletag)[0];
                $item->region_id = $item->region;
                $item->region = $this->globalDataService->getRegionIDtoString()[$item->region];
                $counter++;
                unset($item->battlenet_accounts_id);
            });

            $data = $ranked;
        }

        return $data;
    }

    /**
     * The top players by real Storm League rank read off loading screens (battletags_real_ranks), each by
     * their latest rank read in the season, grouped by league: Master (Grandmasters first, by ladder
     * position), then Diamond down to Bronze, each by division. Within a division, players with the Player
     * leaderboard's games requirement come first by Storm League win rate, then everyone under it by games
     * played; read count breaks what's left.
     */
    private function getRealRankLeaderboard($season, $region, $league = null)
    {
        $seasonDates = SeasonDate::find($season);
        if (! $seasonDates) {
            return [];
        }

        // The Player leaderboard's requirement: 5 games per week of the season so far (a past season's full length)
        $weeks = $seasonDates->id == SeasonDate::max('id')
            ? $this->globalDataService->getWeeksSinceSeasonStart()
            : max(1, (int) round(Carbon::parse($seasonDates->start_date)->diffInDays(Carbon::parse($seasonDates->end_date)) / 7));
        $minGames = $weeks * 5;

        $build = function () use ($seasonDates, $region, $minGames) {
            $periods = BattletagRealRank::query()->toBase()
                ->where('season', $seasonDates->id)
                ->when($region, fn ($query) => $query->where('region', $region))
                ->select('player_id', 'blizz_id', 'region', 'league_tier', 'division', 'grandmaster', 'ladder_position', 'unranked', 'read_count')
                ->selectRaw('ROW_NUMBER() OVER (PARTITION BY blizz_id, region ORDER BY last_game_date DESC) AS row_num');

            // Each player's latest rank this season; unranked reads don't place anyone
            $latest = DB::connection('heroesprofile')->query()
                ->fromSub($periods, 'periods')
                ->join('battletags', 'battletags.player_id', '=', 'periods.player_id')
                ->where('periods.row_num', 1)
                ->where('periods.unranked', 0)
                ->whereNotNull('periods.league_tier')
                ->get(['battletags.battletag', 'periods.blizz_id', 'periods.region', 'periods.league_tier', 'periods.division', 'periods.grandmaster', 'periods.ladder_position', 'periods.read_count'])
                ->map(fn ($period) => [
                    'battletag' => $period->battletag,
                    'blizz_id' => (int) $period->blizz_id,
                    'region' => (int) $period->region,
                    'league_tier' => (int) $period->league_tier,
                    'grandmaster' => (bool) $period->grandmaster,
                    'division' => $period->division === null ? null : (int) $period->division,
                    'ladder_position' => $period->ladder_position === null ? null : (int) $period->ladder_position,
                    'read_count' => (int) $period->read_count,
                ]);

            // League and division come before games in board order, so only the divisions that reach each
            // league's top 500 need games. Restricted accounts don't count toward it; the board drops them per viewer.
            $restricted = $this->globalDataService->restrictedAccountKeys();
            $latest = $latest->groupBy('league_tier')
                ->flatMap(function ($league) use ($restricted) {
                    $shown = 0;

                    return $league->groupBy(fn ($period) => $period['grandmaster'] ? -1 : ($period['division'] ?? 0))
                        ->sortKeys()
                        ->takeWhile(function ($division) use (&$shown, $restricted) {
                            $reached = $shown >= self::REAL_RANK_LEAGUE_SIZE;
                            $shown += $division->reject(fn ($period) => isset($restricted[$period['blizz_id'].'|'.$period['region']]))->count();

                            return ! $reached;
                        })
                        ->collapse();
                })
                ->values();

            // This season's Storm League games, every upload counted
            $playerType = $this->globalDataService->getMMRTypeValue('player');
            $games = collect();
            foreach ($latest->pluck('blizz_id')->unique()->chunk(1000) as $blizzIds) {
                $games = $games->concat(MasterGamesPlayedData::query()
                    ->where('type_value', $playerType)
                    ->where('stack_size', 0)
                    ->where('season', $seasonDates->id)
                    ->where('game_type', self::STORM_LEAGUE)
                    ->whereIn('blizz_id', $blizzIds->values())
                    ->get(['blizz_id', 'region', 'win', 'loss']));
            }
            $games = $games->keyBy(fn ($row) => $row->blizz_id.'|'.$row->region);

            return $latest->map(function ($period) use ($games, $minGames) {
                $record = $games->get($period['blizz_id'].'|'.$period['region']);
                $wins = (int) ($record->win ?? 0);
                $gamesPlayed = $wins + (int) ($record->loss ?? 0);

                return [
                    'battletag' => $period['battletag'],
                    'blizz_id' => $period['blizz_id'],
                    'region' => $period['region'],
                    'league' => $period['grandmaster'] ? 'grandmaster' : self::REAL_RANK_LEAGUES[$period['league_tier']],
                    'league_tier' => $period['league_tier'],
                    'grandmaster' => $period['grandmaster'],
                    'division' => $period['division'],
                    'ladder_position' => $period['ladder_position'],
                    'read_count' => $period['read_count'],
                    'games_played' => $gamesPlayed,
                    'win_rate' => $gamesPlayed > 0 ? round($wins / $gamesPlayed * 100, 2) : null,
                    'eligible' => $gamesPlayed >= $minGames,
                ];
            })
                ->sort(fn ($a, $b) => $this->compareRealRanks($a, $b))
                ->values()
                ->all();
        };

        // Local shares prod's cache table, so only production caches
        $rows = $this->globalDataService->usesProductionCaching()
            ? Cache::remember('real_rank_leaderboard|'.$season.'|'.$region, 600, $build)
            : $build();

        $patreonAccounts = BattlenetAccount::without(['patreonAccount', 'userSettings'])
            ->whereHas('patreonAccount', fn ($query) => $query->where('site_flair', 1))
            ->get(['blizz_id', 'region'])
            ->keyBy(fn ($a) => $a->blizz_id.'|'.$a->region);
        $authenticatedUser = Auth::user();
        $regionNames = $this->globalDataService->getRegionIDtoString();

        $rank = 0;

        return collect($rows)
            ->reject(fn ($row) => $this->globalDataService->isHiddenFrom($row['blizz_id'], $row['region'], $authenticatedUser))
            // Top 500 per league; rows are already in board order
            ->groupBy('league_tier')
            ->flatMap(fn ($league) => $league->take(self::REAL_RANK_LEAGUE_SIZE))
            ->map(function ($row) use (&$rank, $patreonAccounts, $regionNames, $minGames) {
                $key = $row['blizz_id'].'|'.$row['region'];

                return array_merge($row, [
                    'rank' => ++$rank,
                    // Groups by league, Master first: 0 Master ... 5 Bronze
                    'leaderboard_group' => 6 - $row['league_tier'],
                    'group_name' => ucfirst(self::REAL_RANK_LEAGUES[$row['league_tier']]),
                    'min_games' => $minGames,
                    'split_battletag' => explode('#', $row['battletag'])[0],
                    'region_id' => $row['region'],
                    'region' => $regionNames[$row['region']],
                    'patreon' => $patreonAccounts->has($key) && ! $this->globalDataService->isFlairHidden('patreon', $row['blizz_id'], $row['region']),
                    'hp_owner' => $this->globalDataService->showOwnerFlair($row['blizz_id'], $row['region']),
                ]);
            })
            // After ranking so ranks stay board-wide; Master includes Grandmasters
            ->when($league, fn ($board) => $board->filter(fn ($row) => $league === 'grandmaster'
                ? $row['grandmaster']
                : self::REAL_RANK_LEAGUES[$row['league_tier']] === $league))
            ->values();
    }

    /**
     * Rank board order, best first: league, Grandmasters by ladder position, division, then players with the
     * games requirement by win rate, then those under it by games played, then read count.
     */
    private function compareRealRanks(array $a, array $b): int
    {
        $score = fn ($row) => $row['eligible'] ? ($row['win_rate'] ?? 0) : $row['games_played'];

        return [$b['grandmaster'], $b['league_tier']] <=> [$a['grandmaster'], $a['league_tier']]
            ?: ($a['grandmaster'] ? ($a['ladder_position'] ?? 1000) <=> ($b['ladder_position'] ?? 1000) : 0)
            ?: ($a['division'] ?? 0) <=> ($b['division'] ?? 0)
            ?: $b['eligible'] <=> $a['eligible']
            ?: $score($b) <=> $score($a)
            ?: $b['read_count'] <=> $a['read_count'];
    }

    public function getLeaderboardRating(Request $request)
    {
        // Only ever the signed-in user's own rating. The account is never taken from
        // the request, which would let anyone look up anyone, private players included.
        $user = Auth::user();

        if ($user === null) {
            return ['rating' => 0, 'games_played' => 0];
        }

        $validationRules = [
            'season' => ['required', new SeasonInputValidation],
            'game_type' => ['required', new GameTypeInputValidation],
            'type' => 'required|in:player,hero,role',
            'groupsize' => ['required', new StackSizeInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }
        $blizz_id = $user->blizz_id;
        $hero = $request['hero'];
        $role = $this->globalDataService->getMMRTypeValue($request['role']);

        $gameType = $this->globalDataService->getGameTypeFilterValues($request['game_type']);
        $season = $request['season'];
        $region = $user->region;

        $type = $request['type'];
        $typeNumber = 0;

        if ($type == 'player') {
            $typeNumber = $this->globalDataService->getMMRTypeValue($request['type']);
        } elseif ($type == 'hero') {
            $typeNumber = $hero;
        } elseif ($type == 'role') {
            $typeNumber = $role;
        }

        // Mirrors CalculateLeaderboards (Calculate.getPlayers), so the estimate is the
        // rating the board would give. `All` reads the ungrouped table; Solo takes
        // stack_size 0 and 1, and like the board scores each row on its own and keeps
        // the best rather than adding them together.
        $stackSizes = match ($request['groupsize']) {
            'Solo' => [0, 1],
            'Duo' => [2],
            '3 Players' => [3],
            '4 Players' => [4],
            '5 Players' => [5],
            default => [0],
        };

        $table = $request['groupsize'] == 'All' ? MasterGamesPlayedData::class : MasterGamesPlayedDataGroups::class;
        $season = $this->globalDataService->getDefaultSeason();
        $weeks = $this->globalDataService->getWeeksSinceSeasonStart();

        $board = fn () => $table::query()
            ->where('type_value', $typeNumber)
            ->whereIn('stack_size', $stackSizes)
            ->where('season', $season)
            ->where('game_type', $gameType);

        $playerRows = $board()
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get(['win_leaderboard', 'loss_leaderboard', 'games_played_leaderboard']);

        if ($playerRows->isEmpty()) {
            return ['rating' => 0, 'games_played' => 0];
        }

        // The average of the top `weeks` players' games, and the fewest games among
        // the top `weeks * 20`: the divisor and the cap in the formula below.
        $topAverage = $board()->orderByDesc('games_played_leaderboard')->limit($weeks)->pluck('games_played_leaderboard')->avg();
        $maxGamesPlayed = $topAverage === null ? 1 : (int) floor($topAverage);
        $maxMinGamesPlayed = (int) ($board()->orderByDesc('games_played_leaderboard')->limit($weeks * 20)->pluck('games_played_leaderboard')->min() ?? 0);

        $mmrTable = match ((int) $gameType) {
            1 => MasterMMRDataQM::class,
            2 => MasterMMRDataUD::class,
            3 => MasterMMRDataHL::class,
            4 => MasterMMRDataTL::class,
            5 => MasterMMRDataSL::class,
            6 => MasterMMRDataAR::class,
            default => null,
        };

        $conservativeRating = $mmrTable === null ? null : $mmrTable::where('type_value', $typeNumber)
            ->where('game_type', $gameType)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->value('conservative_rating');

        // The board inner-joins the rating, so a player without one is not on it.
        if ($conservativeRating === null) {
            return ['rating' => 0, 'games_played' => (int) $playerRows->max('games_played_leaderboard')];
        }

        $best = null;

        foreach ($playerRows as $row) {
            $decided = $row->win_leaderboard + $row->loss_leaderboard;
            $winRate = $decided > 0 ? ($row->win_leaderboard / $decided) * 100 : 0;
            $cappedGames = min($row->games_played_leaderboard, $maxMinGamesPlayed);
            $rating = 50 + ($winRate - 50) * ($cappedGames / $maxGamesPlayed) + ($conservativeRating / 10);

            if ($best === null || $rating > $best['rating']) {
                $best = ['rating' => $rating, 'games_played' => (int) $row->games_played_leaderboard];
            }
        }

        return ['rating' => round($best['rating'], 2), 'games_played' => $best['games_played']];
    }
}
