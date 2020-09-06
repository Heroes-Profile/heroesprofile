<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingPageController extends Controller
{
  public function show(){
    $data = array(
      'maxReplayID' => getMaxReplayID(),
      'maxGameVersion' => getMaxGameVersion(),
      'getMaxGameDate' => getMaxGameDate(),
    );
    return view('index')->with($data);
  }
}
