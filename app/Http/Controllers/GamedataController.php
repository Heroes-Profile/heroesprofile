<?php

namespace App\Http\Controllers;

use Heroes\Locator;
use Heroes\Factories\HeroFactory;
use Heroes\Providers\StringProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ReflectionClass;
use Throwable;

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
	protected function heroes(Request $request)
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
		return view('Gamedata.heroes', $data);
	}

	/**
	 * Displays the table of Hero data.
	 *
	 * @param Request $request
	 * @param string $id ID of the requested Hero
	 *
	 * @return View|RedirectResponse
	 */
	protected function hero(Request $request, string $id)
	{
		$data = $this->getFormData($request);

		try
		{
			$heroes = new HeroFactory($data['locale'], $data['patch']);
		}
		catch (Throwable $e)
		{
			return back()->withInput()->with('status', $e->getMessage());
		}

		if (! $data['hero'] = $heroes->get($id))
		{
			return back()->withInput()->with('status', 'Unable to locate hero "' . $id . '" in patch ' . $data['patch']);
		}

		$data['strings'] = [
			'title'        => 'Title',
			'type'         => 'Type',
			'expandedrole' => 'Role',
			'difficulty'   => 'Difficulty',
			'lifetype'     => 'Life',
			'shieldtype'   => 'Shield',
			'energytype'   => 'Energy',
		];

		$data['stats'] = [
			'radius'      => 'Size',
			'speed'       => 'Speed',
			'sight'       => 'Vision',
		];

		$data['title'] = $data['hero']->string('name') . ' Data';

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
		$data  = [
			'locales' => StringProvider::LOCALE,
			'patches' => Locator::getPatches(),
			'status'  => $request->session()->get('status'),
		];

		// Set the current locale and patch
		$data['locale'] = ($locale = $request->input('locale')) && in_array($locale, $data['locales'])
		    ? $locale
		    : StringProvider::LOCALE['USA'];

		$data['patch'] = ($patch = $request->input('patch')) && in_array($patch, $data['patches'])
		    ? $patch
		    : Locator::getLatest();

		return $data;
	}
}
