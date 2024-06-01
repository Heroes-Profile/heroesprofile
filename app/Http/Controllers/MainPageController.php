<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Award;

class MainPageController extends Controller
{
    public function show(Request $request)
    {
        return view('mainPage')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'maxReplayID' => $this->globalDataService->calculateMaxReplayNumber(),
            'latestPatch' => $this->globalDataService->getLatestPatch(),
            'latestGameDate' => $this->globalDataService->getLatestGameDate(),
        ]);
    }

    public function getFooterData()
    {
        return [
            'maxReplayID' => $this->globalDataService->calculateMaxReplayNumber(),
            'latestPatch' => $this->globalDataService->getLatestPatch(),
            'latestGameDate' => $this->globalDataService->getLatestGameDate(),
        ];
    }

    public function getHeaderAlertData()
    {
        return $this->globalDataService->getHeaderAlert();
    }

    public function test()
    {
        return Award::all();
    }

    public function testJS()
    {
        return view('jsException')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
