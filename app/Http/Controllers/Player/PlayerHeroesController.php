<?php

namespace App\Http\Controllers\Player;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Rules\GameTypeInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\HeroInputValidation;
use App\Rules\RoleInputValidation;

use App\Models\Replay;
use App\Models\Map;
use App\Models\HeroesDataTalent;

use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataUD;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataAR;



class PlayerHeroesController extends Controller
{
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
                'filters' => $this->globalDataService->getFilterData(),
                ]);

    }
    public function showSingle(Request $request, $battletag, $blizz_id, $region, $hero){
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }
        $hero = (new HeroInputValidation())->passes('hero', $hero);


        return view('Player.Heroes.singleHeroData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'hero' => $hero,
                'filters' => $this->globalDataService->getFilterData(),
                'regions' => $this->globalDataService->getRegionIDtoString(),
                ]);
    }
}
