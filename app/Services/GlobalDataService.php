<?php

namespace App\Services;

use App\Models\Replay;
use App\Models\Hero;
use App\Models\SeasonGameVersion;

use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataUD;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataAR;


class GlobalDataService
{
    public function calculateMaxReplayNumber()
    {
        if (!session()->has('maxReplayID')) {
            session(['maxReplayID' => Replay::max('replayID')]);
        }

        return session('maxReplayID');
    }

    public function getLatestPatch()
    {
        if (!session()->has('latestPatch')) {
            session(['latestPatch' => SeasonGameVersion::orderBy('id', 'desc')->limit(1)->value('game_version')]);
        }

        return session('latestPatch');
    }

    public function getLatestGameDate(){
        if (!session()->has('latestGameDate')) {
            session(['latestGameDate' => Replay::where('game_date', '<=', now())
                                            ->orderByDesc('game_date')
                                            ->value('game_date')
                    ]);
        }

        return session('latestGameDate');
    }

    public function calculateCacheTimeInMinutes($timeframe){
        //Cache time is set to 0.  Need to setup how cache time is done
        return 0;
    }

    public function getHeroes(){
        if (!session()->has('heroes')) {
            session(['heroes' => Hero::all()]);
        }
        return session('heroes');
    }

    public function getHeroModel($heroName){
        if (!session()->has('heroes')) {
            session(['heroes' => Hero::all()]);
        }
        $heroModel = session('heroes')->firstWhere('name', $heroName);
        return $heroModel;
    }

    public function getMasterMMRData($blizz_id, $region, $type, $game_type){
        $model = "";
        if($game_type == 1){
            $model = MasterMMRDataQM::class;
        }else if($game_type == 2){
            $model = MasterMMRDataUD::class;
        }else if($game_type == 3){
            $model = MasterMMRDataHL::class;
        }else if($game_type == 4){
            $model = MasterMMRDataTL::class;
        }else if($game_type == 5){
            $model = MasterMMRDataSL::class;
        }else if($game_type == 6){
            $model = MasterMMRDataAR::class;
        }

        $data = $model::select('conservative_rating', 'win', 'loss')
                    ->filterByType($type)
                    ->filterByGametype($game_type)
                    ->filterByBlizzID($blizz_id)
                    ->filterByRegion($region)
                    ->get();
        return $data;
    }
}