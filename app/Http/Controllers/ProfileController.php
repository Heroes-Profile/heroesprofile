<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
  public function show(Request $request)
  {
    return view('profile',
    [
      'title'  => $request['battletag'] . "'s Profile",
      'paragraph' => $request['battletag'] . "'s Profile data.  Summary of heroes played, maps, MMR and more.",
      'blizz_id' => $request['blizz_id'],
      'battletag' => $request['battletag'],
      'region' => $request['region']
    ]);
  }
}
