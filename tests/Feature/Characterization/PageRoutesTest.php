<?php

namespace Tests\Feature\Characterization;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/** Every public page, as an anonymous visitor. */
class PageRoutesTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    public static function pages(): array
    {
        $player = 'Player/'.self::PLAYER_BATTLETAG.'/'.self::PLAYER_BLIZZ_ID.'/'.self::PLAYER_REGION;
        $hero = rawurlencode(self::HERO);
        $map = rawurlencode(self::MAP);
        $replay = self::REPLAY_ID;

        $uris = [
            '/', 'Contact', 'Privacy/Policy', 'Terms/Of/Service', 'FAQ', 'Community/Support', 'ads.txt',
            'Upload', 'Upload/Embed', 'Upload/Widget', 'Upload/Uploader',
            'Github/Change/Log', 'Authenticate/Battlenet', 'Authenticate/Battlenet/Failed', 'Authenticate/Patreon/Failed',
            'Event/Xalatath', 'Flair/State', 'Search',
            'Global/Hero', 'Global/Hero/Maps', "Global/Hero/Maps/{$hero}",
            'Global/Matchups', "Global/Matchups/{$hero}", 'Global/Matchups/Talents', "Global/Matchups/Talents/{$hero}/Auriel",
            'Global/Draft', "Global/Draft/{$hero}", 'Drafter',
            'Global/Talents', "Global/Talents/{$hero}", 'Global/Talents/Builder', "Global/Talents/Builder/{$hero}",
            'Global/Leaderboard', 'Global/Compositions', 'Global/Party',
            'Heroes', "Heroes/{$hero}", 'Heroes/NotAHero',
            $player, "{$player}/FriendFoe", "{$player}/Party", "{$player}/Hero", "{$player}/Hero/{$hero}",
            "{$player}/Matchups", "{$player}/Role", "{$player}/Role/Support", "{$player}/Map", "{$player}/Map/{$map}",
            "{$player}/Talents", "{$player}/Talents/{$hero}", "{$player}/MMR", "{$player}/Awards",
            "{$player}/Match/History", "{$player}/Match/Latest",
            'Player/Nobody/1/1', 'Player/Bad\'Name/abc/9',
            'Match/Search', "Match/Single/{$replay}", 'Match/Single?replayID='.$replay, 'Match/Single/999999999999',
            'Match/Prediction/Game', 'Tools/RandomizeMe', 'Tools/Activity', 'Tools/AutoBattler',
            'PreMatch/Results', 'PreMatch/Results/1',
            'Esports', 'Esports/NGS', 'NGS', 'Esports/CCL', 'Esports/MastersClash', 'Esports/HeroesInternational', 'Esports/NutCup', 'Esports/Other',
            'Twitch', 'Twitch/Guidelines', 'Analytics', 'Analytics/Sample', 'Analytics/Sample/Match/Single', 'DeepAnalytics',
            'Profile/Settings', 'Battlenet/Logout',
            'Api', 'Api/DeveloperTier', 'Api/EndpointLimits', 'Api/Endpoint/Limits', 'Api/Docs', 'Api/Migrating',
            'Api/Terms', 'Api/Privacy', 'Api/Login', 'Api/Register', 'Api/Password/Forgot', 'Api/Account', 'Api/Admin',
            'This/Page/Does/Not/Exist',
        ];

        return collect($uris)->mapWithKeys(fn ($uri) => [$uri => [$uri]])->all();
    }

    #[DataProvider('pages')]
    public function test_page(string $uri): void
    {
        $response = $this->get('/'.ltrim($uri, '/'));

        $this->assertResponseMatchesSnapshot('page '.$uri, $response);
    }
}
