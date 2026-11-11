<?php

namespace App\Services\Api;

use App\Services\GlobalDataService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\Config;

/**
 * Battle.net sign-in on the API portal, to find which player an API account belongs to. It only
 * records the player; unlike the main site's, it never signs anyone in. Each feature using it has
 * its own callback, registered as an extra redirect URI on the Blizzard app.
 */
class BattlenetPlayerLink
{
    public const REGIONS = [1 => 'NA', 2 => 'EU', 3 => 'KR', 5 => 'CN'];

    public function __construct(private GlobalDataService $globalDataService) {}

    public function redirect(string $callbackPath)
    {
        return Socialite::driver('battlenet')->setConfig($this->config($callbackPath))->redirect();
    }

    /**
     * The player who signed in, found in our data for the region they picked, or why not.
     *
     * @return array{blizz_id: int, battletag: string}|string
     */
    public function player(Request $request, string $callbackPath, int $region): array|string
    {
        if ($request->filled('error') || ! $request->filled('code')) {
            return 'Battle.net sign-in did not complete. Please try again.';
        }

        try {
            $user = Socialite::driver('battlenet')->setConfig($this->config($callbackPath))->user();
        } catch (InvalidStateException $e) {
            return 'Battle.net sign-in did not complete. Please try again.';
        }

        $blizzId = $this->globalDataService->getBlizzIDGivenFullBattletag($user->nickname, $region);

        if (! $blizzId) {
            return "We have no games for {$user->nickname} in ".self::REGIONS[$region].' yet. '
                .'Upload a replay from that region and try again, or pick the region you play in.';
        }

        return ['blizz_id' => (int) $blizzId, 'battletag' => $user->nickname];
    }

    private function config(string $callbackPath): Config
    {
        return new Config(
            config('services.battlenet.client_id'),
            config('services.battlenet.client_secret'),
            url($callbackPath),
            ['region' => 'us'],
        );
    }
}
