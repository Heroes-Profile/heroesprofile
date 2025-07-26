<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    public function showSupport(Request $request)
    {
        return view('communitySupport')->with([
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
        $exception = 1 / 0;
    }

    public function testJS()
    {
        return view('jsException')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
        ]);
    }
}
