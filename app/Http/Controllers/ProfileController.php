<?php

namespace App\Http\Controllers;

use App\Models\GameType;
use App\Models\Hero;
use App\Models\PatreonAccount;
use App\Rules\GameTypeInputValidation;
use App\Rules\TalentBuildTypeInputValidation;
use App\Services\GlobalDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function showSettings(Request $request)
    {
        $user = Auth::user();

        return view('Profile.profileSettings')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'user' => $user,
            'filters' => $this->globalDataService->getFilterData(),
            'availableFlair' => $this->globalDataService->getAvailableFlair($user),
        ]);
    }

    public function saveSettings(Request $request)
    {
        // Always the logged-in account; never an id from the request.
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        $validationRules = [
            'userhero' => 'nullable|numeric',
            'usergametype' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'playermultigametype' => 'sometimes|nullable|array',
            'talentbuildtype' => ['sometimes', 'nullable', new TalentBuildTypeInputValidation],
            'talentbuilderstyle' => 'nullable|in:vertical,horizontal',
            'darkmode' => 'nullable|boolean',
            'playerhistorytable' => 'nullable|boolean',
            'customgames' => 'nullable|boolean',
            'flair_hide_owner' => 'nullable|boolean',
            'flair_hide_patreon' => 'nullable|boolean',
            'flair_hide_void_eye' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
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

            $user->userSettings()->updateOrCreate(
                ['setting' => 'hero'],
                ['value' => $userhero]
            );
        }

        if (! is_null($request['usergametype'])) {
            $usergametype = $request['usergametype'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'game_type'],
                ['value' => $usergametype]
            );
        }

        if ($request->has('playermultigametype')) {
            $playerGameTypes = (array) $request['playermultigametype'];

            // Nothing ticked means every game type
            if (empty($playerGameTypes)) {
                $user->userSettings()->where('setting', 'player_multi_game_type')->delete();
            } else {
                $validPlayerGameTypes = GameType::whereIn('short_name', ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'])
                    ->whereIn('short_name', $playerGameTypes)
                    ->pluck('short_name')
                    ->all();

                if (count($validPlayerGameTypes) !== count($playerGameTypes)) {
                    return ['success' => false];
                }

                $user->userSettings()->updateOrCreate(
                    ['setting' => 'player_multi_game_type'],
                    ['value' => implode(',', $playerGameTypes)]
                );
            }
        }

        if (! is_null($request['usermultigametype'])) {
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
            $advancedfiltering = $request['advancedfiltering'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'advancedfiltering'],
                ['value' => $advancedfiltering]
            );
        }

        if (! is_null($request['customgames'])) {
            $customgames = $request['customgames'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'customgames'],
                ['value' => $customgames]
            );
        }

        if (! is_null($request['talentbuildtype'])) {
            $talentbuildtype = $request['talentbuildtype'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'talentbuildtype'],
                ['value' => $talentbuildtype]
            );
        }

        if (! is_null($request['talentbuilderstyle'])) {
            $user->userSettings()->updateOrCreate(
                ['setting' => 'talentbuilderstyle'],
                ['value' => $request['talentbuilderstyle']]
            );
        }

        if (! is_null($request['darkmode'])) {
            $darkmode = $request['darkmode'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'darkmode'],
                ['value' => $darkmode]
            );
        }

        if (! is_null($request['playerhistorytable'])) {
            $playerhistorytable = $request['playerhistorytable'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'playerhistorytable'],
                ['value' => $playerhistorytable]
            );
        }

        if (! is_null($request['playerload'])) {
            $playerload = $request['playerload'];

            $user->userSettings()->updateOrCreate(
                ['setting' => 'playerload'],
                ['value' => $playerload]
            );
        }

        $flairChanged = false;
        foreach (GlobalDataService::FLAIR_HIDE_SETTINGS as $setting) {
            if (! is_null($request[$setting])) {
                $user->userSettings()->updateOrCreate(
                    ['setting' => $setting],
                    ['value' => $request[$setting] ? 1 : 0]
                );
                $flairChanged = true;
            }
        }

        if ($flairChanged) {
            Cache::forget('global_hidden_flair');
        }

        return ['success' => true];
    }

    public function removePatreon(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        try {
            $account = PatreonAccount::where('battlenet_accounts_id', $user->battlenet_accounts_id)->first();
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
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => 'unauthenticated'], 401);
        }

        $validationRules = [
            'accountVisibility' => 'required|in:true,false',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => $request->all(),
                'errors' => $validator->errors()->all(),
                'status' => 'failure to validate inputs',
            ];
        }

        try {
            $accountVisibility = $request['accountVisibility'];
            $value = $accountVisibility == 'true' ? 1 : 0;

            // Only stamp a real change. Saving the same value again would put the
            // account back through the API privacy feed for no reason, and a null
            // `private` is already public, so null -> 0 is not a change either.
            if ((int) $user->private !== $value) {
                $user->private = $value;
                $user->private_changed_at = now();
                $user->save();
            }

        } catch (\Exception $e) {
            return ['status' => 'failure'];
        }

        return ['status' => 'success'];
    }
}
