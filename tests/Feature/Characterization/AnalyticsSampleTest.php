<?php

namespace Tests\Feature\Characterization;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\FeatureTestCase;

/** The public Analytics sample anyone can open, served from resources/analytics-sample. */
class AnalyticsSampleTest extends FeatureTestCase
{
    use AssertsSnapshots;

    private const MAP = 'Battlefield of Eternity';

    private static function meta(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../../../resources/analytics-sample/meta.json'), true);
    }

    public static function requests(): array
    {
        $meta = self::meta();
        $cases = ['games' => ['games', []], 'games one map' => ['games', ['game_map' => [self::MAP]]]];

        foreach ($meta['tabs'] as $tab) {
            $cases[$tab] = [$tab, []];
            $cases["{$tab} one map"] = [$tab, ['game_map' => [self::MAP]]];
        }
        foreach ($meta['match_tabs'] as $tab) {
            $cases["{$tab} sample game"] = [$tab, ['game' => $meta['replay_id']]];
        }

        $cases['unknown map'] = ['deaths', ['game_map' => ['Cursed Hollow']]];
        $cases['two maps'] = ['deaths', ['game_map' => [self::MAP, 'Cursed Hollow']]];
        $cases['other game'] = ['deaths', ['game' => 1]];
        $cases['unknown tab'] = ['nope', []];

        return $cases;
    }

    #[DataProvider('requests')]
    public function test_sample_data(string $tab, array $payload): void
    {
        $response = $this->postJson("/Analytics/Sample/Data/{$tab}", $payload);

        $this->assertResponseMatchesSnapshot('analytics sample '.$this->dataName(), $response);
    }

    public function test_sample_replay_and_performance(): void
    {
        $this->assertResponseMatchesSnapshot('analytics sample replay', $this->getJson('/Analytics/Sample/Match/Single/Data'));
        $this->assertResponseMatchesSnapshot('analytics sample performance', $this->getJson('/Analytics/Sample/Match/Single/Performance'));
    }
}
