<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BattlenetAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class BattleNetController extends Controller
{
    public function show(Request $request)
    {
        return view('Battlenet.authenticate')
            ->with([
                'regions' => $this->globalDataService->getRegionIDtoString(),
                'filters' => $this->globalDataService->getFilterData(),
            ]);
    }

    public function redirectToProvider()
    {
        return Socialite::driver('battlenet')->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        $clientId = env('BATTLENET_KEY', false);
        $clientSecret = env('BATTLENET_SECRET', false);
        $redirectUrl = env('BATTLENET_REDIRECT_URI', false);
        $additionalProviderConfig = ['region' => 'us'];
        $config = new \SocialiteProviders\Manager\Config($clientId, $clientSecret, $redirectUrl, $additionalProviderConfig);

        $user = Socialite::driver('battlenet')->setConfig($config)->user();

        $battlenetAccount = BattlenetAccount::updateOrCreate(
            ['battlenet_id' => $user->id],
            [
                'battletag' => $user->nickname,
                'blizz_id' => $this->globalDataService->getBlizzIDGivenFullBattletag($user->nickname, $request->cookie('battlenet_region')),
                'region' => $request->cookie('battlenet_region'),
                'battlenet_access_token' => $user->accessTokenResponseBody['access_token'],
                'remember_token' => $user->token,
                // Other necessary fields like email, etc.
            ]
        );

        Auth::login($battlenetAccount, true);

        return redirect('/Profile/Settings'); // Redirect to desired location
    }

    public function logout()
    {
        Auth::logout();
        session()->forget('patreonSubscriberSiteFlair');
        session()->forget('patreonSubscriberAdFree');

        return redirect('/');
    }
}
