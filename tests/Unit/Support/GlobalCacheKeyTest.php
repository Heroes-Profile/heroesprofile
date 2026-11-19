<?php

namespace Tests\Unit\Support;

use App\Support\GlobalCacheKey;
use PHPUnit\Framework\TestCase;

class GlobalCacheKeyTest extends TestCase
{
    public function test_normalize_drops_transport_and_empty_values_and_sorts(): void
    {
        $this->assertSame([
            'game_map' => ['Braxis Holdout', 'Cursed Hollow'],
            'hero' => 'Abathur',
            'mirror' => '0',
            'region' => '1',
        ], GlobalCacheKey::normalize([
            'region' => 1,
            'api_token' => 'secret',
            'mode' => 'csv',
            'hero' => 'Abathur',
            'empty' => '',
            'missing' => null,
            'mirror' => false,
            'game_map' => ['Cursed Hollow', 'Braxis Holdout'],
        ]));
    }

    public function test_the_same_question_gives_the_same_key_however_it_arrived(): void
    {
        $site = GlobalCacheKey::for('GlobalHeroStats', [10, 11], ['hero' => null, 'region' => [2, 1], 'mirror' => 0]);
        $api = GlobalCacheKey::for('GlobalHeroStats', [10, 11], ['api_token' => 'abc', 'mirror' => '0', 'region' => ['1', '2']]);

        $this->assertSame($site, $api);
        $this->assertStringStartsWith('GlobalHeroStats|10,11|', $site);
    }

    public function test_version_one_keeps_the_unversioned_prefix(): void
    {
        $this->assertSame('GlobalHeroStats', GlobalCacheKey::prefix('GlobalHeroStats'));
        $this->assertSame('GlobalHeroStats|v3', GlobalCacheKey::prefix('GlobalHeroStats', 3));
        $this->assertStringStartsWith('GlobalHeroStats|v2|5|', GlobalCacheKey::for('GlobalHeroStats', [5], [], 2));
    }

    public function test_with_parameters_swaps_only_the_hash(): void
    {
        $key = GlobalCacheKey::for('GlobalHeroStats', [5], ['game_map' => 'Cursed Hollow']);
        $other = GlobalCacheKey::for('GlobalHeroStats', [5], ['game_map' => 'Sky Temple']);

        $this->assertSame($other, GlobalCacheKey::withParameters($key, ['game_map' => 'Sky Temple']));
        $this->assertSame('nopipe', GlobalCacheKey::withParameters('nopipe', ['a' => 1]));
    }

    public function test_key_hash_is_stable(): void
    {
        // Changing this hash orphans every cached global stats entry
        $this->assertSame(
            'GlobalHeroStats|1|'.hash('sha256', '{"hero":"Abathur","region":["1","2"]}'),
            GlobalCacheKey::for('GlobalHeroStats', [1], ['region' => [2, 1], 'hero' => 'Abathur'])
        );
    }
}
