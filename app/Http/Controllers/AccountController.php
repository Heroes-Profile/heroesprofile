<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class AccountController extends Controller
{
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
  * Show the application dashboard.
  *
  * @return \Illuminate\Contracts\Support\Renderable
  */
  public function show()
  {
    return view('Account.index');
  }

  public function optout(){
    $battletag_data = \App\Models\Battletag::where('Battletag', Auth::user()->battletag)->get();

    foreach($battletag_data as $result){
      $result->opt_out = 1;
      $result->save();
    }
  }
}
