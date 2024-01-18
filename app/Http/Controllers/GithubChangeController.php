<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GithubChangeController extends Controller
{
    public function show(Request $request)
    {
        return view('githubChanges')->with([
            'regions' => $this->globalDataService->getRegionIDtoString(),
            'access_token' => ENV('GITHUB_PERSONAL_ACCESS_TOKEN'),
        ]);
    }
}
