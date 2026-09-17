<?php

namespace App\Http\Controllers\Global;

use App\Models\BannedLeaderboardAccounts;
use App\Models\BattlenetAccount;
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
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RegionInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\SeasonInputValidation;
use App\Rules\StackSizeInputValidation;
use App\Rules\TierInputByIDValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GlobalLeaderboardController extends GlobalsInputValidationController
{
    private $rankModifier = 0;

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
            'type' => 'required|in:match prediction,player,hero,role',
            'groupsize' => ['required', new StackSizeInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputByIDValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'tierrank' => ['sometimes', 'nullable', new TierInputByIDValidation],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
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

            // $weeksDifference = $this->globalDataService->matchPredictionGetWeeksSinceSeasonStart();

            $authenticatedUser = Auth::user();

            $filteredLeaderboard = $leaderboard->filter(function ($item) use ($authenticatedUser) {
                if ($item->games_played < 20) {
                    return false;
                }

                return ! $this->globalDataService->isHiddenFrom($item->blizz_id, $item->region, $authenticatedUser);
            })->values();

            $sortedLeaderboard = $filteredLeaderboard->sortByDesc('rating');
            $counter = 1;

            $sortedLeaderboard->each(function ($item) use (&$counter) {
                $item->rank = $counter;
                $item->split_battletag = explode('#', $item->battletag)[0];
                $item->region_id = $item->region;
                $item->region = $this->globalDataService->getRegionIDtoString()[$item->region];
                $counter++;
                unset($item->battlenet_accounts_id);
            });

            $data = $sortedLeaderboard->values();
        }

        return $data;
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
