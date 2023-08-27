<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;

use App\Models\PatreonAccount;
use App\Models\BattlenetAccount;

class ProfileController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function show(Request $request)
    {
        $user = Auth::user()->load('patreonAccount');
        return view('profile', ['user' => $user]);
    }
}
