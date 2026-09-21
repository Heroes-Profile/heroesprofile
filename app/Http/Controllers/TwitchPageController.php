<?php

namespace App\Http\Controllers;

use App\Services\Twitch\TwitchDirectoryService;

/**
 * Public pages for the Twitch extension: who is using it, and the code of conduct
 * listed streamers agree to.
 */
class TwitchPageController extends Controller
{
    public function index(TwitchDirectoryService $directory)
    {
        return view('twitch.index')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'directory' => $directory->list(),
        ]);
    }

    public function guidelines()
    {
        return view('twitch.guidelines')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'termsVersion' => (int) config('twitch.listing_terms_version'),
        ]);
    }
}
