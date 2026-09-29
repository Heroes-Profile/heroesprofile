<?php

namespace App\Http\Controllers;

use App\Models\Award;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    public function show(Request $request)
    {
        $versions = DB::table('season_game_versions')
            ->where('valid_globals', 1)
            ->orderBy('date_added')
            ->orderBy('id')
            ->get(['id', 'season', 'game_version', 'date_added', 'patch_notes_url'])
            ->values();

        $patchHistory = [];
        foreach ($versions as $i => $v) {
            $next = $versions[$i + 1] ?? null;
            $patchHistory[] = [
                'season' => $v->season,
                'game_version' => $v->game_version,
                'patch_notes_url' => $v->patch_notes_url ?: null,
                'start_date' => substr($v->date_added, 0, 10),
                'end_date' => $next ? substr($next->date_added, 0, 10) : null,
            ];
        }

        $patchHistory = array_reverse($patchHistory);

        // The current season has no real end date yet, so it's left off.
        $seasonDates = $this->globalDataService->getSeasonsData()
            ->values()
            ->map(fn ($season, $i) => [
                'id' => $season->id,
                'year' => $season->year,
                'season' => rtrim(rtrim(number_format((float) $season->season, 2, '.', ''), '0'), '.'),
                // UTC; the page converts to the reader's time zone.
                'start_date' => (string) $season->start_date,
                'end_date' => $i === 0 ? null : (string) $season->end_date,
            ]);

        $awards = Award::orderBy('award_id')
            ->orderBy('title')
            ->get(['award_id', 'title', 'icon', 'description']);

        return view('faq')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
            'patchHistory' => $patchHistory,
            'awards' => $awards,
            'seasonDates' => $seasonDates,
        ]);
    }
}
