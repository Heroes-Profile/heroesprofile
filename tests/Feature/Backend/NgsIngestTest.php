<?php

namespace Tests\Feature\Backend;

use App\Models\Api\ApiAccount;
use App\Services\Api\NgsReplayIngestService;
use App\Services\Api\ReplayParserClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Characterization\AssertsSnapshots;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeReplayParser;

/**
 * NGS game upload and delete on the public API: the replay fetched from NGS's bucket (faked), copied
 * into ours (faked), parsed by the parser (its own output for real replays), and written to the NGS schema.
 */
class NgsIngestTest extends FeatureTestCase
{
    use AssertsSnapshots;
    use SeedFixtures;

    private const REPLAY_URL = 'https://s3.amazonaws.com/ngs-replay-storage/season-20/test-match.StormReplay';

    private FakeReplayParser $parser;

    private MockHandler $downloads;

    /** Each URL fetched from NGS. */
    private array $fetched = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.gcs-ngs.bucket' => 'ngs-test']);
        Storage::fake('gcs-ngs');
        $this->parser = new FakeReplayParser;

        $this->downloads = new MockHandler;
        $stack = HandlerStack::create($this->downloads);
        $stack->push(Middleware::mapRequest(function ($request) {
            $this->fetched[] = (string) $request->getUri();

            return $request;
        }));
        $this->app->bind(NgsReplayIngestService::class, fn () => new NgsReplayIngestService(
            app(ReplayParserClient::class),
            new Client(['handler' => $stack]),
        ));

