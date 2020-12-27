<?php

namespace App\Http\Controllers;

use Heroes\Locator;
use Heroes\Factories\HeroFactory;
use Heroes\Providers\StringProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ReflectionClass;

/**
 * Gamedata Controller Class
 *
 * Allows web users to browse through
 * game data by patch and locale, as
 * served by HeroesPatchNotes' SDK.
 */
class GamedataController extends Controller
{
	/**
	 * Displays the table of Hero data.
	 *
	 * @param Request $request
	 *
	 * @return View|RedirectResponse
	 */
	protected function hero(Request $request)
	{
		$data = $this->getFormData($request);

		try
		{
			$data['heroes'] = new HeroFactory($data['locale'], $data['patch']);
		}
		catch (Throwable $e)
		{
			return back()->withInput()->with('status', $e->getMessage());
		}

		$data['title'] = 'Hero Data';
		return view('Gamedata.hero', $data);
	}

	/**
	 * Retrieves data needed for the form.
	 *
	 * @param Request $request
	 *
	 * @return array
	 */
	private function getFormData(Request $request): array
	{
        $class = new ReflectionClass(StringProvider::class);
		$data  = [
			'locales' => $class->getConstants(),
			'patches' => Locator::getPatches(),
		];

		// Set the current locale and patch
		$data['locale'] = ($locale = $request->input('locale')) && in_array($locale, $data['locales'])
		    ? $locale
		    : StringProvider::USA;

		$data['patch'] = ($patch = $request->input('patch')) && in_array($patch, $data['patches'])
		    ? $patch
		    : Locator::getLatest();

		return $data;
	}
}
