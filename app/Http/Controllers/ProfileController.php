<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\BattlenetAccount;
use App\Models\Hero;
use App\Models\GameType;

class ProfileController extends Controller
{
    public function showSettings(Request $request)
    {
        $user = Auth::user();
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
          if (Hero::where('name', $request["userhero"])->exists()) {
                $userhero = $request["userhero"];
            } else {
                return ["success" => false];
            }

            $user = BattlenetAccount::find($request["userid"]);

            $user->userSettings()->updateOrCreate(
                ['setting' => "hero"],
                ['value' => $userhero]
            );
        }

        if(!is_null($request["usergametype"])){
            $user = BattlenetAccount::find($request["userid"]);

            $userGameTypes = $request["usergametype"];
            $existingGameTypes = GameType::whereIn('short_name', $userGameTypes)->pluck('short_name')->all();
            if (count($existingGameTypes) === count($userGameTypes)) {
                $usergametype = $request["usergametype"];
            } else {
                return ["success" => false];
            }


            $user->userSettings()->updateOrCreate(
                ['setting' => "game_type"],
                ['value' =>  implode(',', $usergametype)]
            );
        }

        return ["success" => true];
    }
}
