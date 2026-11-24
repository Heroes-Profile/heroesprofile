<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function show(Request $request)
    {
        return view('team')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'members' => config('team.members'),
            'contributors' => config('team.contributors', []),
        ]);
    }
}
