<?php

namespace App\Http\Controllers\Player;
use App\Services\GlobalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Rules\GameTypeInputValidation;

use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataUD;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataAR;
use App\Models\Hero;

class PlayerHeroesController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function showAll(Request $request, $battletag, $blizz_id, $region)
    {
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.Heroes.allHeroesData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData()
                ]);

    }

    public function getHeroAllData(Request $request){
        //return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ]);

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);


/*
        $resultQM = MasterMMRDataQM::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 1)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();

        $resultUD = MasterMMRDataUD::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 2)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();

        $resultHL = MasterMMRDataHL::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 3)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();

        $resultTL = MasterMMRDataTL::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 4)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();  

        $resultSL = MasterMMRDataSL::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 5)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();

        $resultAR = MasterMMRDataAR::select('type_value')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 6)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get();

            */

        $query = function($table, $gameType) use ($blizz_id, $region) {
            return DB::table($table)
                ->select('type_value', 'game_type')
                ->selectRaw('1800 + 40 * conservative_rating as mmr')
                ->addSelect(DB::raw("$gameType as game_type"))
                ->whereIn('type_value', function($query) {
                    $query->select('id')
                          ->from(with(new Hero)->getTable());
                })
                ->where('game_type', $gameType)
                ->where('blizz_id', $blizz_id)
                ->where('region', $region)
                ->get();
        };

        $resultQM = $query('master_mmr_data_qm', 1);
        $resultUD = $query('master_mmr_data_ud', 2);
        $resultHL = $query('master_mmr_data_hl', 3);
        $resultTL = $query('master_mmr_data_tl', 4);
        $resultSL = $query('master_mmr_data_sl', 5);
        $resultAR = $query('master_mmr_data_ar', 6);

        $combinedResults = $resultQM->merge($resultUD)
                                    ->merge($resultHL)
                                    ->merge($resultTL)
                                    ->merge($resultSL)
                                    ->merge($resultAR);

        return $combinedResults;
    }
}
