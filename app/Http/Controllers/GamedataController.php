<?php

namespace App\Http\Controllers;

use Heroes\Locator;
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
	 * Displays the form for selecting patch,
	 * locale, and Entity type.
	 *
	 * @return View
	 */
	public function index(): View
	{
        $class = new ReflectionClass(StringProvider::class);

		return view('Gamedata.index', [
			'title'   => 'Game Data',
			'patches' => Locator::getPatches(),
			'locales' => $class->getConstants(),
		]);
	}

	/**
	 * Processes index form data and sends to
	 * the appropriate entity type handler.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
	 */
	public function submit(Request $request): RedirectResponse
	{
		$type = $request->input('entity');

        return redirect()->route('/Gamedata/' . $type);
	}

	/**
	 * Displays the Hero select form.
	 *
	 * @param Request $request
	 *
	 * @return View
	 */
	public function hero(): View
	{
	}
}
