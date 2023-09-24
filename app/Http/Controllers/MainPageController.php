<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class MainPageController extends Controller
{
    public function show(Request $request)
    {
        return view('mainPage');
    }
}
