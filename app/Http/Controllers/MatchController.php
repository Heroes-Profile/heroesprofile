<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MatchController extends Controller
{
  public function show(Request $request){
    $match_replay = new \MatchReplay($request["replayID"]);
    return view('match')->with(["replay" => $match_replay->getTeams()]); // @phpstan-ignore-line
  }
}
