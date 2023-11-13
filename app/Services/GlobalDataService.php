<?php

namespace App\Services;

use App\Models\BattlenetAccount;
use App\Models\Battletag;
use App\Models\CCL\CCLTeam;
use App\Models\GameType;
use App\Models\Hero;
use App\Models\HeroesDataTalent;
use App\Models\LeagueTier;
use App\Models\Map;
use App\Models\MastersClash\MastersClashTeam;
use App\Models\NGS\NGSTeam;
use App\Models\Replay;
use App\Models\SeasonDate;
use App\Models\SeasonGameVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GlobalDataService
{
    private $filtersMinimumPatch;

    public function __construct()
    {
        //can add modifier here for patreons to reduce what they can filter on
        $this->filtersMinimumPatch = '2.53.0.83004';
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
        if (! session()->has('maxReplayID')) {
            session(['maxReplayID' => Replay::max('replayID')]);
        }

        return session('maxReplayID');
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
        if (! session()->has('defaulttimeframe')) {
            session(['defaulttimeframe' => SeasonGameVersion::select('game_version')->orderBy('game_version', 'DESC')->first()->game_version]);
        }

        return session('defaulttimeframe');
    }

    public function getDefaultBuildType()
    {
        if (Auth::check()) {
            $user = Auth::user();
        }
        if (! session()->has('defaultbuildtype')) {
            session(['defaultbuildtype' => 'Popular']);
        }

        return session('defaultbuildtype');
    }

    public function getLatestPatch()
    {
        if (! session()->has('latestPatch')) {
            session(['latestPatch' => SeasonGameVersion::orderBy('id', 'desc')->limit(1)->value('game_version')]);
        }

        return session('latestPatch');
    }

    public function getLatestGameDate()
    {
        if (! session()->has('latestGameDate')) {
            session(['latestGameDate' => Replay::where('game_date', '<=', now())
                ->orderByDesc('game_date')
                ->value('game_date'),
            ]);
        }

        return session('latestGameDate');
    }

    public function getRegionIDtoString()
    {
        if (! session()->has('regions')) {
            $regions = [
                1 => 'NA',
                2 => 'EU',
                3 => 'KR',
                /*  4 => "UNK",*/
                5 => 'CN',
            ];
            session(['regions' => $regions]);
        }

        return session('regions');
    }

    public function getRegionStringToID()
    {
        if (! session()->has('regions_string')) {
            $regions = [
                'NA' => 1,
                'EU' => 2,
                'KR' => 3,
                /*  4 => "UNK",*/
                'CN' => 5,
            ];
            session(['regions_string' => $regions]);
        }

        return session('regions_string');
    }

    public function getGameTypeIDtoString()
    {
        if (! session()->has('game_types')) {
            $game_types = GameType::all();
            $game_types = $game_types->keyBy('type_id');
            session(['game_types' => $game_types]);
        }

        return session('game_types');
    }

    public function getWeeksSinceSeasonStart(){
        $currentSeasonStartDate = SeasonDate::orderBy('id', 'desc')->limit(1)->value('start_date');
        $startDate = Carbon::parse($currentSeasonStartDate);
        $currentDate = Carbon::now();
        return $startDate->diffInWeeks($currentDate);
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
        if (app()->environment('production')) {
            if (count($timeframe) == 1 && $timeframe[0] == session('latestPatch')) {
                $date = SeasonGameVersion::where('game_version', min($timeframe))->value('date_added');
                $changeInMinutes = Carbon::now()->diffInMinutes(new Carbon($date));

                if ($changeInMinutes < 1440) {  //1 day
                    return .25; //15 min
                } elseif ($changeInMinutes < (1440 * 3.5)) { //half week
                    return 6 * 60; //6 hours
                } elseif ($changeInMinutes < (1440 * 7)) { //1 week
                    return 24 * 60; //1 day
                } elseif ($changeInMinutes < (1440 * 2)) { //2 week
                    return 24 * 60 * 7; //7 day
                } else {
                    return 24 * 60 * 7 * 2; //2 weeks
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
        if (! session()->has('game_types')) {
            session(['game_types' => GameType::orderBy('type_id', 'ASC')->get()]);
        }

        return session('game_types');
    }

    public function getHeroes()
    {
        if (! session()->has('heroes')) {
            session(['heroes' => Hero::orderBy('name', 'ASC')->get()]);
        }

        return session('heroes');
    }

    public function getHeroesByID()
    {
        $heroData = $this->getHeroes();
        $heroData = $heroData->keyBy('id');

        return $heroData;
    }

    public function getHeroModel($heroName)
    {
        if (! session()->has('heroes')) {
            session(['heroes' => Hero::all()]);
        }
        $heroModel = session('heroes')->firstWhere('name', $heroName);

        return $heroModel;
    }

    public function getSeasonsData()
    {
        if (! session()->has('seasonData')) {
            session(['seasonData' => SeasonDate::orderBy('id', 'desc')->get()]);
        }

        return session('seasonData');
    }

    public function getSeasonFromDate($date)
    {
        return SeasonDate::select('id')->where('start_date', '<=', $date)->where('end_date', '>=', $date)->first()->id;
    }

    public function getAdvancedFilterShowDefault()
    {
        if (Auth::check()) {
            $user = Auth::user();

            $advancedfiltering = $user->userSettings->firstWhere('setting', 'advancedfiltering')->value;

            return $advancedfiltering === 'true';
        }

        return false;
    }

    public function getGameTypeDefault()
    {

        if (Auth::check()) {
            $user = Auth::user();

            $gameTypeSetting = $user->userSettings->firstWhere('setting', 'game_type');

            if ($gameTypeSetting) {
                $gameTypeValue = $gameTypeSetting->value;

                return explode(',', $gameTypeSetting->value);
            }
        }

        return ['sl'];
    }

    public function getDefaultSeason()
    {
        return SeasonDate::select('id')->orderBy('id', 'DESC')->first()->id;
    }

    public function getFilterData()
    {
        $filterData = new \stdClass;

        $filterData->timeframe_type = [
            ['code' => 'major', 'name' => 'Major Patch'],
            ['code' => 'minor', 'name' => 'Minor Patch'],
        ];

        $filterData->timeframes = SeasonGameVersion::select('game_version')
            ->where('game_version', '>=', $this->filtersMinimumPatch)
            ->orderBy('game_version', 'DESC')
            ->get()
            ->map(function ($item) {
                return ['code' => $item->game_version, 'name' => $item->game_version];
            });

        $filterData->timeframes_grouped = SeasonGameVersion::select('game_version')
            ->where('game_version', '>=', $this->filtersMinimumPatch)
            ->orderBy('game_version', 'DESC')
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
            ['code' => 'Ranged Assassi', 'name' => 'Ranged Assassi'],
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

        $counter = 5;
        $multiply = 1;
        foreach ($rankTiers as $key => $tierInfo) {
            $minMmr = $tierInfo['min_mmr'];
            $maxMmr = $tierInfo['max_mmr'];
            $split = $tierInfo['split'];

            if ($mmr >= $minMmr && $mmr < $maxMmr) {
                for ($i = $minMmr; $i < $maxMmr; $i += $split) {
                    if ($mmr >= $i) {
                        $result = $tierNames[$key].' '.$counter;
                        $counter--;
                    }
                }
            } else {
                if ($mmr >= $minMmr && $maxMmr == '') {
                    $result = 'Master';
                }
            }
        }

        return $result;
    }
    public function calculateTierID($rankName){
        if($rankName == "Master"){
            return 6;
        }

        $split = explode(' ', $rankName);

        if($split[0] == "Diamond"){
            return 6 - ($split[1] / 10);
        }else if($split[0] == "Platinum"){
            return 5 - ($split[1] / 10);
        }else if($split[0] == "Gold"){
            return 4 - ($split[1] / 10);
        }else if($split[0] == "Silver"){
            return 3 - ($split[1] / 10);
        }else if($split[0] == "Bronze"){
            return 2 - ($split[1] / 10);
        }else if($split[0] == "Wood"){
            return 1 - ($split[1] / 10);
        }
    }
    public function getSubTiers($rankTiers, $mmr)
    {
        $tierNames = [
            'bronze' => 'Bronze',
            'silver' => 'Silver',
            'gold' => 'Gold',
            'platinum' => 'Platinum',
            'diamond' => 'Diamond',
            'master' => 'Master',
        ];

        $result = [];

        $counter = 5;
        $multiply = 1;
        foreach ($rankTiers as $key => $tierInfo) {
            $minMmr = $tierInfo['min_mmr'];
            $maxMmr = $tierInfo['max_mmr'];
            $split = $tierInfo['split'];

            if ($mmr >= $minMmr && $mmr < $maxMmr) {
                for ($i = $minMmr; $i < $maxMmr; $i += $split) {
                    $result[$tierNames[$key].' '.$counter] = $i;
                    $counter--;
                }
            } else {
                if ($mmr >= $minMmr && $maxMmr == '') {
                    $result['Master'] = $minMmr;
                }
            }
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
}
