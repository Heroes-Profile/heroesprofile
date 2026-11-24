<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedesignController extends Controller
{
    // The 2026 redesign's page; a placeholder until its write-up is ready
    public function show(Request $request)
    {
        return view('redesign')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
