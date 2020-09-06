<?php

namespace App\Http\Controllers\Auth\battlenet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class LogoutController extends Controller
{
  public function show(){
    return redirect('/')->with(Auth::logout());
  }
}
