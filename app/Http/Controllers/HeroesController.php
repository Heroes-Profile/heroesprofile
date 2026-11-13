<?php

namespace App\Http\Controllers;

use App\Rules\HeroInputValidation;
use App\Services\HeroPageService;
use App\Services\LocalizedGameStrings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class HeroesController extends Controller
{
    public function index(HeroPageService $heroPageService)
    {
        return view('Heroes.heroesIndex')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroes' => $heroPageService->getIndex(),
            ]);
    }

    public function show(Request $request, HeroPageService $heroPageService, LocalizedGameStrings $localizedGameStrings, $hero)
    {
        $validator = Validator::make(['hero' => $hero], [
            'hero' => ['required', new HeroInputValidation],
        ]);

        if ($validator->fails()) {
            return redirect('/Heroes');
        }

        $heroModel = $this->globalDataService->getHeroModel($hero);
        $herodata = $heroPageService->getHero($heroModel);
        $metadata = $herodata['metadata'];

        $locale = App::getLocale();
        if ($locale !== 'en') {
            $herodata['talents'] = $localizedGameStrings->localizeTalents($herodata['talents']->toArray(), $locale);
            $herodata['abilities'] = $localizedGameStrings->localizeAbilities($herodata['abilities']->toArray(), $locale, $heroModel->name);
            $herodata['localized'] = $localizedGameStrings->hero($locale, $heroModel->id);
        }

        $summary = $heroModel->name.($metadata && $metadata->title ? ', '.$metadata->title.',' : '')
            .' is a'.(preg_match('/^[AEIOU]/i', $heroModel->new_role ?? '') ? 'n ' : ' ').($heroModel->new_role ?? '').' hero in Heroes of the Storm.';
        $art = 'images/heroes_rectangle_large/'.$heroModel->short_name.'.jpg';

        return view('Heroes.heroPage')
            ->with([
                'bladeGlobals' => $this->globalDataService->getBladeGlobals(),
                'heroname' => $heroModel->name,
                'herodata' => $herodata,
                'metadescription' => trim($summary.' '.($metadata->description ?? '')).' Every ability and talent tooltip, plus base stats at any level from 1 to 30.',
                'ogimage' => file_exists(public_path($art)) ? asset($art) : null,
                'urlparameters' => $request->all(),
            ]);
    }
}
