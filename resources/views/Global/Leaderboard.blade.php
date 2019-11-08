<?php
//var_dump(openssl_get_cert_locations());
//die;
//$data = \PlayerData::instance()->getPlayerData();
//print_r(json_encode($data,true));
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

//Auth::loginUsingId(1, true);

/*
if (Auth::check()) {
    $user = Auth::user();
    print_r($user);
}
*/
$data = \PlayerData::instance()->getPlayerData();
$data = json_encode($data,true);
print_r(json_encode($data));
 ?>
