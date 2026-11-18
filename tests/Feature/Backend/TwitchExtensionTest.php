<?php

namespace Tests\Feature\Backend;

use App\Models\Api\TwitchChannel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\Feature\FeatureTestCase;
use Tests\Support\SignsInApiAccounts;
use Tests\Support\SimulatesCloudTasks;

/**
 * The Twitch extension's backend: the uploader's snapshots turned into viewer updates and pushed
 * through Twitch's Helix API (faked with Http::fake), the delayed path through Cloud Tasks, the
 * broadcaster's view, the portal setup with Twitch sign-in faked, admin controls and the directory.
 *
 * The game is a real one: the Storm League match in tests/Fixtures/replay-parser, its lobby, heroes
 * and talents as the parser read them.
 */
class TwitchExtensionTest extends FeatureTestCase
{
    use SignsInApiAccounts;
    use SimulatesCloudTasks;

    private const CHANNEL_ID = '41234567';

    // Twitch issues 32-byte extension secrets
    private const SECRET = 'twitch-extension-test-secret-32b';

    private TwitchChannel $channel;

    private string $uploaderKey;

    /** @var array<int, Request> */
    private array $helix = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Real time: the push dedupe and the trial run on now(), the extension JWT on time()
        $this->travelBack();
        $this->setUpCloudTasks();
        config([
            'twitch.client_id' => 'twitch-client-test', 'twitch.client_secret' => 'twitch-secret-test',
            'twitch.extension_secrets' => [base64_encode(self::SECRET)], 'twitch.owner_user_id' => '900',
            'twitch.push_handler_url' => 'https://worker.test/api/twitch/v1/internal/push',
            'twitch.public_enabled' => true,
        ]);

        Http::fake([
            'https://api.twitch.tv/helix/extensions/pubsub' => Http::response(null, 204),
            'https://api.twitch.tv/helix/extensions/configurations' => Http::response(null, 204),
            'https://id.twitch.tv/oauth2/token' => Http::response(['access_token' => 'app-token', 'expires_in' => 5000000, 'token_type' => 'bearer']),
            'https://api.twitch.tv/helix/extensions/live*' => Http::response([
                'data' => [['broadcaster_id' => self::CHANNEL_ID, 'broadcaster_name' => 'Zemill', 'game_name' => 'Heroes of the Storm', 'game_id' => '32959', 'title' => 'Storm League']],
                'pagination' => '',
            ]),
            'https://api.twitch.tv/helix/streams*' => Http::response(['data' => [[
                'id' => '40001', 'user_id' => self::CHANNEL_ID, 'user_login' => 'zemill', 'user_name' => 'Zemill',
                'game_id' => '32959', 'game_name' => 'Heroes of the Storm', 'type' => 'live', 'title' => 'Storm League grind',
                'viewer_count' => 42, 'started_at' => '2026-11-18T18:00:00Z', 'language' => 'en',
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/previews-ttv/live_user_zemill-{width}x{height}.jpg',
            ]], 'pagination' => []]),
        ]);

        // The developer account holds a paid plan, which includes the extension
        $this->channel = TwitchChannel::forceCreate([
            'user_id' => $this->apiAccount('developer')->id, 'twitch_user_id' => self::CHANNEL_ID,
            'twitch_login' => 'zemill', 'twitch_display_name' => 'Zemill',
            'blizz_id' => 67280, 'region' => 1, 'battletag' => 'Zemill#1940', 'delay_seconds' => 0, 'show_stats' => true,
        ]);
        $this->uploaderKey = $this->channel->rotateUploaderKey();

