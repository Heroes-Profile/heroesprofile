<?php

namespace App\Services;

use App\Models\BattlenetAccount;
use App\Models\Battletag;
use App\Models\CCL\CCLTeam;
use App\Models\GameType;
use App\Models\GlobalHeroStats;
use App\Models\HeaderAlert;
use App\Models\Hero;
use App\Models\HeroesDataTalent;
use App\Models\LeagueTier;
use App\Models\Map;
use App\Models\MastersClash\MastersClashTeam;
use App\Models\MatchPredictionSeason;
use App\Models\MMRTypeID;
use App\Models\NGS\NGSTeam;
use App\Models\Replay;
use App\Models\SeasonDate;
use App\Models\SeasonGameVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GlobalDataService
{
    private $cachedHeroes = null;
    private $cachedGameTypes = null;
    private $cachedSeasonsData = null;

    public function __construct() {}

    public function getHeaderAlert()
    {
        $text = HeaderAlert::where('valid', 1)->value('text');

        return $text ?? null;
    }

    public function getPrivateAccounts()
    {
        $privateAccounts = BattlenetAccount::select('battletag', 'blizz_id', 'region')->where('private', 1)->get();
        $filteredAccounts = $privateAccounts->map(function ($account) {
            return [
                'battletag' => $account->battletag,
                'blizz_id' => $account->blizz_id,
                'region' => $account->region,
            ];
        });

        return $filteredAccounts;
    }

    public function calculateMaxReplayNumber()
    {
        return Replay::max('replayID');
    }

    public function getDefaultTimeframeType()
    {
        if (Auth::check()) {
            $user = Auth::user();
        }

        return 'minor';
    }

    public function getDefaultTimeframe()
    {
        return SeasonGameVersion::select('game_version')->where('valid_globals', 1)->orderBy('major', 'DESC')->orderBy('minor', 'DESC')->orderBy('patch', 'DESC')->orderBy('build', 'DESC')->first()->game_version;
    }

    public function getDefaultBuildType()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $talentbuildtype = $user->userSettings->firstWhere('setting', 'talentbuildtype');

            return $talentbuildtype ? $talentbuildtype->value : 'Popular';

        }

        return 'Popular';
    }

    public function getLatestPatch()
    {
        return SeasonGameVersion::orderBy('id', 'desc')->limit(1)->value('game_version');
    }

    public function getLatestGameDate()
    {
        return Replay::where('game_date', '<=', now())
            ->orderByDesc('game_date')
            ->value('game_date');
    }

    public function getBladeGlobals()
    {
        $darkModeValue = null;

        if (Auth::check()) {
            $user = Auth::user();

            $darkmode = $user->userSettings->firstWhere('setting', 'darkmode');

            $darkModeValue = $darkmode ? $darkmode->value : '0';
        }

        $regions = $this->getRegionIDtoString();
        $latestGame = $this->getLatestGameDate();
        $latestGameUtc = Carbon::parse($latestGame, 'UTC');
        $nowUtc = Carbon::now('UTC');

        $isBackendOff = $nowUtc->diffInSeconds($latestGameUtc) > (3 * 60 * 60); // 6 hours

        return [
            'regions' => $regions,
            'darkmode' => $darkModeValue,
            'maxReplayID' => $this->calculateMaxReplayNumber(),
            'latestPatch' => $this->getLatestPatch(),
            'latestGameDate' => $latestGame,
            'isBackendOff' => $isBackendOff,
            'headeralert' => $this->getHeaderAlert(),
        ];

    }

    public function showcustomgames($battletag, $blizz_id, $region)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->blizz_id != $blizz_id || $user->region != $region) {
                return false;
            }

            $customgames = $user->userSettings->firstWhere('setting', 'customgames');

            if ($customgames) {
                $customgames = $customgames->value == 1 ? true : false;

            }

            return $customgames;
        }

        return false;
    }

    public function getPlayerLoadSettings()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $playerload = $user->userSettings->firstWhere('setting', 'playerload');

            $playerload = $playerload ? $playerload->value : true;

            return $playerload;
        }

        return true;
    }

    public function getPlayerMatchStyle()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $playerhistorytable = $user->userSettings->firstWhere('setting', 'playerhistorytable');

            $playerhistorytable = $playerhistorytable ? $playerhistorytable->value : true;

            return $playerhistorytable;
        }

        return false;
    }

    public function getRegionIDtoString()
    {
        return $regions = [
            1 => 'NA',
            2 => 'EU',
            3 => 'KR',
            /*  4 => "UNK", */
            5 => 'CN',
        ];
    }

    public function getRegionStringToID()
    {
        return $regions = [
            'NA' => 1,
            'EU' => 2,
            'KR' => 3,
            /*  4 => "UNK", */
            'CN' => 5,
        ];
    }

    public function getGameTypeIDtoString()
    {
        $game_types = GameType::all();
        $game_types = $game_types->keyBy('type_id');

        return $game_types;
    }

    public function getWeeksSinceSeasonStart()
    {
        $currentSeasonStartDate = SeasonDate::orderBy('id', 'desc')->limit(1)->value('start_date');
        $startDate = Carbon::parse($currentSeasonStartDate);
        $currentDate = Carbon::now();

        return $startDate->diffInWeeks($currentDate);
    }

    public function matchPredictionGetWeeksSinceSeasonStart()
    {
        $startDate = MatchPredictionSeason::orderBy('match_prediction_season_id', 'desc')->limit(1)->value('start_date');
        $startDateCarbon = Carbon::parse($startDate);
        $currentDate = Carbon::now();

        return $startDateCarbon->diffInWeeks($currentDate);
    }

    public function getBlizzIDGivenFullBattletag($battletag, $region)
    {
        $blizzID = Battletag::where('battletag', $battletag)
            ->where('region', $region)
            ->orderBy('latest_game', 'DESC')
            ->first();

        if (is_null($blizzID)) {
            return null;
        } else {
            return $blizzID->blizz_id;
        }
    }

    public function calculateCacheTimeInMinutes($timeframe)
    {
        // return 300;

        if (app()->environment('production')) {
            if (count($timeframe) == 1 && $timeframe[0] == $this->getLatestPatch()) {
                $date = SeasonGameVersion::where('game_version', min($timeframe))->value('date_added');
                $changeInMinutes = Carbon::now()->diffInMinutes(new Carbon($date));

                if ($changeInMinutes < 1440) {  // 1 day
                    return .25; // 15 min
                } elseif ($changeInMinutes < (1440 * 3.5)) { // half week
                    return 6 * 60; // 6 hours
                } elseif ($changeInMinutes < (1440 * 7)) { // 1 week
                    return 24 * 60; // 1 day
                } elseif ($changeInMinutes < (1440 * 2)) { // 2 week
                    return 24 * 60 * 7; // 7 day
                } else {
                    return 24 * 60 * 7 * 2; // 2 weeks
                }
            } else {
                $date = SeasonGameVersion::where('game_version', min($timeframe))->value('date_added');
                $changeInMinutes = Carbon::now()->diffInMinutes(new Carbon($date));

                return $changeInMinutes;
            }
        }

        return 0;
    }

    public function getGameTypes()
    {
        if ($this->cachedGameTypes === null) {
            $this->cachedGameTypes = GameType::orderBy('type_id', 'ASC')->get();
        }
        return clone $this->cachedGameTypes;
    }

    public function getHeroes()
    {
        if ($this->cachedHeroes === null) {
            $this->cachedHeroes = Hero::orderBy('name', 'ASC')->get();
        }
        return clone $this->cachedHeroes;
    }

    public function getHeroesByID()
    {
        $heroData = $this->getHeroes();
        $heroData = $heroData->keyBy('id');

        return $heroData;
    }

    public function getHeroModel($heroName)
    {
        return Hero::firstWhere('name', $heroName);
    }

    public function getSeasonsData()
    {
        if ($this->cachedSeasonsData === null) {
            $this->cachedSeasonsData = SeasonDate::orderBy('id', 'desc')->get();
        }
        return clone $this->cachedSeasonsData;
    }

    public function getSeasonFromDate($date)
    {
        return SeasonDate::select('id')->where('start_date', '<=', $date)->where('end_date', '>=', $date)->first()->id;
    }

    public function getAdvancedFilterShowDefault()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $advancedfiltering = $user->userSettings->firstWhere('setting', 'advancedfiltering');

            return $advancedfiltering && $advancedfiltering->value;
        }

        return false;
    }

    public function getGameTypeDefault($type)
    {

        if (Auth::check()) {
            $user = Auth::user();

            if ($type == 'single') {
                $gameTypeSetting = $user->userSettings->firstWhere('setting', 'game_type');
                if ($gameTypeSetting) {
                    return [$gameTypeSetting->value];
                }
            } else {
                $gameTypeSetting = $user->userSettings->firstWhere('setting', 'multi_game_type');
                if ($gameTypeSetting) {
                    $gameTypeValue = $gameTypeSetting->value;

                    return explode(',', $gameTypeSetting->value);
                }
            }

        }

        return ['sl'];
    }

    public function getMMRGameTypeDefault()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $gameTypeSetting = $user->userSettings->firstWhere('setting', 'mmr_player_game_type');
            if ($gameTypeSetting) {
                return [$gameTypeSetting->value];
            }

        }

        return ['sl'];
    }

    public function getDefaultSeason()
    {
        return SeasonDate::select('id')->orderBy('id', 'DESC')->first()->id;
    }

    public function getDefaultMatchPredictionSeason()
    {
        return MatchPredictionSeason::select('match_prediction_season_id')->orderBy('match_prediction_season_id', 'DESC')->first()->match_prediction_season_id;
    }

    /**
     * Parse a version string into its numeric components
     */
    private function parseVersionString($versionString)
    {
        $parts = explode('.', $versionString);

        return [
            'major' => (int) ($parts[0] ?? 0),
            'minor' => (int) ($parts[1] ?? 0),
            'patch' => (int) ($parts[2] ?? 0),
            'build' => (int) ($parts[3] ?? 0),
        ];
    }

    /**
     * Apply version filtering to a query using numeric comparison
     */
    private function applyVersionFilter($query, $minimumVersionString)
    {
        $minVersion = $this->parseVersionString($minimumVersionString);

        return $query->where(function ($q) use ($minVersion) {
            $q->where('major', '>', $minVersion['major'])
                ->orWhere(function ($q2) use ($minVersion) {
                    $q2->where('major', '=', $minVersion['major'])
                        ->where('minor', '>', $minVersion['minor']);
                })
                ->orWhere(function ($q3) use ($minVersion) {
                    $q3->where('major', '=', $minVersion['major'])
                        ->where('minor', '=', $minVersion['minor'])
                        ->where('patch', '>', $minVersion['patch']);
                })
                ->orWhere(function ($q4) use ($minVersion) {
                    $q4->where('major', '=', $minVersion['major'])
                        ->where('minor', '=', $minVersion['minor'])
                        ->where('patch', '=', $minVersion['patch'])
                        ->where('build', '>=', $minVersion['build']);
                });
        });
    }

    public function getFilterData($overrideDefaultPatchVersion = false, $defaultPatchVersion = null)
    {
        $filtersMinimumPatch = '2.53.0.83004';
        if (Auth::check()) {
            $user = Auth::user();

            if ($this->checkIfSiteFlair($user->blizz_id, $user->region) || $this->isOwner($user->blizz_id, $user->region)) {
                $filtersMinimumPatch = '2.52.0.81700';
            }
        }

        if ($overrideDefaultPatchVersion) {
            $filtersMinimumPatch = $defaultPatchVersion;
        }

        $filterData = new \stdClass;

        $filterData->timeframe_type = [
            ['code' => 'major', 'name' => 'Major Patch'],
            ['code' => 'major_grouped', 'name' => 'Major Sub Patch'],
            ['code' => 'minor', 'name' => 'Minor Patch'],
        ];

        $timeframesQuery = SeasonGameVersion::select('game_version')
            ->where('valid_globals', 1);

        $filterData->timeframes = $this->applyVersionFilter($timeframesQuery, $filtersMinimumPatch)
            ->orderBy('major', 'DESC')
            ->orderBy('minor', 'DESC')
            ->orderBy('patch', 'DESC')
            ->get()
            ->map(function ($item) {
                return ['code' => $item->game_version, 'name' => $item->game_version];
            });

        $timeframesGroupedQuery = SeasonGameVersion::select('game_version')
            ->where('valid_globals', 1);

        $filterData->timeframes_grouped = $this->applyVersionFilter($timeframesGroupedQuery, $filtersMinimumPatch)
            ->orderBy('major', 'DESC')
            ->orderBy('minor', 'DESC')
            ->orderBy('patch', 'DESC')
            ->get()
            ->groupBy(function ($date) {
                return substr($date->game_version, 0, 4);  // group by years (first 4 characters)
            })
            ->map(function ($grouped) {
                return $grouped->first();  // pick the first item from each group
            })
            ->values()  // reset the array keys
            ->map(function ($item) {
                return ['code' => substr($item->game_version, 0, 4), 'name' => substr($item->game_version, 0, 4)];  // use the first 4 characters
            });

        $timeframesSubGroupedQuery = SeasonGameVersion::select('game_version')
            ->where('valid_globals', 1);

        $filterData->timeframes_sub_grouped = $this->applyVersionFilter($timeframesSubGroupedQuery, $filtersMinimumPatch)
            ->orderBy('major', 'DESC')
            ->orderBy('minor', 'DESC')
            ->orderBy('patch', 'DESC')
            ->get()
            ->groupBy(function ($date) {
                // Group by major.minor.patch (first three segments)
                $segments = explode('.', $date->game_version);

                return isset($segments[2]) ? "{$segments[0]}.{$segments[1]}.{$segments[2]}" : "{$segments[0]}.{$segments[1]}";
            })
            ->map(function ($grouped) {
                return $grouped->first(); // Pick the first item from each group
            })
            ->values()  // Reset the array keys
            ->map(function ($item) {
                $segments = explode('.', $item->game_version);
                $code = isset($segments[2]) ? "{$segments[0]}.{$segments[1]}.{$segments[2]}" : "{$segments[0]}.{$segments[1]}";

                return ['code' => $code, 'name' => $code];  // Use the first three segments
            });

        $filterData->regions = [
            ['code' => 'NA', 'name' => 'NA'],
            ['code' => 'EU', 'name' => 'EU'],
            ['code' => 'KR', 'name' => 'KR'],
            ['code' => 'CN', 'name' => 'CN'],
        ];

        $filterData->stat_filter = [
            ['code' => 'win_rate', 'name' => 'Win Rate'],
            ['code' => 'assists', 'name' => 'Assists'],
            ['code' => 'clutch_heals', 'name' => 'Clutch Heals'],
            ['code' => 'creep_damage', 'name' => 'Lane Merc. Damage'],
            ['code' => 'damage_taken', 'name' => 'Damage Taken'],
            ['code' => 'deaths', 'name' => 'Deaths'],
            ['code' => 'escapes', 'name' => 'Escapes'],
            ['code' => 'experience_contribution', 'name' => 'Experience Contribution'],
            ['code' => 'game_time', 'name' => 'Game Time'],
            ['code' => 'healing', 'name' => 'Healing'],
            ['code' => 'hero_damage', 'name' => 'Hero Damage'],
            ['code' => 'highest_kill_streak', 'name' => 'Highest Kill Streak'],
            ['code' => 'kills', 'name' => 'Kills'],
            ['code' => 'merc_camp_captures', 'name' => 'Merc Camp Captures'],
            ['code' => 'minion_damage', 'name' => 'Minion Damage'],
            ['code' => 'multikill', 'name' => 'Multikill'],
            ['code' => 'outnumbered_deaths', 'name' => 'Outnumbered Deaths'],
            ['code' => 'physical_damage', 'name' => 'Physical Damage'],
            ['code' => 'protection_Allies', 'name' => 'Protection Allies'],
            ['code' => 'regen_globes', 'name' => 'Regen Globes'],
            ['code' => 'rooting_enemies', 'name' => 'Rooting Enemies'],
            ['code' => 'self_healing', 'name' => 'Self Healing'],
            ['code' => 'siege_damage', 'name' => 'Siege Damage'],
            ['code' => 'silencing_enemies', 'name' => 'Silencing Enemies'],
            ['code' => 'spell_damage', 'name' => 'Spell Damage'],
            ['code' => 'stunning_enemies', 'name' => 'Stunning Enemies'],
            ['code' => 'summon_damage', 'name' => 'Summon Damage'],
            ['code' => 'takedowns', 'name' => 'Takedowns'],
            ['code' => 'teamfight_damage_taken', 'name' => 'Teamfight Damage Taken'],
            ['code' => 'teamfight_escapes', 'name' => 'Teamfight Escapes'],
            ['code' => 'teamfight_healing', 'name' => 'Teamfight Healing'],
            ['code' => 'teamfight_hero_damage', 'name' => 'Teamfight Hero Damage'],
            ['code' => 'time_spent_dead', 'name' => 'Time Spent Dead'],
            ['code' => 'town_kills', 'name' => 'Town Kills'],
            ['code' => 'vengeance', 'name' => 'Vengeance'],
            ['code' => 'watch_tower_captures', 'name' => 'Watch Tower Captures'],
        ];

        $filterData->hero_level = [
            ['code' => '1', 'name' => '1-5'],
            ['code' => '5', 'name' => '5-10'],
            ['code' => '10', 'name' => '10-15'],
            ['code' => '15', 'name' => '15-25'],
            ['code' => '25', 'name' => '25-40'],
            ['code' => '40', 'name' => '40-60'],
            ['code' => '60', 'name' => '60-80'],
            ['code' => '80', 'name' => '80-100'],
            ['code' => '100', 'name' => '100+'],
        ];

        $filterData->role = [
            ['code' => 'Bruiser', 'name' => 'Bruiser'],
            ['code' => 'Healer', 'name' => 'Healer'],
            ['code' => 'Melee Assassin', 'name' => 'Melee Assassin'],
            ['code' => 'Ranged Assassin', 'name' => 'Ranged Assassin'],
            ['code' => 'Support', 'name' => 'Support'],
            ['code' => 'Tank', 'name' => 'Tank'],
        ];

        $filterData->heroes = $this->getHeroes()->map(function ($hero) {
            return ['code' => $hero->id, 'name' => $hero->name];
        });

        $filterData->game_types = GameType::whereNotIn('type_id', [-1, 0, 3, 4])
            ->orderBy('type_id', 'ASC')
            ->get()
            ->map(function ($gameType) {
                return ['code' => $gameType->short_name, 'name' => $gameType->name];
            });

        $filterData->game_types_full = GameType::whereNotIn('type_id', [-1, 0])
            ->orderBy('type_id', 'ASC')
            ->get()
            ->map(function ($gameType) {
                return ['code' => $gameType->short_name, 'name' => $gameType->name];
            });

        $filterData->game_types_full_add_custom = GameType::whereNotIn('type_id', [-1])
            ->orderBy('type_id', 'ASC')
            ->get()
            ->map(function ($gameType) {
                return ['code' => $gameType->short_name, 'name' => $gameType->name];
            });

        $filterData->game_maps = Map::where('playable', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->map(function ($map) {
                return ['code' => $map->name, 'name' => $map->name];
            });

        $filterData->rank_tiers = LeagueTier::whereNot('tier_id', 7)->orderBy('tier_id', 'DESC')->get()->map(function ($tiers) {
            return ['code' => $tiers->tier_id, 'name' => ucfirst($tiers->name)];
        });

        $filterData->mirror = [
            ['code' => '0', 'name' => 'Exclude'],
            ['code' => '1', 'name' => 'Include'],
        ];

        $filterData->talent_build_types = [
            ['code' => 'Popular', 'name' => 'Popular'],
            ['code' => 'HP Algorithm', 'name' => 'HP Algorithm'],
            ['code' => 'Unique Lvl 1', 'name' => 'Unique Lvl 1'],
            ['code' => 'Unique Lvl 4', 'name' => 'Unique Lvl 4'],
            ['code' => 'Unique Lvl 7', 'name' => 'Unique Lvl 7'],
            ['code' => 'Unique Lvl 10', 'name' => 'Unique Lvl 10'],
            ['code' => 'Unique Lvl 13', 'name' => 'Unique Lvl 13'],
            ['code' => 'Unique Lvl 16', 'name' => 'Unique Lvl 16'],
            ['code' => 'Unique Lvl 20', 'name' => 'Unique Lvl 20'],
        ];

        $filterData->minimum_games = [];
        for ($i = 0; $i <= 5000; $i += 25) {
            $filterData->minimum_games[] = ['code' => (string) $i, 'name' => (string) $i];
        }

        $filterData->hero_party_size = [];
        for ($i = 1; $i <= 5; $i++) {
            $filterData->hero_party_size[] = ['code' => (string) $i, 'name' => (string) $i];
        }

        $filterData->party_combinations = [
            ['code' => '00005', 'name' => '5 Solos'],
            ['code' => '00023', 'name' => '3 Solos and 1 Duo'],
            ['code' => '00041', 'name' => '1 Solo and 2 Duo'],
            ['code' => '00302', 'name' => '2 Solos and 1 Triple'],
            ['code' => '00320', 'name' => '1 Duo and 1 Triple'],
            ['code' => '04001', 'name' => '1 Solo and 1 Quad'],
            ['code' => '50000', 'name' => '5 Stack'],
        ];

        $filterData->chart_type = [
            ['code' => 'Account Level', 'name' => 'Account Level'],
            ['code' => 'Hero Level', 'name' => 'Hero Level'],
        ];

        $filterData->minimum_account_level = [
            ['code' => '0', 'name' => '0'],
            ['code' => '25', 'name' => '25'],
            ['code' => '50', 'name' => '50'],
            ['code' => '100', 'name' => '100'],
            ['code' => '250', 'name' => '250'],
            ['code' => '500', 'name' => '500'],
            ['code' => '1000', 'name' => '1000'],
            ['code' => '2000', 'name' => '2000'],
            ['code' => '4000', 'name' => '4000'],
        ];

        $filterData->x_axis_increments = [
            ['code' => '1', 'name' => '1'],
            ['code' => '25', 'name' => '25'],
            ['code' => '50', 'name' => '50'],
            ['code' => '100', 'name' => '100'],
            ['code' => '250', 'name' => '250'],
        ];

        $filterData->type = [
            ['code' => 'Player', 'name' => 'Player'],
            ['code' => 'Hero', 'name' => 'Hero'],
            ['code' => 'Role', 'name' => 'Role'],
        ];

        $filterData->leaderboard_type = [
            ['code' => 'Player', 'name' => 'Player'],
            ['code' => 'Hero', 'name' => 'Hero'],
            ['code' => 'Role', 'name' => 'Role'],
            ['code' => 'Match Prediction', 'name' => 'Match Prediction'],
        ];

        $filterData->group_size = [
            ['code' => 'All', 'name' => 'All'],
            ['code' => 'Solo', 'name' => 'Solo'],
            ['code' => 'Duo', 'name' => 'Duo'],
            ['code' => '3 Players', 'name' => '3 Players'],
            ['code' => '4 Players', 'name' => '4 Players'],
            ['code' => '5 Players', 'name' => '5 Players'],
        ];

        $filterData->seasons = SeasonDate::select('id', 'year', 'season')->orderBy('id', 'DESC')->get()->map(function ($data) {
            return ['code' => $data->id, 'name' => $data->year.' Season '.$data->season];
        });

        $filterData->match_prediction_seasons = MatchPredictionSeason::select('match_prediction_season_id', 'season', 'start_date')->orderBy('match_prediction_season_id', 'DESC')->get()->map(function ($data) {
            return ['code' => $data->match_prediction_season_id, 'name' => 'Season '.$data->season];
        });

        $filterData->hero_role = [
            ['code' => 'Hero', 'name' => 'Hero'],
            ['code' => 'Role', 'name' => 'Role'],
        ];

        $filterData->ngs_divisions = NGSTeam::distinct()->orderBy('division', 'asc')->pluck('division')->map(function ($division) {
            return ['code' => $division, 'name' => $division];
        });

        $filterData->ngs_seasons = NGSTeam::distinct()->orderBy('season', 'desc')->pluck('season')->map(function ($season) {
            return ['code' => $season, 'name' => strval($season)];
        });

        $filterData->mcl_seasons = MastersClashTeam::distinct()->orderBy('season', 'desc')->pluck('season')->map(function ($season) {
            return ['code' => $season, 'name' => strval($season)];
        });

        $filterData->ccl_seasons = CCLTeam::distinct()->orderBy('season', 'desc')->pluck('season')->map(function ($season) {
            return ['code' => $season, 'name' => strval($season)];
        });

        $filterData->nut_cup_seasons = [
            ['code' => '1', 'name' => '1'],
            ['code' => '2', 'name' => '2'],
        ];

        return $filterData;
    }

    public function getRankTiers($game_type, $type)
    {
        $result = DB::table('league_breakdowns')
            ->select('game_type', 'league_tier', 'min_mmr')
            ->where('type_role_hero', $type)
            ->where('game_type', $game_type)
            ->get();

        $returnData = [];
        $prevMin = 0;

        foreach ($result as $row) {
            $data = [];
            $data['min_mmr'] = $prevMin;
            $data['max_mmr'] = round($row->min_mmr);
            $prevMin = round($row->min_mmr);

            if ($data['min_mmr'] == 0) {
                $data['split'] = ($data['max_mmr'] - 1800) / 5;
            } else {
                $data['split'] = ($data['max_mmr'] - $data['min_mmr']) / 5;
            }

            switch ($row->league_tier) {
                case '2':
                    $returnData['bronze'] = $data;
                    break;
                case '3':
                    $returnData['silver'] = $data;
                    break;
                case '4':
                    $returnData['gold'] = $data;
                    break;
                case '5':
                    $returnData['platinum'] = $data;
                    break;
                case '6':
                    $returnData['diamond'] = $data;
                    break;
            }
        }

        $data['min_mmr'] = $prevMin;
        $data['max_mmr'] = '';
        $returnData['master'] = $data;

        return $returnData;
    }

    public function calculateSubTier($rankTiers, $mmr)
    {
        $tierNames = [
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
            'diamond' => 'Diamond',
            'master' => 'Master',
        ];

        $result = '';

        $counter = 4;
        $multiply = 1;

        foreach ($rankTiers as $key => $tierInfo) {
            $minMmr = $tierInfo['min_mmr'];
            $maxMmr = $tierInfo['max_mmr'];
            $split = $tierInfo['split'];

            if ($maxMmr == '') {
                $maxMmr = $minMmr + $split;
            }

            if ($mmr >= $minMmr && $mmr < $maxMmr) {
                if ($tierNames[$key] != 'Master') {
                    if ($mmr < ($minMmr + $split)) {
                        $result = $tierNames[$key].' '.$counter;
                    } else {
                        for ($i = ($minMmr + $split); $i < $maxMmr; $i += $split) {
                            if ($mmr >= $i) {

                                $result = $tierNames[$key].' '.$counter;
                                $counter--;
                            }
                        }
                    }
                } else {
                    $result = 'Master';
                }
            } else {
                if ($mmr >= $minMmr && $mmr >= $maxMmr) {
                    $result = 'Master';
                }
            }
        }

        return $result;
    }

    public function calculateTierID($rankName)
    {
        if ($rankName == 'Master') {
            return 6;
        }

        $split = explode(' ', $rankName);

        if ($split[0] == 'Diamond') {
            return 6 - ($split[1] / 10);
        } elseif ($split[0] == 'Platinum') {
            return 5 - ($split[1] / 10);
        } elseif ($split[0] == 'Gold') {
            return 4 - ($split[1] / 10);
        } elseif ($split[0] == 'Silver') {
            return 3 - ($split[1] / 10);
        } elseif ($split[0] == 'Bronze') {
            return 2 - ($split[1] / 10);
        } elseif ($split[0] == 'Wood') {
            return 1 - ($split[1] / 10);
        }
    }

    public function getSubTiers($tier, $rankTierName)
    {
        $result = [];

        $counter = 5;

        $minMmr = $tier['min_mmr'];
        $maxMmr = $tier['max_mmr'];
        $split = $tier['split'];

        if ($maxMmr == '') {
            $maxMmr = $minMmr + $split;
        }

        for ($i = ($minMmr + $split); $i <= $maxMmr; $i += $split) {
            $result[ucfirst($rankTierName).' '.$counter] = $i;
            $counter--;
        }

        return $result;
    }

    public function getBattletagShort($blizz_id, $region)
    {
        $battletag = Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->orderBy('latest_game', 'desc')
            ->limit(1)
            ->value('battletag');

        if ($battletag) {
            $battletag = explode('#', $battletag)[0];
        }

        return $battletag;
    }

    public function getPreloadTalentImageUrls()
    {
        $talentData = HeroesDataTalent::select('hero_name', 'icon')->get();

        $images = $talentData->groupBy('hero_name')->map(function ($data) {
            return $data->map(function ($item) {
                return '/images/talents/'.$item->icon;
            });
        })->toArray();

        return $images;
    }

    public function getPreloadHeroSmallImageUrls()
    {
        $heroData = Hero::select('short_name')->get();

        return $heroData->pluck('short_name')->toArray();
    }

    public function checkIfSiteFlair($blizz_id, $region)
    {
        $data = BattlenetAccount::where('blizz_id', $blizz_id)->where('region', $region)->first();

        if (! $data || empty($data)) {
            return false;
        }

        $data = $data->patreonAccount;

        if (! $data || empty($data)) {
            return false;
        }

        if ($data->site_flair == 1) {
            return true;
        }

        return false;
    }

    public function isOwner($blizz_id, $region)
    {
        return ($blizz_id == 67280 and $region == 1) ? true : false;
    }

    public function getTimeframeFilterValues($timeframeType, $timeframes)
    {
        if ($timeframeType == 'major' || $timeframeType == 'major_grouped') {
            $query = SeasonGameVersion::select('game_version');

            foreach ($timeframes as $timeframe) {
                $query->orWhere('game_version', 'like', $timeframe.'%');
            }
            $gameVersion = $query->get()
                ->pluck('game_version')
                ->toArray();

            return $gameVersion;
        }

        return $timeframes;
    }

    public function getTimeFrameFilterValuesLastUpdate($hero)
    {
        $game_version = Hero::select('last_change_patch_version')->where('id', $hero)->first()->last_change_patch_version;

        $query = SeasonGameVersion::select('game_version');
        $gameVersion = $this->applyVersionFilter($query, $game_version)->get()->pluck('game_version')->toArray();

        return $gameVersion;
    }

    public function getRegionFilterValues($regions)
    {
        if (is_null($regions)) {
            return null;
        }

        if (! is_array($regions)) {
            return $this->getRegionStringToID()[$regions];
        }

        return array_map(function ($region) {
            return $this->getRegionStringToID()[$region];
        }, $regions);
    }

    public function getGameMapFilterValues($game_maps)
    {
        if (is_null($game_maps)) {
            return null;
        }
        $mapIds = Map::whereIn('name', $game_maps)->pluck('map_id')->toArray();

        return $mapIds;
    }

    public function getHeroFilterValue($hero)
    {
        if (is_null($hero)) {
            return null;
        }

        return $this->getHeroes()->keyBy('name')[$hero]->id;
    }

    public function getGameTypeFilterValues($game_type)
    {
        if (is_array($game_type)) {
            return GameType::whereIn('short_name', $game_type)->pluck('type_id')->toArray();
        } else {
            return GameType::where('short_name', $game_type)->pluck('type_id')->first();
        }
    }

    public function getMMRTypeValue($input)
    {
        return MMRTypeID::where('name', $input)->pluck('mmr_type_id')->first();
    }

    public function getAllHeroesGlobalWinRates(Request $request)
    {
        $gameVersion = $this->getTimeframeFilterValues($request['timeframe_type'], $request['timeframe']);
        $gameType = $this->getGameTypeFilterValues($request['game_type']);
        $leagueTier = $request['league_tier'];
        $heroLeagueTier = $request['hero_league_tier'];
        $roleLeagueTier = $request['role_league_tier'];
        $gameMap = $this->getGameMapFilterValues($request['game_map']);
        $heroLevel = $request['hero_level'];
        $mirror = $request['mirror'];
        $region = $this->getRegionFilterValues($request['region']);
        $hero = $this->getHeroFilterValue($request['hero']);
        $role = $request['role'];

        $cacheKey = 'GlobalHeroStats|'.hash('sha256', json_encode($gameVersion).'|'.json_encode($request->all()));

        $data = Cache::store('database')->remember($cacheKey, $this->calculateCacheTimeInMinutes($gameVersion), function () use ($gameVersion,
            $gameType,
            $leagueTier,
            $heroLeagueTier,
            $roleLeagueTier,
            $gameMap,
            $heroLevel,
            $region,
            $mirror
        ) {
                $data = GlobalHeroStats::query()
                    ->join('heroes', 'heroes.id', '=', 'global_hero_stats.hero')
                    ->select('heroes.name', 'heroes.short_name', 'heroes.id as hero_id', 'global_hero_stats.win_loss', 'heroes.new_role as role')
                    ->selectRaw('SUM(global_hero_stats.games_played) as games_played')
                    ->filterByGameVersion($gameVersion)
                    ->filterByGameType($gameType)
                    ->filterByLeagueTier($leagueTier)
                    ->filterByHeroLeagueTier($heroLeagueTier)
                    ->filterByRoleLeagueTier($roleLeagueTier)
                    ->filterByGameMap($gameMap)
                    ->filterByHeroLevel($heroLevel)
                    ->excludeMirror($mirror)
                    ->filterByRegion($region)
                    ->groupBy('global_hero_stats.hero', 'global_hero_stats.win_loss')
                    ->groupBy('global_hero_stats.win_loss')  // Ensure win_loss is in GROUP BY
                    ->get();

                $sorted = $data->sortBy(function ($item) {
                    return [$item->name, $item->win_loss];
                })->values();

                return $this->combineData($sorted);
            });

        return $data;
    }

    private function combineData($data)
    {
        $totalGamesPlayed = collect($data)->sum('games_played') / 10;

        $combinedData = collect($data)->groupBy('name')->map(function ($group) use ($totalGamesPlayed) {
            $firstItem = $group->first();

            $wins = $group->where('win_loss', 1)->sum('games_played');
            $losses = $group->where('win_loss', 0)->sum('games_played');
            $gamesPlayed = $wins + $losses;

            $winRate = 0;
            if ($gamesPlayed > 0) {
                $winRate = ($wins / $gamesPlayed) * 100;
            }

            $popularity = (($gamesPlayed) / $totalGamesPlayed) * 100;
            $pickRate = ($gamesPlayed / $totalGamesPlayed) * 100;

            $adjustedPickRate = (($gamesPlayed / $totalGamesPlayed) * 100) / 100;
            $influence = round((($winRate / 100) - 0.5) * ($adjustedPickRate * 10000));

            $confidenceInterval = $this->calculateWinRateConfidenceInterval($wins, $gamesPlayed);

            $statFilterTotal = $group->sum('total_filter_type');

            return [
                'name' => $firstItem['name'],
                'short_name' => $firstItem['short_name'],
                'hero_id' => $firstItem['hero_id'],
                'role' => $firstItem['role'],
                'wins' => $wins,
                'losses' => $losses,
                'games_played' => $gamesPlayed,
                'win_rate' => round($winRate, 2),
                'popularity' => round($popularity, 2),
                'pick_rate' => round($pickRate, 2),
                'influence' => $influence,
                'confidence_interval' => round($confidenceInterval, 2),
                'total_filter_type' => $gamesPlayed > 0 ? round($statFilterTotal / $gamesPlayed, 2) : 0,
            ];
        })->sortByDesc('win_rate')->values()->toArray();

        $combinedCollection = collect($combinedData);

        $positiveInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] > 0;
        });

        $negativeInfluenceCollection = $combinedCollection->filter(function ($item) {
            return $item['influence'] < 0;
        });

        $averageWinRate = $combinedCollection->avg('win_rate');
        $averageConfidenceInterval = $combinedCollection->avg('confidence_interval');
        $averagePopularity = $combinedCollection->avg('popularity');
        $averagePickRate = $combinedCollection->avg('pick_rate');
        $averagePositiveInfluence = $positiveInfluenceCollection->avg('influence');
        $averageNegativeInfluence = $negativeInfluenceCollection->avg('influence');
        $averageGamesPlayed = count($combinedCollection) > 0 ? $combinedCollection->sum('games_played') / count($combinedCollection) : 0;
        $averageTotalFilterType = $combinedCollection->avg('total_filter_type');

        return $combinedData;
    }

    public function calculateWinRateConfidenceInterval($wins, $totalGamesPlayed, $confidenceLevel = 0.95)
    {
        if ($totalGamesPlayed == 0) {
            return 0; // Or whatever you'd like to return when no games are played
        }

        $winRate = ($wins / $totalGamesPlayed) * 100;
        $zScore = 1.96; // For a 95% confidence level, you might want to map other confidence levels to their respective z-scores

        $confidence = ($zScore * sqrt(($winRate / 100 * (1 - $winRate / 100)) / $totalGamesPlayed)) * 100;

        return $confidence;
    }
}
