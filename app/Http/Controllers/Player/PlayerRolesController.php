<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Battletag;
use App\Rules\RoleInputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerRolesController extends Controller
{
    public function showAll(Request $request, $battletag, $blizz_id, $region)
    {
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }

        $account_level = $result = Battletag::where('blizz_id', $blizz_id)
            ->where('region', $region)
            ->select('account_level')
            ->orderByDesc('account_level')
            ->first()->account_level;

        return view('Player.Roles.allRoleData')->with([
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'account_level' => $account_level,
            'region' => $region,
            'filters' => $this->globalDataService->getFilterData(),
        ]);
    }

    public function showSingle(Request $request, $battletag, $blizz_id, $region, $role)
    {
        $validationRules = [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer',
            'role' => ['required', new RoleInputValidation()],
        ];

        $validator = Validator::make(compact('battletag', 'blizz_id', 'region', 'role'), $validationRules);

        if ($validator->fails()) {
            return [
                'data' => compact('battletag', 'blizz_id', 'region', 'role'),
                'status' => 'failure to validate inputs',
            ];
        }

        return view('Player.Roles.singleRoleData')->with([
            'battletag' => $battletag,
            'blizz_id' => $blizz_id,
            'region' => $region,
            'role' => $role,
            'filters' => $this->globalDataService->getFilterData(),
            'regions' => $this->globalDataService->getRegionIDtoString(),
        ]);
    }
}
