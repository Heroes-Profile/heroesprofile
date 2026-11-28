<?php

namespace Tests\Feature\Characterization;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/** Esports pages and their data endpoints, against each league's seeded 40-replay slice. */
class EsportsTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    public static function pages(): array
    {
        $team = rawurlencode(self::NGS_TEAM);
        $player = 'Esports/NGS/Player/'.rawurlencode(self::NGS_PLAYER_BATTLETAG).'/'.self::NGS_PLAYER_BLIZZ_ID;
        $season = '?season='.self::NGS_SEASON;

        $uris = [
            'Esports/NGS'.$season,
            'Esports/NGS/Division/'.rawurlencode(self::NGS_DIVISION).$season,
            'Esports/NGS/Division/'.rawurlencode(self::NGS_DIVISION).'/Match/History'.$season,
            "Esports/NGS/Team/{$team}".$season,
            "Esports/NGS/Team/{$team}/Match/History".$season,
            $player, "{$player}/Hero", "{$player}/Hero/".rawurlencode(self::PLAYER_HERO), "{$player}/Map",
            "{$player}/Map/".rawurlencode(self::MAP), "{$player}/Role", "{$player}/Talents", "{$player}/Matchups",
            "{$player}/FriendFoe", "{$player}/Match/History", "{$player}/Match/Latest",
            'Esports/NGS/Match/Single/'.self::NGS_REPLAY_ID,
            'Esports/CCL?season='.self::CCL_SEASON,
            'Esports/CCL/Team/'.rawurlencode(self::CCL_TEAM).'?season='.self::CCL_SEASON,
            'Esports/MastersClash?season='.self::MCL_SEASON,
            'Esports/MastersClash/Team/'.rawurlencode(self::MCL_TEAM).'?season='.self::MCL_SEASON,
            'Esports/HeroesInternational?tournament=main&season='.self::HI_SEASON,
            'Esports/HeroesInternational/Team/'.rawurlencode(self::HI_TEAM).'?tournament=main&season='.self::HI_SEASON,
            'Esports/NutCup?season='.self::NUTCUP_SEASON,
            'Esports/Other/'.rawurlencode(self::OTHER_SERIES),
        ];

        // The other leagues' player pages share NGS's controller; each shows its own league name
        $battletag = rawurlencode(self::NGS_PLAYER_BATTLETAG).'/'.self::NGS_PLAYER_BLIZZ_ID;
        foreach ([
            'Esports/MastersClash/Player/'.$battletag => '?season='.self::MCL_SEASON,
            'Esports/HeroesInternational/Player/'.$battletag => '?tournament=main&season='.self::HI_SEASON,
            'Esports/HeroesInternational/Player/'.$battletag.'/Hero' => '?tournament=nationscup&season='.self::HI_SEASON,
            'Esports/CCL/Player/'.$battletag => '?season='.self::CCL_SEASON,
            'Esports/Other/'.rawurlencode(self::OTHER_SERIES).'/Player/'.$battletag => '',
            'Esports/Other/'.rawurlencode(self::OTHER_SERIES).'/Player/'.$battletag.'/Map' => '',
            'Esports/Other/'.rawurlencode(self::OTHER_SERIES).'/Player/'.$battletag.'/Match/History' => '',
            'Esports/MastersClash/Player/'.$battletag.'/Match/History' => '?season='.self::MCL_SEASON,
        ] as $page => $query) {
            $uris[] = $page.$query;
        }

        return collect($uris)->mapWithKeys(fn ($uri) => [$uri => [$uri]])->all();
    }

    #[DataProvider('pages')]
    public function test_page(string $uri): void
    {
        $this->assertResponseMatchesSnapshot('esports page '.$uri, $this->get('/'.$uri));
    }

    public static function endpoints(): array
    {
        $ngs = ['esport' => 'NGS', 'season' => self::NGS_SEASON];
        $ngsPlayer = $ngs + ['battletag' => self::NGS_PLAYER_BATTLETAG, 'blizz_id' => self::NGS_PLAYER_BLIZZ_ID];
        // The player pages send the route's blizz_id as a string, and the single-player endpoints validate it as one
        $ngsPagePlayer = ['esport' => 'NGS', 'battletag' => self::NGS_PLAYER_BATTLETAG, 'blizz_id' => (string) self::NGS_PLAYER_BLIZZ_ID];

        return [
            'ngs standings' => ['esports/ngs/standings', ['season' => self::NGS_SEASON, 'division' => self::NGS_DIVISION]],
            'ngs divisions' => ['esports/ngs/divisions', ['season' => self::NGS_SEASON]],
            'ngs teams' => ['esports/ngs/teams', ['season' => self::NGS_SEASON]],
            'ngs player search' => ['esports/ngs/player/search', ['userinput' => 'Linehouse']],
            'ngs matches' => ['esports/ngs/matches', $ngs + ['pagination_page' => 1]],
            'ngs hero stats' => ['esports/ngs/hero/stats', $ngs],
            'ngs hero talents stats' => ['esports/ngs/hero/talents/stats', $ngs + ['hero' => self::PLAYER_HERO]],
            'ngs division single' => ['esports/ngs/division/single', ['season' => self::NGS_SEASON, 'division' => self::NGS_DIVISION]],
            'ngs division match history' => ['esport/division/match/history', ['season' => self::NGS_SEASON, 'division' => self::NGS_DIVISION, 'pagination_page' => 1]],
            'ngs single team' => ['esports/single/team', $ngs + ['team' => self::NGS_TEAM]],
            'ngs single team matches' => ['esports/single/team/matches', $ngs + ['team' => self::NGS_TEAM, 'pagination_page' => 1]],
            'ngs team match history' => ['esports/team/match/history', $ngs + ['team' => self::NGS_TEAM, 'pagination_page' => 1]],
            'ngs single player' => ['esports/single/player', $ngsPagePlayer + ['season' => self::NGS_SEASON]],
            'ngs single player hero' => ['esports/single/player/hero', $ngsPagePlayer + ['season' => self::NGS_SEASON, 'hero' => self::PLAYER_HERO]],
            'ngs single player match history' => ['esports/single/player/match/history', $ngsPagePlayer + ['pagination_page' => 1]],
            'ngs player heroes' => ['esports/ngs/player/heroes', $ngsPlayer],
            'ngs player hero' => ['esports/ngs/player/hero', $ngsPlayer + ['hero' => self::PLAYER_HERO]],
            'ngs player maps' => ['esports/ngs/player/maps', $ngsPlayer],
            'ngs player roles' => ['esports/ngs/player/roles', $ngsPlayer],
            'ngs player talents' => ['esports/ngs/player/talents', $ngsPlayer + ['hero' => self::PLAYER_HERO]],
            'ngs player matchups' => ['esports/ngs/player/matchups', $ngsPlayer],
            'ngs player friendfoe' => ['esports/ngs/player/friendfoe', $ngsPlayer],
            'ngs player match history' => ['esports/ngs/player/match/history', $ngsPlayer + ['pagination_page' => 1]],
            'ngs match single' => ['match/single', ['esport' => 'NGS', 'replayID' => self::NGS_REPLAY_ID]],

            'ccl organizations' => ['esports/ccl/organizations', ['season' => self::CCL_SEASON]],
            'ccl matches' => ['esports/ccl/matches', ['esport' => 'CCL', 'season' => self::CCL_SEASON, 'pagination_page' => 1]],
            'ccl hero stats' => ['esports/ccl/hero/stats', ['esport' => 'CCL', 'season' => self::CCL_SEASON]],
            'mcl teams' => ['esports/mastersclash/teams', ['season' => self::MCL_SEASON]],
            'mcl matches' => ['esports/mastersclash/matches', ['esport' => 'MastersClash', 'season' => self::MCL_SEASON, 'pagination_page' => 1]],
            'mcl hero stats' => ['esports/mastersclash/hero/stats', ['esport' => 'MastersClash', 'season' => self::MCL_SEASON]],
            'hi teams' => ['esports/heroesinternational/teams', ['esport' => 'hi', 'season' => self::HI_SEASON]],
            'hi matches' => ['esports/heroesinternational/matches', ['esport' => 'HeroesInternational', 'tournament' => 'main', 'season' => self::HI_SEASON, 'pagination_page' => 1]],
            'hi hero stats' => ['esports/heroesinternational/hero/stats', ['esport' => 'hi', 'tournament' => 'main', 'season' => self::HI_SEASON]],
            'hi nations cup hero stats' => ['esports/heroesinternational/hero/stats', ['esport' => 'hi_nc', 'tournament' => 'nationscup', 'season' => self::HI_SEASON]],
            'nutcup hero stats' => ['esports/nutcup/hero/stats', ['esport' => 'NutCup', 'season' => self::NUTCUP_SEASON]],
            'other teams' => ['esports/other/teams', ['series' => self::OTHER_SERIES]],
            'other all matches' => ['esports/other/get/all/matches', ['series' => self::OTHER_SERIES_ID, 'pagination_page' => 1]],
            'other hero stats' => ['esports/other/'.self::OTHER_SERIES.'/hero/stats', ['esport' => 'Other', 'series' => self::OTHER_SERIES]],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_endpoint(string $uri, array $payload): void
    {
        $this->assertResponseMatchesSnapshot('esports post '.$this->dataName(), $this->postJson('/api/v1/'.$uri, $payload));
    }
}
