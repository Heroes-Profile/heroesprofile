<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GlobalDataService;


use Illuminate\Support\Facades\Cache;


class MainPageController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request)
    {
        $maxReplayID = $this->globalDataService->calculateMaxReplayNumber();
        $latestPatch = $this->globalDataService->getLatestPatch();
        $latestGameDate = $this->globalDataService->getLatestGameDate();

        return view('mainPage', compact('maxReplayID', 'latestPatch', 'latestGameDate'));
    }
}
