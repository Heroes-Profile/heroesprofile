<?php

namespace Tests\Feature\Characterization;

use App\Http\Middleware\ThrottleNonApiRequests;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\Feature\FeatureTestCase;

/**
 * The browser crawl's page list (tests/browser/pages.json), built from the routes and the seeded
 * values. Fails when a page route is neither crawled nor skipped below, so a new page is crawled
 * from the day it's added, and when a crawled page doesn't load.
 */
class BrowserPagesTest extends FeatureTestCase
{
    use SeedFixtures;

    public const PAGES_FILE = 'tests/browser/pages.json';

    /** Routes the crawl skips, by URI prefix, with why */
    private const SKIPPED = [
        'Api' => 'API portal, its own layout and logins',
        'Analytics/Match' => 'needs a Battle.net login',
        'Analytics/Status' => 'JSON',
        'Analytics/Sample/Match/Single/' => 'JSON',
        'Analytics/{section}' => 'needs a Battle.net login',
        'Animation' => 'animation demos',
        'Authenticate' => 'login flow',
        'authenticate' => 'login flow',
        'redirect/authenticate' => 'login flow',
        'Battlenet/Logout' => 'login flow',
        'Error/Preview' => 'error page previews',
        'Upload' => 'replay upload',
        'Session/CsrfToken' => 'JSON',
        'sanctum' => 'JSON',
        'ads.txt' => 'text file',
        'http://' => 'ads.txt on other hosts',
        'https://' => 'ads.txt on other hosts',
        '{fallbackPlaceholder}' => 'the 404 fallback',
        'DeepAnalytics' => 'redirects to Analytics',
        'Match/Single/{replayID}/Download' => 'needs a Battle.net login',
        'Match/Single' => 'redirects to a replay',
        'PreMatch/Results' => 'needs a live pre-match lookup',
        'battletag/searched' => 'search redirect',
        'Poll' => 'needs a Battle.net login',
        'Profile/Settings' => 'needs a Battle.net login',
        'NGS' => 'redirects to Esports/NGS',
        'Event/Xalatath' => 'the event ended; redirects',
        'Search' => 'redirects',
        'api/' => 'JSON',
    ];

