<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

use App\Rules\HeroInputByIDValidation;
use App\Rules\GameTypeInputValidation;

class ProfileController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function showSettings(Request $request)
    {
        $user = Auth::user()->load('patreonAccount');
        return view('Profile.profileSettings')->with([
            'user' => $user,
            'filters' => $this->globalDataService->getFilterData(),
        ]);
    }

    public function saveSettings(Request $request){
        //return response()->json($request->all());

        $userhero = null;
        $usergametype = null;

        if(!is_null($request["userhero"])){
            $userhero = (new HeroInputByIDValidation())->passes('userhero', $request["userhero"]);
        }

        if(!is_null($request["usergametype"])){
            $usergametype = (new GameTypeInputValidation())->passes('usergametype', $request["usergametype"]);
        }

        return $userhero;
    }
}
