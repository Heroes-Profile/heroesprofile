<?php

namespace App\Services;

use App\Models\Replay;
use App\Models\SeasonGameVersion;

class GlobalDataService
{
    public function calculateMaxReplayNumber()
    {
        if (!session()->has('maxReplayID')) {
            session(['maxReplayID' => Replay::max('replayID')]);
        }

        return session('maxReplayID');
    }

    public function getLatestPatch()
    {
        if (!session()->has('latestPatch')) {
            session(['latestPatch' => SeasonGameVersion::orderBy('id', 'desc')->limit(1)->value('game_version')]);
        }

        return session('latestPatch');
    }

    public function getLatestGameDate(){
        if (!session()->has('latestGameDate')) {
            session(['latestGameDate' => Replay::where('game_date', '<=', now())
                                            ->orderByDesc('game_date')
                                            ->value('game_date')
                    ]);
        }

        return session('latestGameDate');
    }
}