    /** Pages the crawl opens, keyed by route URI */
    public static function pages(): array
    {
        $player = 'Player/'.self::PLAYER_BATTLETAG.'/'.self::PLAYER_BLIZZ_ID.'/'.self::PLAYER_REGION;
        $ngsPlayer = rawurlencode(self::NGS_PLAYER_BATTLETAG).'/'.self::NGS_PLAYER_BLIZZ_ID;
        // Global pages open on the seeded patch, so they show data rather than an empty newest patch
        $patch = '?timeframe_type=minor&timeframe='.self::GAME_VERSION;
        $ngs = '?season='.self::NGS_SEASON;

        return [
            '/' => ['/'],
            'Analytics' => ['/Analytics'],
            'Analytics/Sample' => ['/Analytics/Sample'],
            'Analytics/Sample/Match/Single' => ['/Analytics/Sample/Match/Single'],
            'Community/Support' => ['/Community/Support'],
            'Contact' => ['/Contact'],
            'Drafter' => ['/Drafter'.$patch],
            'Esports' => ['/Esports'],
            'Esports/CCL' => ['/Esports/CCL?season='.self::CCL_SEASON],
            'Esports/HeroesInternational' => ['/Esports/HeroesInternational?tournament=main', '/Esports/HeroesInternational?tournament=nationscup'],
            'Esports/MastersClash' => ['/Esports/MastersClash'],
            'Esports/NGS' => ['/Esports/NGS'.$ngs],
            'Esports/NGS/Division/{division}' => ['/Esports/NGS/Division/'.rawurlencode(self::NGS_DIVISION).$ngs],
            'Esports/NGS/Division/{division}/Match/History' => ['/Esports/NGS/Division/'.rawurlencode(self::NGS_DIVISION).'/Match/History'.$ngs],
            'Esports/NutCup' => ['/Esports/NutCup'],
            'Esports/Other' => ['/Esports/Other'],
            'Esports/Other/{series}' => ['/Esports/Other/'.self::OTHER_SERIES],
            'Esports/Other/{series}/Match/Single/{replayID}' => ['/Esports/Other/'.self::OTHER_SERIES.'/Match/Single/'.self::OTHER_REPLAY_ID],
            'Esports/Other/{series}/Team/{team}' => ['/Esports/Other/'.self::OTHER_SERIES.'/Team/'.rawurlencode(self::OTHER_TEAM)],
            'Esports/Other/{series}/Team/{team}/Match/History' => ['/Esports/Other/'.self::OTHER_SERIES.'/Team/'.rawurlencode(self::OTHER_TEAM).'/Match/History'],
            'Esports/{esport}/Match/Single/{replayID}' => ['/Esports/NGS/Match/Single/'.self::NGS_REPLAY_ID],
            'Esports/{esport}/Organization/{team}' => ['/Esports/CCL/Organization/'.rawurlencode(self::CCL_TEAM).'?season='.self::CCL_SEASON],
            'Esports/{esport}/Team/{team}' => [
                '/Esports/NGS/Team/'.rawurlencode(self::NGS_TEAM).$ngs,
                '/Esports/CCL/Team/'.rawurlencode(self::CCL_TEAM).'?season='.self::CCL_SEASON,
                '/Esports/MastersClash/Team/'.rawurlencode(self::MCL_TEAM).'?season='.self::MCL_SEASON,
            ],
            'Esports/{esport}/Team/{team}/Match/History' => ['/Esports/NGS/Team/'.rawurlencode(self::NGS_TEAM).'/Match/History'.$ngs],
            'FAQ' => ['/FAQ'],
            'Github/Change/Log' => ['/Github/Change/Log'],
            'Global/Compositions' => ['/Global/Compositions'.$patch],
            'Global/Draft' => ['/Global/Draft'],
            'Global/Draft/{hero}' => ['/Global/Draft/'.self::HERO.$patch],
            'Global/Hero' => ['/Global/Hero'.$patch],
            'Global/Hero/Maps' => ['/Global/Hero/Maps'],
            'Global/Hero/Maps/{hero}' => ['/Global/Hero/Maps/'.self::HERO.$patch],
            'Global/Leaderboard' => ['/Global/Leaderboard'],
            'Global/Matchups' => ['/Global/Matchups'],
            'Global/Matchups/Talents' => ['/Global/Matchups/Talents'],
            'Global/Matchups/Talents/{hero}/{allyenemy}' => ['/Global/Matchups/Talents/'.self::HERO.'/Auriel?timeframe_type=minor&timeframe='.self::MATCHUP_TALENTS_GAME_VERSION],
            'Global/Matchups/{hero}' => ['/Global/Matchups/'.self::HERO.$patch],
            'Global/Party' => ['/Global/Party'.$patch],
            'Global/Talents' => ['/Global/Talents'],
            'Global/Talents/Builder' => ['/Global/Talents/Builder'],
            'Global/Talents/Builder/{hero}' => ['/Global/Talents/Builder/'.self::HERO.$patch],
            'Global/Talents/{hero}' => ['/Global/Talents/'.self::HERO.$patch],
            'Heroes' => ['/Heroes'],
            'Heroes/{hero}' => ['/Heroes/'.self::HERO],
            'Match/Prediction/Game' => ['/Match/Prediction/Game'],
            'Match/Search' => ['/Match/Search'],
            'Match/Single/{replayID}' => ['/Match/Single/'.self::REPLAY_ID],
            'Privacy/Policy' => ['/Privacy/Policy'],
            'Redesign/2026' => ['/Redesign/2026'],
            'Team' => ['/Team'],
            'Terms/Of/Service' => ['/Terms/Of/Service'],
            'Tools/Activity' => ['/Tools/Activity'],
            'Tools/AutoBattler' => ['/Tools/AutoBattler'],
            'Tools/RandomizeMe' => ['/Tools/RandomizeMe'],
            'Twitch' => ['/Twitch'],
            'Twitch/Guidelines' => ['/Twitch/Guidelines'],

            // The seeded player and their sub-pages
            'Player/{battletag}/{blizz_id}/{region}' => ['/'.$player],
            'Player/{battletag}/{blizz_id}/{region}/Awards' => ['/'.$player.'/Awards'],
            'Player/{battletag}/{blizz_id}/{region}/FriendFoe' => ['/'.$player.'/FriendFoe'],
            'Player/{battletag}/{blizz_id}/{region}/Hero' => ['/'.$player.'/Hero'],
            'Player/{battletag}/{blizz_id}/{region}/Hero/{hero}' => ['/'.$player.'/Hero/'.self::PLAYER_HERO],
            'Player/{battletag}/{blizz_id}/{region}/MMR' => ['/'.$player.'/MMR'],
            'Player/{battletag}/{blizz_id}/{region}/Map' => ['/'.$player.'/Map'],
            'Player/{battletag}/{blizz_id}/{region}/Map/{map}' => ['/'.$player.'/Map/'.rawurlencode(self::MAP)],
            'Player/{battletag}/{blizz_id}/{region}/Match/History' => ['/'.$player.'/Match/History'],
            'Player/{battletag}/{blizz_id}/{region}/Matchups' => ['/'.$player.'/Matchups'],
            'Player/{battletag}/{blizz_id}/{region}/Party' => ['/'.$player.'/Party'],
            'Player/{battletag}/{blizz_id}/{region}/Role' => ['/'.$player.'/Role'],
            'Player/{battletag}/{blizz_id}/{region}/Role/{role}' => ['/'.$player.'/Role/Support'],
            'Player/{battletag}/{blizz_id}/{region}/Talents' => ['/'.$player.'/Talents'],
            'Player/{battletag}/{blizz_id}/{region}/Talents/{hero}' => ['/'.$player.'/Talents/'.self::PLAYER_HERO],

            // The seeded NGS player (the other leagues share the controller), and in the Other series
            ...self::esportsPlayerPages('Esports/{esport}/Player/{battletag}/{blizz_id}', '/Esports/NGS/Player/'.$ngsPlayer, $ngs),
            ...self::esportsPlayerPages('Esports/Other/{series}/Player/{battletag}/{blizz_id}', '/Esports/Other/'.self::OTHER_SERIES.'/Player/'.$ngsPlayer, ''),
        ];
    }

