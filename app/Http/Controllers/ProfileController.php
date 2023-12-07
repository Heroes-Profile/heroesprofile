<?php

namespace App\Http\Controllers;

use App\Models\BattlenetAccount;
use App\Models\GameType;
use App\Models\Hero;
use App\Models\PatreonAccount;
use App\Rules\GameTypeInputValidation;
use App\Rules\TalentBuildTypeInputValidation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function showSettings(Request $request)
    {
        $user = Auth::user();

        return view('Profile.profileSettings')->with([
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'user' => $user,
            'filters' => $this->globalDataService->getFilterData(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        //return response()->json($request->all());

        $validationRules = [
            'userhero' => 'nullable|numeric',
            'usergametype' => ['sometimes', 'nullable', new GameTypeInputValidation()],
            'talentbuildtype' => ['sometimes', 'nullable', new TalentBuildTypeInputValidation()],
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

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

            $usergametype = $request['usergametype'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'game_type'],
                ['value' => $usergametype]
            );
        }


        if (! is_null($request['usermultigametype'])) {
            $user = BattlenetAccount::find($request['userid']);

            $userGameTypes = $request['usermultigametype'];
            $existingGameTypes = GameType::whereIn('short_name', $userGameTypes)->pluck('short_name')->all();
            if (count($existingGameTypes) === count($userGameTypes)) {
                $usergametype = $request['usermultigametype'];
            } else {
                return ['success' => false];
            }

            $user->userSettings()->updateOrCreate(
                ['setting' => 'multi_game_type'],
                ['value' => implode(',', $userGameTypes)]
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

        if (! is_null($request['talentbuildtype'])) {
            $user = BattlenetAccount::find($request['userid']);

            $talentbuildtype = $request['talentbuildtype'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'talentbuildtype'],
                ['value' => $talentbuildtype]
            );
        }
        return ['success' => true];
    }

    public function removePatreon(Request $request)
    {
        $validationRules = [
            'userid' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        try {
            $userIdToDelete = $request['userid'];
            $account = PatreonAccount::where('battlenet_accounts_id', $userIdToDelete)->first();
            if ($account) {
                $account->delete();
            }

        } catch (\Exception $e) {
            return ['status' => 'failure'];
        }

        return ['status' => 'success'];
    }

    public function setAccountVisibility(Request $request)
    {
        $validationRules = [
            'userid' => 'required|numeric',
            'accountVisibility' => 'required|in:true,false',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        try {
            $accountVisibility = $request['accountVisibility'];
            $value = $accountVisibility == 'true' ? 1 : 0;

            $user = BattlenetAccount::find($request['userid']);
            $user->private = $value;
            $user->save();

        } catch (\Exception $e) {
            return ['status' => 'failure'];
        }

        return ['status' => 'success'];
    }
}
