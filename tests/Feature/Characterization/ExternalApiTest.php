<?php

namespace Tests\Feature\Characterization;

use App\Support\ApiSpecConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/**
 * The public API (api/external/v1), one call per endpoint in config/api_spec.php with its
 * required parameters filled from the seeded database.
 */
class ExternalApiTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    // Plaintext keys ApiAccountsSeeder gives its accounts ("basic" is migrated, so it gets live data)
    private static function key(string $account): string
    {
        return str_pad($account.'_local_api_key_', 60, '0');
    }

    // Uploads, deletes and job polling need real files or queued work
    private const SKIPPED = [
        'api.external.ngs.games.upload', 'api.external.ngs.games.delete', 'api.external.upload',
        'api.external.prematch', 'api.external.replay.download', 'api.external.jobs',
    ];

    private static function seedValues(): array
    {
        return [
            'battletag' => self::PLAYER_BATTLETAG.'#194',
            'region' => 'NA',
            'hero' => self::HERO,
            'ally_enemy' => 'Auriel',
            'role' => 'Support',
            'game_map' => self::MAP,
            'map' => self::MAP,
            'timeframe_type' => 'minor',
            'timeframe' => self::GAME_VERSION,
            'game_type' => 'Storm League',
            'replayID' => self::REPLAY_ID,
            'award_id' => 1,
            'type' => 'friend',
            'composition_id' => 1,
            'mmr' => 2400,
            'division' => 'A',
            'team' => 'Test',
        ];
    }

    public static function endpoints(): array
    {
        $spec = require __DIR__.'/../../../config/api_spec.php';
        $cases = [];

        foreach ($spec['endpoints'] as $name => $endpoint) {
            if (in_array($name, self::SKIPPED, true)) {
                continue;
            }

            $values = self::seedValues();
            if (str_starts_with($name, 'api.external.players.')) {
                $values['hero'] = self::PLAYER_HERO;
            }

            $required = [];
            foreach (ApiSpecConfig::resolve($endpoint, $spec) as $parameter => $definition) {
                if (($definition['required'] ?? false) || $parameter === 'replayID') {
                    $required[$parameter] = $values[$parameter] ?? $definition['example'] ?? null;
                }
            }
            if (str_starts_with($name, 'api.external.ngs.')) {
                $ngs = ['season' => self::NGS_SEASON, 'division' => self::NGS_DIVISION, 'team' => self::NGS_TEAM,
                    'battletag' => self::NGS_PLAYER_BATTLETAG, 'blizz_id' => self::NGS_PLAYER_BLIZZ_ID, 'replayID' => self::NGS_REPLAY_ID];
                foreach (ApiSpecConfig::resolve($endpoint, $spec) as $parameter => $definition) {
                    if (isset($ngs[$parameter])) {
                        $required[$parameter] = $ngs[$parameter];
                    }
                }
                if (isset($required['hero'])) {
                    $required['hero'] = self::PLAYER_HERO;
                }
            }
            if ($name === 'api.external.heroes.matchups.talents') {
                $required['timeframe'] = self::MATCHUP_TALENTS_GAME_VERSION;
            }
            if ($name === 'api.external.leaderboard') {
                $required += ['season' => 26, 'game_type' => 'Storm League', 'type' => 'player', 'groupsize' => 'All'];
            }
            if ($name === 'api.external.heroes.talents.builder.replays') {
                $required = ['game_type' => 'ARAM', 'hero' => self::BUILDER_REPLAYS_HERO, 'selectedtalents' => ['1' => self::BUILDER_REPLAYS_TALENT]] + $required;
            }

            $cases[$name] = [$name, $required, self::key('basic')];
        }

        $cases['api.external.heroes.stats fixtures account'] = ['api.external.heroes.stats', [
            'timeframe_type' => 'minor', 'timeframe' => self::GAME_VERSION, 'game_type' => 'Storm League',
        ], self::key('fixtures')];
        $cases['api.external.maps no key'] = ['api.external.maps', [], null];
        $cases['api.external.maps bad key'] = ['api.external.maps', [], str_repeat('x', 60)];
        $cases['api.external.heroes.stats csv'] = ['api.external.heroes.stats', [
            'timeframe_type' => 'minor', 'timeframe' => self::GAME_VERSION, 'game_type' => 'Storm League', 'mode' => 'csv',
        ], self::key('basic')];

        return $cases;
    }

    #[DataProvider('endpoints')]
    public function test_endpoint(string $routeName, array $parameters, ?string $key): void
    {
        $routeParameters = array_intersect_key($parameters, array_flip(['replayID']));
        $query = array_diff_key($parameters, $routeParameters) + ($key ? ['api_token' => $key] : []);

        $response = $this->get(route($routeName, $routeParameters, false).'?'.http_build_query($query));

        $this->assertResponseMatchesSnapshot('external '.$this->dataName(), $response);
    }
}
