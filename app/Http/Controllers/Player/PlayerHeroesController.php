<?php

namespace App\Http\Controllers\Player;
use App\Services\GlobalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

use App\Rules\GameTypeInputValidation;
use App\Rules\GameMapInputValidation;
use App\Rules\HeroInputByIDValidation;
use App\Rules\RoleInputValidation;

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
    public function showSingle(Request $request, $battletag, $blizz_id, $region){
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }


        return view('Player.Heroes.singleHeroData')->with([
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
            'minimumgames' => 'integer',
        ]);

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];
        $gameType = (new GameTypeInputValidation())->passes('game_type', $request["game_type"]);
        $hero = (new HeroInputByIDValidation())->passes('statfilter', $request["hero"]);
        $role = (new RoleInputValidation())->passes('role', $request["role"]);
        $minimum_games = $request["minimumgames"];

        $resultQM = in_array(1, $gameType) ? MasterMMRDataQM::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 1)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();
            

        $resultUD = in_array(2, $gameType) ? MasterMMRDataUD::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 2)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();

        $resultHL = in_array(3, $gameType) ? MasterMMRDataHL::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 3)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();

        $resultTL = in_array(4, $gameType) ? MasterMMRDataTL::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 4)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();  

        $resultSL = in_array(5, $gameType) ? MasterMMRDataSL::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 5)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();

        $resultAR = in_array(6, $gameType) ? MasterMMRDataAR::select('type_value', 'win', 'loss')
            ->selectRaw('1800 + 40 * conservative_rating as mmr')
            ->whereIn('type_value', function($query) {
                $query->select('id')
                      ->from(with(new Hero)->getTable());
            })
            ->where('game_type', 6)
            ->where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->get() : new Collection();


        $heroes = $this->globalDataService->getHeroes();

       
        if($role)
        {
            $heroes = $heroes->where('new_role', $role);
        }


        if($hero)
        {
            $heroes = $heroes->where('id', $hero);
        }

        $heroes->each(function($hero) use ($resultQM, $resultUD, $resultHL, $resultTL, $resultSL, $resultAR) {
            $hero->wins = 0;
            $hero->losses = 0;

            $hero->wins += $resultQM->where('type_value', $hero->id)->first() ? $resultQM->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultQM->where('type_value', $hero->id)->first() ? $resultQM->where('type_value', $hero->id)->first()->loss : 0;

            $hero->wins += $resultUD->where('type_value', $hero->id)->first() ? $resultUD->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultUD->where('type_value', $hero->id)->first() ? $resultUD->where('type_value', $hero->id)->first()->loss : 0;

            $hero->wins += $resultHL->where('type_value', $hero->id)->first() ? $resultHL->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultHL->where('type_value', $hero->id)->first() ? $resultHL->where('type_value', $hero->id)->first()->loss : 0;

            $hero->wins += $resultTL->where('type_value', $hero->id)->first() ? $resultTL->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultTL->where('type_value', $hero->id)->first() ? $resultTL->where('type_value', $hero->id)->first()->loss : 0;

            $hero->wins += $resultSL->where('type_value', $hero->id)->first() ? $resultSL->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultSL->where('type_value', $hero->id)->first() ? $resultSL->where('type_value', $hero->id)->first()->loss : 0;

            $hero->wins += $resultAR->where('type_value', $hero->id)->first() ? $resultAR->where('type_value', $hero->id)->first()->win : 0;
            $hero->losses += $resultAR->where('type_value', $hero->id)->first() ? $resultAR->where('type_value', $hero->id)->first()->loss : 0;

            $hero->games_played = $hero->wins + $hero->losses;
            $hero->win_rate = $hero->games_played > 0 ? round(($hero->wins / $hero->games_played) * 100, 2): 0;

            $hero->qm_mmr = $resultQM->where('type_value', $hero->id)->first() ? round($resultQM->where('type_value', $hero->id)->first()->mmr) : "";
            $hero->ud_mmr = $resultUD->where('type_value', $hero->id)->first() ? round($resultUD->where('type_value', $hero->id)->first()->mmr) : "";
            $hero->hl_mmr = $resultHL->where('type_value', $hero->id)->first() ? round($resultHL->where('type_value', $hero->id)->first()->mmr) : "";
            $hero->tl_mmr = $resultTL->where('type_value', $hero->id)->first() ? round($resultTL->where('type_value', $hero->id)->first()->mmr) : "";
            $hero->sl_mmr = $resultSL->where('type_value', $hero->id)->first() ? round($resultSL->where('type_value', $hero->id)->first()->mmr) : "";
            $hero->ar_mmr = $resultAR->where('type_value', $hero->id)->first() ? round($resultAR->where('type_value', $hero->id)->first()->mmr) : "";
        });


 
        if($minimum_games > 0){
            $heroes = $heroes->filter(function($hero) use ($minimum_games){
                return $hero->games_played > $minimum_games;
            });
        }

        return $heroes;
    }
}
