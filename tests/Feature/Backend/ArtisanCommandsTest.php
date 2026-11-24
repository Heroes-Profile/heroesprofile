<?php

namespace Tests\Feature\Backend;

use App\Http\Controllers\Api\DocsController;
use App\Models\Hero;
use App\Models\Map;
use App\Services\AnalyticsQueryService;
use App\Services\AnalyticsSampleService;
use App\Services\AnalyticsService;
use App\Services\Twitch\TwitchExtensionManifest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeBigQuery;

/**
 * The app's own artisan commands. Each one that writes is pointed at a temp directory, so a run
 * never touches the files the repo commits (public/sitemap.xml, the API spec, the analytics sample).
 */
class ArtisanCommandsTest extends FeatureTestCase
{
    private string $temp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temp = sys_get_temp_dir().'/hp-commands-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->temp);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->temp);

        parent::tearDown();
    }

    public function test_the_sitemap_lists_static_pages_heroes_and_playable_maps(): void
    {
        $this->app->usePublicPath($this->temp);

        $this->artisan('sitemap:generate')->expectsOutputToContain('Sitemap generated with')->assertSuccessful();

        $xml = simplexml_load_file($this->temp.'/sitemap.xml');
        $locs = [];
        foreach ($xml->url as $url) {
            $locs[] = (string) $url->loc;
        }

        $heroes = Hero::count();
        $maps = Map::where('playable', 1)->count();
        $this->assertCount(43 + 6 * $heroes + $maps, $locs);
        $this->assertContains('https://www.heroesprofile.com/Global/Talents/Abathur', $locs);
        $this->assertContains('https://www.heroesprofile.com/Global/Draft/Abathur', $locs);
        $this->assertContains('https://www.heroesprofile.com/Esports/MastersClash', $locs);
        $this->assertContains('https://www.heroesprofile.com/Global/Hero/Maps/Cursed%20Hollow', $locs);
    }

    public function test_the_published_api_spec_is_up_to_date_with_the_routes(): void
    {
        $this->artisan('api:build-spec', ['--check' => true])->assertSuccessful();

        // Built fresh, it matches what is committed. The admin-only file is written to resources/, so it's put back as it was
        $admin = DocsController::adminSpecPath();
        $before = File::get($admin);

        try {
            $this->artisan('api:build-spec', ['--out' => $this->temp.'/spec.json'])->assertSuccessful();
            $this->assertSame($before, File::get($admin), 'the admin-only spec is up to date too');
        } finally {
            File::put($admin, $before);
        }

        $this->assertSame(
            json_decode(File::get(public_path('spec/heroesprofile-v1.json')), true),
            json_decode(File::get($this->temp.'/spec.json'), true),
        );
    }

    public function test_every_public_endpoint_has_a_fixture(): void
    {
        $this->artisan('api:check-fixtures')
            ->expectsOutputToContain('public endpoints all have fixtures.')
            ->expectsOutputToContain('Every registry endpoint has a route, or is accounted for.')
            ->assertSuccessful();
    }

    public function test_api_samples_are_captured_anonymised(): void
    {
        $this->artisan('api:capture-samples', ['--endpoint' => ['maps'], '--out' => $this->temp])
            ->expectsOutputToContain('1 captured.')->assertSuccessful();

        $maps = json_decode(File::get($this->temp.'/maps.json'), true);
        $this->assertNotEmpty($maps);
        $fixture = json_decode(File::get(resource_path('api-fixtures/maps.json')), true);
        $this->assertSame(array_keys($fixture), array_keys($maps));
        $this->assertSame(array_keys($fixture['maps'][0]), array_keys($maps['maps'][0]), 'the same shape as the committed fixture');
    }

    public function test_promoting_a_raw_capture_is_refused(): void
    {
        $this->artisan('api:capture-samples', ['--endpoint' => ['maps'], '--promote' => true, '--raw' => true])->assertFailed();
    }

    public function test_the_twitch_game_data_file_is_generated(): void
    {
        $this->artisan('twitch:game-data', ['--out' => $this->temp.'/game-data.json'])->expectsOutputToContain('Wrote ')->assertSuccessful();

        $data = json_decode(File::get($this->temp.'/game-data.json'), true);
        $committed = json_decode(File::get(public_path(config('twitch.game_data_path'))), true);
        $this->assertSame(array_keys($committed), array_keys($data), 'the same sections as the committed export');
        $this->assertNotEmpty($data[array_key_first($data)]);
    }

    public function test_the_twitch_extension_check_compares_images_with_the_released_manifest(): void
    {
        $this->artisan('twitch:check-extension')->assertSuccessful();

        // The manifest is read once per process
        (fn () => self::$manifest = null)->bindTo(null, TwitchExtensionManifest::class)();
        config(['twitch.extension_manifest_path' => 'resources/twitch/does-not-exist.json']);
        $this->artisan('twitch:check-extension')->expectsOutputToContain('No Twitch extension manifest at')->assertSuccessful();
        (fn () => self::$manifest = null)->bindTo(null, TwitchExtensionManifest::class)();
    }

    public function test_the_analytics_sample_is_captured_from_bigquery_and_the_replay_viewer(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00', 'UTC'));
        config(['analytics.sample.blizz_id' => 67280, 'analytics.sample.region' => 1, 'analytics.sample.maps' => ['Battlefield of Eternity']]);

        // Every tab answered with its real rows
        $bigQuery = new FakeBigQuery(app(AnalyticsService::class));
        foreach (File::files(__DIR__.'/../../Fixtures/analytics-bigquery') as $file) {
            $bigQuery->rows += json_decode(File::get($file->getPathname()), true);
        }
        $this->app->instance(AnalyticsQueryService::class, $bigQuery);

        // The sample game's document, and the game as the site stores it
        $doc = File::get(__DIR__.'/../../Fixtures/analytics-replay/66135342.json.gz');
        Storage::fake('gcs-analytics')->put('doc/66135342.json.gz', $doc);
        $parsed = json_decode(gzdecode($doc), true);
        $db = DB::connection('heroesprofile');
        $db->table('replay')->insert(['replayID' => 66135342, 'game_type' => 5, 'game_date' => '2026-10-18 21:35:23', 'game_length' => $parsed['length_s'],
            'game_map' => 1, 'game_version' => '2.55.14.98348', 'region' => 1, 'date_added' => '2026-11-05 20:30:00']);
        $heroes = Hero::all();
        foreach ($parsed['players'] as $player) {
            $hero = $heroes->first(fn ($h) => in_array($player['parser_hero'], [$h->alt_name, $h->build_copy_name], true));
            $db->table('player')->insert(['replayID' => 66135342, 'blizz_id' => $player['blizz_id'], 'battletag' => 0, 'hero' => $hero->id,
                'hero_level' => 20, 'team' => $player['team'], 'winner' => $player['team'] === 0 ? 1 : 0]);
        }
        $db->table('replay_deep')->insert(['replayID' => 66135342, 'extract_v' => $parsed['extract_v'], 'loaded_at' => '2026-11-06 00:00:00']);

        // Writes to a temp directory instead of resources/analytics-sample, and only clears that
        $directory = $this->temp;
        $this->app->instance(AnalyticsSampleService::class, new class($directory) extends AnalyticsSampleService
        {
            public function __construct(private string $directory) {}

            public function write(string $file, array $data): void
            {
                File::put($this->directory.'/'.$file.'.json', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            public function removeExcept(array $files): int
            {
                return 0;
            }
        });
        $committed = count(File::glob(resource_path('analytics-sample').'/*.json'));

        $this->artisan('analytics:capture-sample', ['--replay' => 66135342])->assertSuccessful();

        $written = array_map(fn ($path) => basename($path, '.json'), File::glob($this->temp.'/*.json'));
        foreach (AnalyticsQueryService::TABS as $tab) {
            $this->assertContains(app(AnalyticsSampleService::class)->tabFile($tab, null), $written);
        }
        $this->assertContains('replay', $written);
        $this->assertContains('performance', $written);
        $this->assertSame($committed, count(File::glob(resource_path('analytics-sample').'/*.json')), 'the committed sample is untouched');
    }
}
