<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
  public function show(Request $request)
  {
      return view('privacyPolicy')->with([
          'regions' => $this->globalDataService->getRegionIDtoString(),
      ]);
  }
}
