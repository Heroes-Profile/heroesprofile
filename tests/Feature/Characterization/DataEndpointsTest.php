<?php

namespace Tests\Feature\Characterization;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/** The site's own JSON endpoints (api/v1), posted the way the Vue pages post them. */
class DataEndpointsTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    private const ALL_GAME_TYPES = ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'];

    private static function globalFilters(array $extra = []): array
    {
        return $extra + [
            'timeframe_type' => 'minor',
            'timeframe' => [self::GAME_VERSION],
            'game_type' => ['sl'],
            'region' => null,
            'statfilter' => 'win_rate',
            'hero_level' => null,
            'role' => null,
            'game_map' => null,
            'league_tier' => null,
            'hero_league_tier' => null,
            'role_league_tier' => null,
            'mirror' => 0,
        ];
    }

    private static function player(array $extra = []): array
    {
        return $extra + [
            'battletag' => self::PLAYER_BATTLETAG,
            'blizz_id' => self::PLAYER_BLIZZ_ID,
            'region' => self::PLAYER_REGION,
            'game_type' => self::ALL_GAME_TYPES,
            'season' => null,
        ];
    }

    public static function endpoints(): array
    {
        $global = fn (array $extra = []) => self::globalFilters($extra);
        $player = fn (array $extra = []) => self::player($extra);

        return [
            'main footer' => ['main/footer/data', []],
            'main header' => ['main/header/data', []],
            'battletag search' => ['battletag/search', ['userinput' => self::PLAYER_BATTLETAG]],
            'battletag search invalid' => ['battletag/search', ['userinput' => "x' OR 1=1"]],

            'global hero' => ['global/hero', $global()],
            'global hero by region and map' => ['global/hero', $global(['region' => ['NA'], 'game_map' => [self::MAP]])],
            'global hero kills stat' => ['global/hero', $global(['statfilter' => 'kills'])],
            'global hero missing timeframe' => ['global/hero', $global(['timeframe' => null])],
            'global hero bad game type' => ['global/hero', $global(['game_type' => ['zz']])],
            'global talents' => ['global/talents', $global(['hero' => self::HERO])],
            'global talents build' => ['global/talents/build', $global(['hero' => self::HERO, 'talentbuildtype' => 'Popular'])],
            'global talents build hp' => ['global/talents/build', $global(['hero' => self::HERO, 'talentbuildtype' => 'HP Algorithm'])],
            'global talents build all' => ['global/talents/build/all', $global(['talentbuildtype' => 'Popular'])],
            'global talents builder' => ['global/talents/builder', $global(['hero' => self::HERO, 'selectedtalents' => []])],
            'global talents builder replays' => ['global/talents/builder/replays', $global(['game_type' => ['ar'], 'hero' => self::BUILDER_REPLAYS_HERO, 'selectedtalents' => ['1' => self::BUILDER_REPLAYS_TALENT]])],
            'global hero map' => ['global/hero/map', $global(['hero' => self::HERO])],
            'global matchups' => ['global/matchups', $global(['hero' => self::HERO])],
            'global matchups talents' => ['global/matchups/talents', $global(['timeframe' => [self::MATCHUP_TALENTS_GAME_VERSION], 'hero' => self::HERO, 'ally_enemy' => 'Auriel', 'type' => 'Ally', 'talent_view' => 'hero'])],
            'global matchups talents enemy' => ['global/matchups/talents', $global(['timeframe' => [self::MATCHUP_TALENTS_GAME_VERSION], 'hero' => self::HERO, 'ally_enemy' => 'Auriel', 'type' => 'Enemy', 'talent_view' => 'ally_enemy'])],
            'global compositions' => ['global/compositions', $global(['minimum_games' => 0])],
            'global compositions heroes' => ['global/compositions/heroes', $global(['minimum_games' => 0, 'composition_id' => 1])],
            'global draft' => ['global/draft', $global(['hero' => self::HERO])],
            'drafter draft order' => ['drafter/draft-order', $global()],
            'drafter composition' => ['drafter/composition', $global()],
            'global party' => ['global/party', $global(['teamoneparty' => '00005', 'teamtwoparty' => '00005'])],
            'global leaderboard player' => ['global/leaderboard', ['season' => 26, 'game_type' => 'sl', 'type' => 'player', 'groupsize' => 'All']],
            'global leaderboard hero' => ['global/leaderboard', ['season' => 26, 'game_type' => 'sl', 'type' => 'hero', 'groupsize' => 'All', 'hero' => self::PLAYER_HERO_ID]],
            'global leaderboard rating' => ['global/leaderboard/calculate/rating', ['season' => 26, 'game_type' => 'sl', 'type' => 'player', 'groupsize' => 'All']],
            'global leaderboard map' => ['global/leaderboard', ['season' => 26, 'game_type' => 'sl', 'type' => 'map', 'groupsize' => 'All', 'map' => self::MAP]],
            'global leaderboard map missing' => ['global/leaderboard', ['season' => 26, 'game_type' => 'sl', 'type' => 'map', 'groupsize' => 'All']],
            'global leaderboard map rating' => ['global/leaderboard/calculate/rating', ['season' => 26, 'game_type' => 'sl', 'type' => 'map', 'groupsize' => 'All', 'map' => self::MAP]],

            'player' => ['player', $player()],
            'player one game type' => ['player', $player(['game_type' => ['ar']])],
            'player date range' => ['player', $player(['start_date' => '2023-01-01', 'end_date' => '2024-02-10'])],
            'player missing blizz_id' => ['player', $player(['blizz_id' => null])],
            'player friendfoe friend' => ['player/friendfoe', $player(['type' => 'friend'])],
            'player friendfoe enemy' => ['player/friendfoe', $player(['type' => 'enemy'])],
            'player party' => ['player/party', $player()],
            'player matchups' => ['player/matchups', $player()],
            'player heroes all' => ['player/heroes/all', $player(['type' => 'all', 'page' => 'hero', 'minimumgames' => 0])],
            'player heroes single' => ['player/heroes/single', $player(['type' => 'single', 'page' => 'hero', 'hero' => self::PLAYER_HERO])],
            'player roles all' => ['player/roles/all', $player(['type' => 'all', 'page' => 'role', 'minimumgames' => 0])],
            'player role single' => ['player/role/single', $player(['type' => 'single', 'page' => 'role', 'role' => 'Support'])],
            'player maps all' => ['player/maps/all', $player(['type' => 'all', 'page' => 'map', 'minimumgames' => 0])],
            'player maps single' => ['player/maps/single', $player(['type' => 'single', 'page' => 'map', 'game_map' => self::MAP])],
            'player max stat match' => ['player/find/max/stat/match', $player(['type' => 'all', 'page' => 'hero', 'stat' => 'max_kills', 'value' => self::PLAYER_MAX_KILLS])],
            'player talents' => ['player/talents', $player(['hero' => self::PLAYER_HERO])],
            'player talents build' => ['player/talents/build', $player(['hero' => self::PLAYER_HERO])],
            'player talents build heroes' => ['player/talents/build/heroes', $player()],
            'player talents build hero' => ['player/talents/build/hero', $player(['hero' => self::PLAYER_HERO])],
            'player mmr player' => ['player/mmr', $player(['type' => 'Player', 'game_type' => ['sl']])],
            'player mmr hero' => ['player/mmr', $player(['type' => 'Hero', 'hero' => self::PLAYER_HERO_ID, 'game_type' => ['sl']])],
            'player mmr role' => ['player/mmr', $player(['type' => 'Role', 'role' => 'Healer', 'game_type' => ['sl']])],
            'player mmr map' => ['player/mmr', $player(['type' => 'Map', 'map' => self::MAP, 'game_type' => ['sl']])],
            'player mmr map missing' => ['player/mmr', $player(['type' => 'Map', 'game_type' => ['sl']])],
            'player mmr map brawl' => ['player/mmr', $player(['type' => 'Map', 'map' => 'Escape From Braxis', 'game_type' => ['sl']])],            'player awards' => ['player/awards', $player()],
            'player realrank' => ['player/realrank', $player()],
            'player match history' => ['player/match/history', $player(['pagination_page' => 1])],
            'player match history page 2' => ['player/match/history', $player(['pagination_page' => 2])],
            'player match history details' => ['player/match/history/details', $player(['replayIDs' => [self::REPLAY_ID]])],

            'match single' => ['match/single', ['replayID' => self::REPLAY_ID]],
            'match single missing' => ['match/single', ['replayID' => 999999999999]],
            'match search' => ['match/search', ['game_type' => ['qm'], 'region' => ['NA']]],
            'prematch missing' => ['prematch', ['prematchid' => 1]],
            'prematch recent missing' => ['prematch/recent', ['prematchid' => 1]],

            'tools randomize me' => ['tools/randomize-me', ['hero' => self::HERO]],
            'tools activity' => ['tools/activity/players/unique', []],
            'tools auto battler' => ['tools/auto-battler/talents', ['hero' => self::HERO]],

            'help search' => ['help/search', ['question' => 'how is mmr calculated']],
            'help chat logged out' => ['help/chat', ['messages' => [['role' => 'user', 'content' => 'hi']]]],
            'profile save settings logged out' => ['profile/save/settings', ['darkmode' => 1]],
            'contact invalid' => ['contact', []],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_endpoint(string $uri, array $payload): void
    {
        $response = $this->postJson('/api/v1/'.$uri, $payload);

        $this->assertResponseMatchesSnapshot('post '.$this->dataName(), $response);
    }
}