        // The developer account holds both NGS grants here
        ApiAccount::where('email', 'developer@heroesprofile.test')->update(['n_approved' => 1, 'n_upload_approved' => 1]);
    }

    private static function key(string $account): string
    {
        return str_pad($account.'_local_api_key_', 60, '0');
    }

    private function input(array $overrides = []): array
    {
        return array_merge([
            'replay_url' => self::REPLAY_URL, 'mode' => 'prod', 'season' => 20, 'round' => '3', 'game' => '1',
            'team_one_name' => 'Team One', 'team_two_name' => 'Team Two',
            'team_one_player' => 'Zemill#1940', 'team_two_player' => 'Drk#11533',
            'team_one_map_ban_1' => 'Cursed Hollow', 'team_one_map_ban_2' => 'Dragon Shire',
            'team_two_map_ban_1' => 'Sky Temple', 'team_two_map_ban_2' => 'Towers of Doom',
        ], $overrides);
    }

    private function uploadGame(array $input, string $account = 'developer')
    {
        return $this->postJson('/api/external/v1/ngs/games/upload?api_token='.self::key($account), $input);
    }

    public function test_the_replay_url_must_be_in_the_ngs_bucket(): void
    {
        $this->uploadGame($this->input(['replay_url' => 'https://s3.amazonaws.com/someone-elses-bucket/x.StormReplay']))
            ->assertUnprocessable()->assertJsonPath('error.errors', ['The replay URL must point at the NGS replay bucket.']);
        $this->uploadGame($this->input(['replay_url' => 'http://ngs-replay-storage.s3.amazonaws.com/x.StormReplay']))->assertUnprocessable();

        $this->assertSame([], $this->fetched);
    }

    public function test_uploading_needs_both_ngs_grants(): void
    {
        $this->uploadGame($this->input(), 'basic')->assertForbidden()->assertJsonPath('error.code', 'ngs_access_required');
        $this->postJson('/api/external/v1/ngs/games/upload', $this->input())->assertUnauthorized();
    }

    public function test_a_test_mode_account_gets_the_fixture_and_nothing_is_fetched(): void
    {
        ApiAccount::where('email', 'developer@heroesprofile.test')->update(['test_mode' => 1]);

        $this->uploadGame($this->input())->assertOk();

        $this->assertSame([], $this->fetched);
        $this->assertSame([], $this->parser->requests);
    }

    public function test_a_replay_ngs_cannot_serve_is_refused(): void
    {
        $this->downloads->append(new Response(403, [], 'AccessDenied'));

        $this->uploadGame($this->input())->assertUnprocessable()
            ->assertExactJson(['error' => ['code' => 'ngs_upload_failed', 'message' => 'Could not fetch the replay from that URL']]);

        $this->assertSame([self::REPLAY_URL], $this->fetched);
        $this->assertSame([], $this->parser->requests);
    }

    public function test_a_replay_the_parser_cannot_read_is_incomplete(): void
    {
        $this->downloads->append(new Response(200, [], 'replay bytes'));
        $this->parser->answer(200, 'Error parsing replay: Incomplete');

        $this->uploadGame($this->input())->assertUnprocessable()->assertJsonPath('error.message', 'Replay Incomplete');

        // Copied into our bucket under the same key, and parsed from there with a full parse
        $this->assertSame('replay bytes', Storage::disk('gcs-ngs')->get('season-20/test-match.StormReplay'));
        $this->assertSame(['season-20/test-match.StormReplay', 'ngs-test', 'default'], [
            $this->parser->requests[0]['body']['input'], $this->parser->requests[0]['body']['bucket'], $this->parser->requests[0]['body']['parseType'],
        ]);
    }

    public function test_a_ranked_game_is_not_an_ngs_game(): void
    {
        // A real Storm League game, fully parsed
        $this->downloads->append(new Response(200, [], 'replay bytes'));
        $this->parser->answerWithFixture('storm-league.default');

        $this->uploadGame($this->input())->assertUnprocessable()->assertJsonPath('error.message', 'Game mode is not custom.  Invalid replay sent');

        $this->assertFalse(DB::connection('heroesprofile_ngs')->table('replay')->where('season', '20')->exists());
    }

    public function test_an_admin_deletes_a_game_and_everything_hanging_off_it(): void
    {
        $ngs = DB::connection('heroesprofile_ngs');
        $tables = ['replay_draft_order', 'replay_bans', 'scores', 'talents', 'player', 'replay'];
        $this->assertTrue($ngs->table('replay')->where('replayID', self::NGS_REPLAY_ID)->exists());

        $this->deleteJson('/api/external/v1/ngs/games/'.self::NGS_REPLAY_ID.'?mode=prod&api_token='.self::key('admin'))
            ->assertOk()->assertExactJson(['deleted' => self::NGS_REPLAY_ID]);

        foreach ($tables as $table) {
            $this->assertFalse($ngs->table($table)->where('replayID', self::NGS_REPLAY_ID)->exists(), $table);
        }
    }

    public function test_deleting_is_hidden_from_non_admins_and_refused_in_test_mode(): void
    {
        $this->deleteJson('/api/external/v1/ngs/games/'.self::NGS_REPLAY_ID.'?mode=prod&api_token='.self::key('developer'))
            ->assertNotFound()->assertJsonPath('error.code', 'not_found');

        ApiAccount::where('email', 'admin@heroesprofile.test')->update(['test_mode' => 1]);
        $this->deleteJson('/api/external/v1/ngs/games/'.self::NGS_REPLAY_ID.'?mode=prod&api_token='.self::key('admin'))
            ->assertForbidden()->assertJsonPath('error.code', 'test_mode');

        $this->assertTrue(DB::connection('heroesprofile_ngs')->table('replay')->where('replayID', self::NGS_REPLAY_ID)->exists());
    }

    public function test_a_real_ngs_match_is_written_to_the_ngs_schema(): void
    {
        // NGS game 1, 2026-11-17, Alterac Pass: its replay parsed by the real parser
        $this->downloads->append(new Response(200, [], 'replay bytes'));
        $this->parser->answerWithFixture('ngs-custom.default');
        $input = $this->input([
            'team_one_name' => 'I Need My Caster Role', 'team_two_name' => 'Tricky Gooses',
            'team_one_player' => 'Duck#1103', 'team_two_player' => 'DHMcGibblets#1121',
        ]);

        $response = $this->uploadGame($input)->assertOk();
        $replayID = (int) basename($response->json('url'));
        $this->assertGreaterThan(self::NGS_REPLAY_ID, $replayID);
        $this->assertCount(5, $response->json('I Need My Caster Role'));
        $this->assertSame('https://www.heroesprofile.com/Esports/NGS/Player/Duck/6981786', $response->json('I Need My Caster Role')['Duck#1103 Profile']);

        $ngs = DB::connection('heroesprofile_ngs');
        $replay = (array) $ngs->table('replay')->where('replayID', $replayID)->first();
        $this->assertSame(['20', 'I Need My Caster Role', 'Tricky Gooses', '2026-11-17 05:00:05'], [$replay['season'], $replay['team_0_name'], $replay['team_1_name'], $replay['game_date']]);

        // Ids are auto-increment, so rows are keyed by battletag rather than by id
        $battletags = $ngs->table('battletags')->pluck('battletag', 'player_id');
        $teams = $ngs->table('teams')->pluck('team_name', 'team_id');
        $byPlayer = fn (string $table) => $ngs->table($table)->where('replayID', $replayID)->get()
            ->mapWithKeys(fn ($row) => [$battletags[$row->battletag] => array_diff_key((array) $row, ['replayID' => 1, 'battletag' => 1, 'player_id' => 1, 'team_name' => 1])])
            ->sortKeys()->all();
        $players = $ngs->table('player')->where('replayID', $replayID)->get()->mapWithKeys(fn ($row) => [$battletags[$row->battletag] => $teams[$row->team_name]])->sortKeys()->all();

        $this->assertMatchesSnapshot('ngs ingest real match', [
            'response' => array_diff_key($response->json(), ['url' => 1]),
            'replay' => array_diff_key($replay, ['replayID' => 1, 'date_added' => 1]),
            'player teams' => $players,
            'player' => $byPlayer('player'),
            'talents' => $byPlayer('talents'),
            'scores' => $byPlayer('scores'),
            'draft' => $ngs->table('replay_draft_order')->where('replayID', $replayID)->orderBy('pick_number')->get(['pick_number', 'type', 'player_slot', 'hero'])->map(fn ($row) => (array) $row)->all(),
            'bans' => $ngs->table('replay_bans')->where('replayID', $replayID)->orderBy('ban_id')->get(['team', 'hero'])->map(fn ($row) => (array) $row)->all(),
        ]);

        $log = DB::connection('heroesprofile_logs')->table('ngs_replays_sent')->where('replayID', $replayID)->first();
        $this->assertSame([self::REPLAY_URL, 'prod'], [$log->replay_url, $log->dev_prod]);
        $this->assertStringNotContainsString(self::key('developer'), json_encode($log), 'the key itself is never logged');
    }

    public function test_a_named_player_who_was_not_in_the_game_is_refused(): void
    {
        $this->downloads->append(new Response(200, [], 'replay bytes'));
        $this->parser->answerWithFixture('ngs-custom.default');

        $this->uploadGame($this->input(['team_one_player' => 'Duck#1103', 'team_two_player' => 'Nobody#9999']))
            ->assertUnprocessable()->assertJsonPath('error.message', 'Invalid parameter. A player provided was not in the game');
    }
}
