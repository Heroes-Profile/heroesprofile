<?php
namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//Auth::loginUsingId(1, true);

/*
if (Auth::check()) {
    $user = Auth::user();
    print_r($user);
}
*/

$player_instance = \ProfileData::instance("Zemill#1940", 67280, 1, "", "");
$heroData = $player_instance->grabAllHeroData();
print_r(json_encode($heroData, true));