    private static function esportsPlayerPages(string $route, string $path, string $query): array
    {
        $pages = [$route => [$path.$query]];
        foreach (['FriendFoe', 'Hero', 'Map', 'Match/History', 'Matchups', 'Role', 'Talents'] as $page) {
            $pages["{$route}/{$page}"] = ["{$path}/{$page}{$query}"];
        }
        $pages["{$route}/Hero/{hero}"] = ["{$path}/Hero/".self::PLAYER_HERO.$query];
        $pages["{$route}/Map/{map}"] = ["{$path}/Map/".rawurlencode(self::MAP).$query];
        $pages["{$route}/Role/{role}"] = ["{$path}/Role/Support{$query}"];
        $pages["{$route}/Talents/{hero}"] = ["{$path}/Talents/".self::PLAYER_HERO.$query];

        return $pages;
    }

    /** Every page route: GET, in the web group */
    private function pageRoutes(): array
    {
        return collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('GET', $route->methods()) && in_array('web', $route->gatherMiddleware()))
            ->map(fn (Route $route) => $route->uri())
            ->unique()
            ->values()
            ->all();
    }

    private function skipReason(string $uri): ?string
    {
        // Redirects to the newest replay page, which is crawled on its own
        if (str_ends_with($uri, '/Match/Latest')) {
            return 'redirects to a replay';
        }

        foreach (self::SKIPPED as $prefix => $reason) {
            if (str_starts_with($uri, $prefix)) {
                return $reason;
            }
        }

        return null;
    }

    public function test_every_page_route_is_crawled_or_skipped(): void
    {
        $pages = self::pages();
        $missing = [];

        foreach ($this->pageRoutes() as $uri) {
            if (array_key_exists($uri, $pages) || $this->skipReason($uri) !== null) {
                continue;
            }
            $missing[] = $uri;
        }

        $this->assertSame([], $missing, 'Add these routes to BrowserPagesTest::pages() with seeded values, or to SKIPPED with a reason');
    }

    public function test_the_crawled_pages_load_and_the_list_is_written(): void
    {
        // Every page in a row from one client; the rate limits aren't what's under test
        $this->withoutMiddleware([ThrottleRequests::class, ThrottleNonApiRequests::class]);

        $routes = $this->pageRoutes();
        $list = [];
        $failures = [];

        foreach (self::pages() as $route => $paths) {
            if (! in_array($route, $routes)) {
                $failures[] = "{$route}: no such route any more";

                continue;
            }
            foreach ($paths as $path) {
                $status = $this->get($path)->status();
                if ($status !== 200) {
                    $failures[] = "{$path}: HTTP {$status}";

                    continue;
                }
                $list[] = ['route' => $route, 'path' => $path];
            }
        }

        $this->assertSame([], $failures, 'Crawled pages have to load with the seeded values');

        file_put_contents(base_path(self::PAGES_FILE), json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
    }
}
