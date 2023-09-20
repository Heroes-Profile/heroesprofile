<?php

namespace App\Http\Controllers;
use App\Services\GlobalDataService;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request)
    {
        return view('compare')
        ->with([
            'filters' => $this->globalDataService->getFilterData(),
            'gametypedefault' => $this->globalDataService->getGameTypeDefault()
        ]);
    }
}
