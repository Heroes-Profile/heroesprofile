<?php

namespace App\Http\Controllers\Esports\NGS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\NGS\Team;

class NGSSingleDivisionController extends Controller
{
    public function show(Request $request){
        $defaultseason = Team::max('season');

        return view('Esports.NGS.ngsMain')  
            ->with([
                'defaultseason' => $defaultseason,
                'filters' => $this->globalDataService->getFilterData(),
                'talentimages' => $this->globalDataService->getPreloadTalentImageUrls(),
            ]);

   
    }
}
