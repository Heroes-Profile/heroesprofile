<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\Auth;


class ProfileController extends Controller
{
    protected $globalDataService;

    public function __construct(GlobalDataService $globalDataService)
    {
        $this->globalDataService = $globalDataService;
    }

    public function showSettings(Request $request)
    {
        $user = Auth::user()->load('patreonAccount');
        return view('Profile.profileSettings', ['user' => $user]);
    }
}
