<?php

namespace App\Http\Controllers\Esports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EsportsController extends Controller
{
    public function show(Request $request){
        return view('Esports.esportsMain')  
            ->with([
                //'filters' => $this->globalDataService->getFilterData(),
                //'gametypedefault' => $this->globalDataService->getGameTypeDefault(),
                //'defaulttimeframetype' => $this->globalDataService->getDefaultTimeframeType(),
                //'defaulttimeframe' => [$this->globalDataService->getDefaultTimeframe()],
                //'defaultbuildtype' => $this->globalDataService->getDefaultBuildType()
            ]);

   
    }
}