        // The game's players as our battletags hold them (blizz ids from the parse); the seed predates their tags
        foreach ($this->parsedGame()['players'] as $player) {
            DB::connection('heroesprofile')->table('battletags')->insertOrIgnore([
                'blizz_id' => $player['blizz_id'], 'battletag' => $player['battletag_name'].'#'.$player['battletag_id'],
                'region' => 1, 'account_level' => $player['account_level'], 'latest_game' => '2026-10-18 21:35:23',
            ]);
        }
    }

    private function parsedGame(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../../Fixtures/replay-parser/storm-league.default.json'), true);
    }

    private function helixCalls(string $path): array
    {
        return collect(Http::recorded())->map(fn ($pair) => $pair[0])
            ->filter(fn (Request $request) => str_starts_with($request->url(), 'https://api.twitch.tv/helix/'.$path))->values()->all();
    }

    /** The real game's lobby as the uploader reads it, with heroes and talents picked so far. */
    private function snapshot(int $seq, string $phase = 'in_game', int $talents = 3): array
    {
        $parsed = $this->parsedGame();

        return [
            'game_id' => 'a1b2c3d4-0000-4000-8000-66135342', 'seq' => $seq, 'phase' => $phase,
            'game_mode' => 'StormLeague', 'map' => $parsed['map'], 'game_version' => $parsed['version'],
            'players' => array_map(fn ($p) => [
                'name' => $p['battletag_name'], 'battletag' => $p['battletag_id'], 'region' => 1, 'team' => $p['team'],
                'hero' => $p['hero'], 'talents' => array_slice($p['talents'], 0, $talents),
            ], $parsed['players']),
        ];
    }

    private function sendSnapshot(array $snapshot, ?string $key = null)
    {
        return $this->postJson('/api/twitch/v1/uploader/snapshot', $snapshot, ['X-HP-Twitch-Key' => $key ?? $this->uploaderKey]);
    }

    public function test_the_uploader_key_identifies_the_channel(): void
    {
        $this->getJson('/api/twitch/v1/uploader/whoami', ['X-HP-Twitch-Key' => $this->uploaderKey])->assertOk()
            ->assertJson(['twitch_login' => 'zemill', 'battletag' => 'Zemill#1940', 'player_linked' => true, 'delay_seconds' => 0])
            ->assertJsonPath('entitlement.active', true);

        $this->getJson('/api/twitch/v1/uploader/whoami', ['X-HP-Twitch-Key' => 'not-a-key'])->assertUnauthorized()->assertJson(['error' => 'invalid_key']);
    }

    public function test_a_snapshot_reaches_viewers_through_pubsub_and_the_configuration_segment(): void
    {
        $this->sendSnapshot($this->snapshot(1))->assertOk()->assertExactJson(['active' => true, 'accepted' => true, 'seq' => 1]);

        [$pubsub] = $this->helixCalls('extensions/pubsub');
        [$config] = $this->helixCalls('extensions/configurations');
        $this->assertSame(['twitch-client-test'], $pubsub->header('Client-Id'));
        $this->assertSame(['broadcast'], $pubsub['target']);
        $this->assertSame(self::CHANNEL_ID, $pubsub['broadcaster_id']);
        $this->assertSame(['developer', '1', $pubsub['message']], [$config['segment'], $config['version'], $config['content']]);

        // Signed as the extension's own backend, for this channel
        $token = substr($pubsub->header('Authorization')[0], strlen('Bearer '));
        $claims = (array) JWT::decode($token, new Key(self::SECRET, 'HS256'));
        $this->assertSame(['external', self::CHANNEL_ID, '900'], [$claims['role'], $claims['channel_id'], $claims['user_id']]);

        // The payload: the real lobby resolved to hero and talent ids, the streamer's side named
        $payload = json_decode($pubsub['message'], true);
        $this->assertSame(['a1b2c3d4-0000-4000-8000-66135342', 1, 'in_game', 'StormLeague', 'Battlefield of Eternity', 'Zemill', 1],
            [$payload['g'], $payload['s'], $payload['p'], $payload['t'], $payload['m'], $payload['c'], $payload['st']]);
        $this->assertCount(10, $payload['pl']);
        $zemill = collect($payload['pl'])->firstWhere(1, 'Zemill');
        $this->assertSame(67280, $zemill[2], 'blizz id from our battletags, not the client');
        $this->assertNotNull($zemill[4], 'Auriel resolved to a hero id');
        $this->assertCount(3, array_filter($zemill[5]), 'three talents picked so far, all resolved');
        $this->assertLessThanOrEqual(config('twitch.max_payload_bytes'), strlen($pubsub['message']));

        $this->assertNotNull($this->channel->fresh()->trial_started_at, 'the first game starts the trial');
    }

    public function test_an_old_or_repeated_snapshot_is_not_sent_again(): void
    {
        $this->sendSnapshot($this->snapshot(5))->assertOk();
        $this->sendSnapshot($this->snapshot(5))->assertOk()->assertJson(['accepted' => false, 'seq' => 5]);
        $this->sendSnapshot($this->snapshot(4))->assertOk()->assertJson(['accepted' => false]);
        $this->sendSnapshot($this->snapshot(6, talents: 4))->assertOk()->assertJson(['accepted' => true, 'seq' => 6]);

        $this->assertCount(2, $this->helixCalls('extensions/pubsub'));
    }

    public function test_a_delayed_channel_is_pushed_by_cloud_task_when_the_delay_is_up(): void
    {
        $this->channel->forceFill(['delay_seconds' => 120])->save();

        $this->sendSnapshot($this->snapshot(1))->assertOk()->assertJson(['accepted' => true]);

        // Nothing leaves before the delay
        $this->assertSame([], $this->helixCalls('extensions/pubsub'));
        $task = $this->cloudTasks->queued[0];
        $this->assertSame(['https://worker.test/api/twitch/v1/internal/push', 'twitch-push'], [$task['url'], $task['queue']]);
        $this->assertSame(now()->addSeconds(120)->getTimestamp(), $task['not_before']->getTimestamp());

        $this->runQueuedTasks()[0]->assertOk()->assertExactJson(['sent' => true]);
        $this->assertCount(1, $this->helixCalls('extensions/pubsub'));

        // A task that fires late, after a newer update went out, is dropped
        $this->postJson('/api/twitch/v1/internal/push', $task['body'], $this->taskHeaders())->assertOk()->assertExactJson(['sent' => false]);
    }

    public function test_the_push_worker_only_answers_cloud_tasks(): void
    {
        $this->postJson('/api/twitch/v1/internal/push', ['channel_id' => self::CHANNEL_ID, 'game_id' => 'x', 'seq' => 1, 'payload' => '{}'])
            ->assertForbidden();
        $this->assertSame([], $this->helixCalls('extensions/pubsub'));
    }

    public function test_a_delayed_update_is_dropped_rather_than_sent_early_when_cloud_tasks_is_down(): void
    {
        $this->channel->forceFill(['delay_seconds' => 60])->save();
        $this->cloudTasks->failNext = new \RuntimeException('Cloud Tasks unavailable');

        $this->sendSnapshot($this->snapshot(1))->assertOk();

        $this->assertSame([], $this->helixCalls('extensions/pubsub'));
    }

    public function test_a_channel_without_access_is_told_once_and_viewers_see_it_is_off(): void
    {
        $account = $this->apiAccount('developer');
        $account->forceFill(['suspended_at' => now(), 'suspension_type' => 'suspension', 'suspension_reason' => 'x'])->save();

        $this->sendSnapshot($this->snapshot(1))->assertStatus(402)->assertJson(['active' => false]);
        $this->sendSnapshot($this->snapshot(2))->assertStatus(402);

        $pubsub = $this->helixCalls('extensions/pubsub');
        $this->assertCount(1, $pubsub, 'announced once per lapse');
        $this->assertSame('inactive', $this->helixCalls('extensions/configurations')[0]['version']);
    }

    public function test_snapshot_validation(): void
    {
        $snapshot = $this->snapshot(1);
        $snapshot['game_id'] = 'bad id!';
        $snapshot['players'][0]['team'] = 2;

        $this->sendSnapshot($snapshot)->assertUnprocessable()->assertJsonValidationErrors(['game_id', 'players.0.team']);
    }

    private function extensionToken(string $role, string $channelId = self::CHANNEL_ID): string
    {
        return JWT::encode(['exp' => time() + 300, 'channel_id' => $channelId, 'role' => $role, 'user_id' => 'U1', 'opaque_user_id' => 'U1'], self::SECRET, 'HS256');
    }

    public function test_the_broadcaster_sees_their_setup_and_the_latest_game_undelayed(): void
    {
        $this->channel->forceFill(['delay_seconds' => 300])->save();
        $this->sendSnapshot($this->snapshot(3))->assertOk();

        $this->getJson('/api/twitch/v1/broadcaster/status', ['Authorization' => 'Extension '.$this->extensionToken('broadcaster')])->assertOk()
            ->assertJson(['linked' => true, 'twitch_login' => 'zemill', 'has_key' => true, 'delay_seconds' => 300])
            ->assertJsonPath('latest.s', 3);

        $this->getJson('/api/twitch/v1/broadcaster/status', ['Authorization' => 'Extension '.$this->extensionToken('viewer')])->assertForbidden();
        $this->getJson('/api/twitch/v1/broadcaster/status', ['Authorization' => 'Extension forged.token.here'])->assertUnauthorized();
        $this->getJson('/api/twitch/v1/broadcaster/status', ['Authorization' => 'Extension '.$this->extensionToken('broadcaster', '1')])->assertOk()->assertExactJson(['linked' => false]);
    }

    private function fakeTwitchSignIn(string $id, string $login): void
    {
        $user = (new SocialiteUser)->setRaw([])->map(['id' => $id, 'nickname' => $login, 'name' => ucfirst($login)]);
        $provider = Mockery::mock();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('twitch')->andReturn($provider);
    }

    public function test_connecting_a_twitch_channel_to_an_api_account(): void
    {
        $account = $this->apiAccount('basic');
        $this->fakeTwitchSignIn('55555555', 'basicstreamer');

        $this->asApiAccount($account)->get('/Api/Twitch/Callback?code=abc&state=xyz')
            ->assertRedirect('/Api/Account#twitch')->assertSessionHas('twitch_status', 'Twitch channel connected.');

        $channel = TwitchChannel::where('twitch_user_id', '55555555')->firstOrFail();
        $this->assertSame([$account->id, 'basicstreamer', 'Basicstreamer'], [$channel->user_id, $channel->twitch_login, $channel->twitch_display_name]);
        $this->assertTrue((bool) $account->fresh()->twitch_extension);
    }

    public function test_a_channel_already_connected_elsewhere_is_refused(): void
    {
        $this->fakeTwitchSignIn(self::CHANNEL_ID, 'zemill');

        $this->asApiAccount('basic')->get('/Api/Twitch/Callback?code=abc&state=xyz')->assertRedirect('/Api/Account#twitch')->assertSessionHasErrors('twitch');

        $this->assertSame($this->apiAccount('developer')->id, $this->channel->fresh()->user_id);
    }

    public function test_the_streamer_manages_key_settings_listing_and_unlink(): void
    {
        $this->asApiAccount('developer');

        $rotated = $this->postJson('/api/v1/account/twitch/key')->assertOk()->json();
        $this->assertSame(substr($rotated['key'], -4), $rotated['channel']['key_last4']);
        $this->getJson('/api/twitch/v1/uploader/whoami', ['X-HP-Twitch-Key' => $this->uploaderKey])->assertUnauthorized();
        $this->getJson('/api/twitch/v1/uploader/whoami', ['X-HP-Twitch-Key' => $rotated['key']])->assertOk();

        $this->postJson('/api/v1/account/twitch/settings', ['delay_seconds' => 90, 'show_stats' => false])->assertOk()
            ->assertJsonPath('channel.delay_seconds', 90)->assertJsonPath('channel.show_stats', false);
        $this->postJson('/api/v1/account/twitch/settings', ['delay_seconds' => 901, 'show_stats' => true])->assertUnprocessable();

        $this->postJson('/api/v1/account/twitch/listing', ['listing_opt_in' => true, 'accept_terms' => false])->assertUnprocessable();
        $this->postJson('/api/v1/account/twitch/listing', ['listing_opt_in' => true, 'accept_terms' => true])->assertOk()
            ->assertJsonPath('channel.listing_opt_in', true)->assertJsonPath('channel.listing_terms_current', true);

        $this->postJson('/api/v1/account/twitch/unlink')->assertOk()->assertExactJson(['channel' => null]);
        $channel = $this->channel->fresh();
        $this->assertSame([null, null, false], [$channel->user_id, $channel->uploader_key_hash, (bool) $channel->listing_opt_in]);
        $this->getJson('/api/twitch/v1/uploader/whoami', ['X-HP-Twitch-Key' => $rotated['key']])->assertUnauthorized();
    }

    public function test_admins_hide_suspend_and_comp_a_channel(): void
    {
        $this->asApiAccount('admin');
        $url = '/api/v1/admin/twitch/'.$this->channel->id;

        $this->postJson($url.'/listing', ['hidden' => true])->assertUnprocessable();
        $this->postJson($url.'/listing', ['hidden' => true, 'reason' => 'Off-topic stream'])->assertOk();
        $this->postJson($url.'/suspend', ['suspended' => true, 'reason' => 'Abuse'])->assertOk();
        $this->postJson($url.'/comp', ['comped_until' => now()->addMonth()->toDateString()])->assertOk();

        $channel = $this->channel->fresh();
        $this->assertSame(['Off-topic stream', 'Abuse'], [$channel->listing_hidden_reason, $channel->suspension_reason]);
        $this->assertSame(now()->addMonth()->toDateString(), $channel->comped_until->toDateString());

        // A suspended channel's snapshots are refused
        $this->sendSnapshot($this->snapshot(1))->assertStatus(402);

        $this->asApiAccount('developer')->postJson($url.'/suspend', ['suspended' => false])->assertForbidden();
    }

    public function test_the_directory_lists_live_hots_streamers_from_helix(): void
    {
        $this->channel->forceFill([
            'listing_opt_in' => true, 'listing_terms_version' => config('twitch.listing_terms_version'), 'uploader_last_seen_at' => now(),
        ])->save();
        Cache::forget('twitch_directory');

        $this->get('/Twitch')->assertOk()->assertViewHas('directory', function ($directory) {
            return $directory['offline'] === [] && $directory['live'][0]['login'] === 'zemill'
                && $directory['live'][0]['viewer_count'] === 42
                && $directory['live'][0]['thumbnail_url'] === 'https://static-cdn.jtvnw.net/previews-ttv/live_user_zemill-440x248.jpg';
        });

        $streams = $this->helixCalls('streams')[0];
        $this->assertSame(['Bearer app-token'], $streams->header('Authorization'), 'app token from the client-credentials grant');
        $this->assertStringContainsString('user_id='.self::CHANNEL_ID, $streams->url());
    }
}
