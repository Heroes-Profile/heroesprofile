<?php

namespace App\Http\Controllers\Player;
use App\Services\GlobalDataService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
                'gametypedefault' => [$this->globalDataService->getGameTypeDefault()],
                'filters' => $this->globalDataService->getFilterData()
                ]);

    }

    public function getHeroAllData(Request $request){
        return response()->json($request->all());

        $validator = \Validator::make($request->only(['blizz_id', 'region']), [
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ]);

        $blizz_id = $request['blizz_id'];
        $region = $request['region'];


    }
}
