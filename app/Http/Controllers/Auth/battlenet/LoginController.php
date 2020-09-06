<?php

namespace App\Http\Controllers\Auth\battlenet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
  public function show(){
    return view('auth.battlenet.login');
  }
}
