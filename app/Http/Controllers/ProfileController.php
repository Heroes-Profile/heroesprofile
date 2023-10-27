<?php

namespace App\Http\Controllers;

use App\Models\BattlenetAccount;
use App\Models\GameType;
use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PatreonAccount;

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

    public function saveSettings(Request $request)
    {
        //return response()->json($request->all());

        $userhero = null;
        $usergametype = null;

        if (! is_null($request['userhero'])) {
            if (Hero::where('name', $request['userhero'])->exists()) {
                $userhero = $request['userhero'];
            } else {
                return ['success' => false];
            }

            $user = BattlenetAccount::find($request['userid']);

            $user->userSettings()->updateOrCreate(
                ['setting' => 'hero'],
                ['value' => $userhero]
            );
        }

        if (! is_null($request['usergametype'])) {
            $user = BattlenetAccount::find($request['userid']);

            $userGameTypes = $request['usergametype'];
            $existingGameTypes = GameType::whereIn('short_name', $userGameTypes)->pluck('short_name')->all();
            if (count($existingGameTypes) === count($userGameTypes)) {
                $usergametype = $request['usergametype'];
            } else {
                return ['success' => false];
            }

            $user->userSettings()->updateOrCreate(
                ['setting' => 'game_type'],
                ['value' => implode(',', $usergametype)]
            );
        }

        if (! is_null($request['advancedfiltering'])) {
            $user = BattlenetAccount::find($request['userid']);

            $advancedfiltering = $request['advancedfiltering'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'advancedfiltering'],
                ['value' => $advancedfiltering]
            );
        }

        return ['success' => true];
    }

    public function removePatreon (Request $request){
        try{
            $userIdToDelete = $request["userid"];
            $account = PatreonAccount::where('battlenet_accounts_id', $userIdToDelete)->first();
            if ($account) {
                $account->delete();
            }

        } catch (\Exception $e) {   
            return ["status" => "failure"];
        }    
        return ["status" => "success"];
    }
}
