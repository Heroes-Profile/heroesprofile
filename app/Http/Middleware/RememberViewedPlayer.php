<?php

namespace App\Http\Middleware;

use App\Rules\BattletagInputProhibitCharacters;
use App\Services\GlobalDataService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the player whose page is being viewed into the header's recent players (alt_search_account1-3)
 * before the page renders, so they show on this load rather than the next one. Same slot rules as
 * rememberSearchAccount() in app.js: an existing entry is refreshed, otherwise the first empty slot,
 * otherwise the oldest.
 */
class RememberViewedPlayer
{
    private const SLOTS = ['alt_search_account1', 'alt_search_account2', 'alt_search_account3'];

    public function __construct(private GlobalDataService $globalDataService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->isMethod('GET') ? $this->viewedAccount($request) : null;
        if (! $account) {
            return $next($request);
        }

        $slots = $this->remember($request, $account);

        // This render reads the request's cookies
        foreach ($slots as $name => $value) {
            $request->cookies->set($name, $value);
        }

        $response = $next($request);

        // Host-only and not Secure, exactly like the ones app.js writes, so the header's Remove (which clears them from JS) matches.
        // Cookie::make would add the session's domain.
        if ($response->getStatusCode() === 200) {
            foreach ($slots as $name => $value) {
                $response->headers->setCookie(new Cookie($name, $value, now()->addDays(90), '/', null, false, false, false, Cookie::SAMESITE_STRICT));
            }
        }

        return $response;
    }

    private function viewedAccount(Request $request): ?array
    {
        $route = $request->route();
        if (! $route || ! str_contains($route->uri(), 'Player/{battletag}/{blizz_id}')) {
            return null;
        }

        $battletag = (string) $route->parameter('battletag');
        $blizzId = (string) $route->parameter('blizz_id');
        if (! (new BattletagInputProhibitCharacters)->passes('battletag', $battletag) || ! ctype_digit($blizzId)) {
            return null;
        }

        $date = now()->utc()->format('Y-m-d\TH:i:s.v\Z');

        if (str_starts_with($route->uri(), 'Player/')) {
            $region = (int) $route->parameter('region');
            if (! isset($this->globalDataService->getRegionIDtoString()[$region])) {
                return null;
            }

            return ['battletag' => $battletag, 'blizz_id' => (int) $blizzId, 'region' => $region, 'date' => $date];
        }

        if (str_starts_with($route->uri(), 'Esports/Other/')) {
            return ['battletag' => $battletag, 'blizz_id' => $blizzId, 'league' => 'Other', 'series' => (string) $route->parameter('series'), 'date' => $date];
        }

        $league = $route->parameter('esport');
        if ($league === 'HeroesInternational') {
            $tournament = $request->query('tournament');
            if (! in_array($tournament, ['main', 'nationscup'], true)) {
                return null;
            }

            return ['battletag' => $battletag, 'blizz_id' => $blizzId, 'league' => $league, 'tournament' => $tournament, 'date' => $date];
        }

        if (! in_array($league, ['NGS', 'CCL', 'MastersClash'], true)) {
            return null;
        }

        return ['battletag' => $battletag, 'blizz_id' => $blizzId, 'league' => $league, 'date' => $date];
    }

    /** @return array<string, string> cookie name => JSON, for every filled slot */
    private function remember(Request $request, array $account): array
    {
        $existing = [];
        foreach (self::SLOTS as $name) {
            $decoded = json_decode((string) $request->cookie($name), true);
            $existing[] = is_array($decoded) ? $decoded : null;
        }

        $sameEntry = fn ($entry) => $entry
            && ($entry['battletag'] ?? null) === $account['battletag']
            && (string) ($entry['blizz_id'] ?? '') === (string) $account['blizz_id']
            && ($entry['league'] ?? null) === ($account['league'] ?? null)
            && ($entry['tournament'] ?? null) === ($account['tournament'] ?? null)
            && ($entry['series'] ?? null) === ($account['series'] ?? null);

        $index = null;
        foreach ($existing as $i => $entry) {
            if ($sameEntry($entry)) {
                $index = $i;
                break;
            }
        }

        if ($index !== null) {
            $existing[$index]['date'] = $account['date'];
            if (isset($account['region'])) {
                $existing[$index]['region'] = $account['region'];
            }
        } else {
            $index = array_search(null, $existing, true);
            if ($index === false) {
                $index = 0;
                foreach ($existing as $i => $entry) {
                    if (strtotime($entry['date'] ?? '') < strtotime($existing[$index]['date'] ?? '')) {
                        $index = $i;
                    }
                }
            }
            $existing[$index] = $account;
        }

        $slots = [];
        foreach ($existing as $i => $entry) {
            if ($entry) {
                $slots[self::SLOTS[$i]] = json_encode($entry);
            }
        }

        return $slots;
    }
}
