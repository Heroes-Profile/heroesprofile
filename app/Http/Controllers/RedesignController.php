<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedesignController extends Controller
{
    // A few words the page shows in every site language
    private const SAMPLE_STRINGS = ['Match History', 'Win Rate', 'Talent Builder'];

    // The 2026 redesign: what changed, with before and after examples
    public function show(Request $request)
    {
        return view('redesign')->with([
            'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
            'languages' => $this->languages(),
        ]);
    }

    private function languages(): array
    {
        $languages = [];
        foreach (config('app.supported_locales') as $code => $name) {
            $lines = $code === 'en' ? [] : (json_decode(@file_get_contents(lang_path($code.'.json')), true) ?? []);
            $strings = [];
            foreach (self::SAMPLE_STRINGS as $key) {
                $strings[$key] = $lines[$key] ?? $key;
            }
            $languages[] = ['code' => $code, 'name' => $name, 'strings' => $strings];
        }

        return $languages;
    }
}
