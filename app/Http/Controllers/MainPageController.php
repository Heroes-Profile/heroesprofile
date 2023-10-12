<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainPageController extends Controller
{
    public function show(Request $request)
    {
        return view('mainPage');
    }
}
