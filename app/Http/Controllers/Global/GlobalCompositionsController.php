<?php

namespace App\Http\Controllers\Global;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Cache;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GlobalCompositionsController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request){
        return view('Global.Compositions.compositionsStats');
    }

    public function getCompositionsData(Request $request){
        
    }
}
