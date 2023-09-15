<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class MainPageController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request)
    {
        return view('mainPage');
    }
}
