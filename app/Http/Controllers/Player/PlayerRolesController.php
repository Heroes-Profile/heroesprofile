<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Rules\RoleInputValidation;


class PlayerRolesController extends Controller
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


        return view('Player.Roles.allRoleData')->with([
                'battletag' => $battletag,
                'blizz_id' => $blizz_id,
                'region' => $region,
                'filters' => $this->globalDataService->getFilterData(),
                ]);
    }

    public function showSingle(Request $request, $battletag, $blizz_id, $region, $role){
        $validator = \Validator::make(compact('battletag', 'blizz_id', 'region'), [
            'battletag' => 'required|string',
            'blizz_id' => 'required|integer',
            'region' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect('/');
        }
        $role = (new RoleInputValidation())->passes('role', $request["role"]);


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
