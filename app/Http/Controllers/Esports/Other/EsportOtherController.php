<?php

namespace App\Http\Controllers\Esports\Other;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Other\Series;

class EsportOtherController extends Controller
{
    public function show(Request $request)
    {
        return view('Esports.Other.OtherMain')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $this->globalDataService->getHeroes(),
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
                'series' => Series::whereNot('name', 'Nut Cup')->whereNot('name', 'Heroes Lounge')->get(),
            ]);
    }
}
