<?php

namespace Tests\Feature\Backend;

use App\Models\ReplayFingerprint;
use App\Models\UploadAttempt;
use App\Models\UploadAttemptLog;
use App\Models\UploadedReplayData;
use App\Models\UploadedReplayPlayer;
use App\Models\UploaderSourceChange;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeReplayParser;

/**
 * The uploaders' replay ingestion, end to end: the bucket faked, the parser answering with its
 * own output for real replays (the Storm League game is the public Analytics sample, 66135342).
 */
class ReplayUploadTest extends FeatureTestCase
{
    use SeedFixtures;

    private const NIL = '00000000-0000-0000-0000-000000000000';

    private const FAILURE = 'Failure.  Try again or Contact zemill@heroesprofile.com';

    private FakeReplayParser $parser;

    protected function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.gcs.bucket' => 'replays-test']);
        Storage::fake('gcs');
        $this->parser = new FakeReplayParser;
        $this->setUploads(open: true);
    }

    private function setUploads(bool $open): void
    {
        DB::connection('heroesprofile_processing')->table('processing_check')->updateOrInsert(['process' => 'ReplayUploads'], ['running' => $open ? 1 : 0]);
        cache()->forget('uploads:open');
    }

    private function upload(string $content = 'replay bytes', string $source = 'desktop', string $path = '/api/external/v1/upload/heroesprofile/')
    {
        return $this->post($path.$source, [
            'file' => UploadedFile::fake()->createWithContent('2026-10-18 17.35.23 Battlefield of Eternity.StormReplay', $content),
            'version' => '3.0.5', 'compiled' => '1',
        ]);
    }

    public function test_a_new_storm_league_replay_is_stored_under_its_fingerprint_with_its_players(): void
    {
        $answer = $this->parser->fixture('storm-league.fingerprintOnly');
        $this->parser->answerWithFixture('storm-league.fingerprintOnly');

        $response = $this->upload()->assertOk();

        $replayID = ReplayFingerprint::where('fingerprint', $answer['fingerprint'])->value('replayID');
        $response->assertExactJson(['fingerprint' => $answer['fingerprint'], 'replayID' => $replayID, 'status' => 'Success']);

        // Kept under the fingerprint; the scratch copy the parser read is gone
        $this->assertSame([$answer['fingerprint'].'.StormReplay'], Storage::disk('gcs')->allFiles());

        $sent = $this->parser->requests[0];
        $this->assertSame(FakeReplayParser::URL, $sent['url']);
        $this->assertSame(['fingerprintOnly', 'replays-test', ''], [$sent['body']['parseType'], $sent['body']['bucket'], $sent['body']['fingerprint']]);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}\.StormReplay$/', $sent['body']['input']);
        $this->assertStringStartsWith('Bearer eyJ', $sent['authorization'], 'a Cloud Run identity token from the metadata server');

        $data = UploadedReplayData::where('replayID', $replayID)->firstOrFail();
        $this->assertSame(['desktop', '3.0.5', '1', '2026-10-18 21:35:23'], [$data->uploaded_source, $data->uploader_version, (string) $data->uploader_compile_checker, (string) $data->game_date]);

        $players = UploadedReplayPlayer::where('replayID', $replayID)->orderBy('battletag')->pluck('team', 'battletag')->all();
        $this->assertCount(10, $players);
        $this->assertSame(1, (int) $players['Zemill#1940']);

        $this->assertSame('Success', UploadAttempt::where('fingerprint', $answer['fingerprint'])->value('status'));
    }

    public function test_the_same_file_from_the_same_address_is_answered_without_parsing(): void
    {
        $this->parser->answerWithFixture('storm-league.fingerprintOnly');
        $first = $this->upload()->assertOk()->json();

        // No second parser answer is queued: a parse here would fail the test
        $this->upload()->assertOk()->assertExactJson($first);

        $this->assertCount(1, $this->parser->requests);
        $this->assertSame('Already submitted', UploadAttemptLog::latest('id')->value('message'));
    }

    public function test_a_replay_someone_else_sent_is_a_duplicate_and_a_primary_uploader_takes_over_its_source(): void
    {
        $this->parser->answerWithFixture('storm-league.fingerprintOnly')->answerWithFixture('storm-league.fingerprintOnly');

        $first = $this->upload('one copy', 'hotsapi')->assertOk()->json();
        $this->upload('another copy', 'electron')->assertOk()
            ->assertExactJson(['fingerprint' => $first['fingerprint'], 'replayID' => $first['replayID'], 'status' => 'Duplicate']);

        $this->assertSame('electron', UploadedReplayData::where('replayID', $first['replayID'])->value('uploaded_source'));
        $this->assertTrue(UploaderSourceChange::where('replayID', $first['replayID'])->where('source', 'electron')->exists());
        $this->assertCount(1, Storage::disk('gcs')->allFiles());
    }

    public function test_only_storm_league_keeps_its_players(): void
    {
        $this->parser->answerWithFixture('quick-match.fingerprintOnly');

        $replayID = $this->upload()->assertOk()->assertJson(['status' => 'Success'])->json('replayID');

        $this->assertFalse(UploadedReplayPlayer::where('replayID', $replayID)->exists());
    }

    public function test_a_replay_the_parser_cannot_read_fails_without_a_retry(): void
    {
        // The parser's own words for an unfinished game
        $this->parser->answer(200, 'Error parsing replay: Incomplete');

        $this->upload()->assertOk()->assertExactJson(['fingerprint' => self::NIL, 'status' => self::FAILURE]);

        $this->assertCount(1, $this->parser->requests);
        $this->assertSame([], Storage::disk('gcs')->allFiles(), 'the scratch copy is cleaned up');
        $this->assertSame('Failure', UploadAttempt::latest('id')->value('status'));
    }

    public function test_a_parser_outage_is_retried_once_and_a_failure_can_be_sent_again(): void
    {
        $this->parser->answer(503, 'Service Unavailable')->answer(503, 'Service Unavailable');

        $this->upload()->assertOk()->assertJson(['status' => self::FAILURE]);
        $this->assertCount(2, $this->parser->requests);

        // A failure doesn't settle the file: once the parser is back, the same upload goes through
        $this->parser->answerWithFixture('storm-league.fingerprintOnly');
        $this->upload()->assertOk()->assertJson(['status' => 'Success']);
    }

    public function test_forged_replays_are_rejected(): void
    {
        // Checked against the real clock (time()), not the test's frozen one
        $this->parser->answerWithFixture('storm-league.fingerprintOnly', ['game_date' => gmdate('Y-m-d\TH:i:s\Z', time() + 3 * 86400)]);
        $this->upload('dated ahead')->assertOk()->assertJson(['status' => 'Rejected', 'replayID' => 0]);

        $this->parser->answerWithFixture('storm-league.fingerprintOnly', ['game_events_bytes' => 1, 'tracker_events_bytes' => 1054]);
        $this->upload('empty events')->assertOk()->assertJson(['status' => 'Rejected']);

        $this->assertSame(['Game date is in the future', 'Too few game_events_bytes for the game\'s length'],
            UploadAttemptLog::where('status', 'Rejected')->orderBy('id')->pluck('message')->all());
        $this->assertFalse(ReplayFingerprint::where('fingerprint', $this->parser->fixture('storm-league.fingerprintOnly')['fingerprint'])->exists());
    }

    public function test_paused_uploads_tell_the_uploader_to_wait(): void
    {
        $this->setUploads(open: false);

        $this->upload()->assertStatus(429)->assertHeader('Retry-After', '3600')->assertExactJson(['success' => false, 'Error' => 'Uploads are paused']);
        $this->assertSame([], $this->parser->requests);
    }

    public function test_a_missing_or_oversized_file_is_refused_in_the_body(): void
    {
        $this->post('/api/external/v1/upload/heroesprofile/desktop')->assertOk()->assertExactJson(['success' => false, 'Error' => 'no file specified']);

        $this->post('/api/external/v1/upload/heroesprofile/desktop', ['file' => UploadedFile::fake()->create('big.StormReplay', 11 * 1024)])
            ->assertOk()->assertExactJson(['success' => false, 'Error' => 'File too large']);
    }

    public function test_every_upload_path_reaches_the_same_ingest(): void
    {
        $prefix = '/'.trim(config('api.path'), '/');

        // The legacy path only answers on the old API domain. Last, as the test client keeps that host afterwards
        $legacy = 'https://'.config('api.domain').'/api/upload/heroesprofile/';

        foreach ([$prefix.'/upload/heroesprofile/', $prefix.'/uploader/upload/heroesprofile/', $legacy] as $i => $path) {
            $this->parser->answer(200, 'Error parsing replay: Incomplete');
            $this->upload('file '.$i, 'desktop', $path)->assertOk()->assertJson(['status' => self::FAILURE]);
        }

        $this->assertCount(3, $this->parser->requests);
    }

    public function test_the_uploaders_fingerprint_and_parsed_checks(): void
    {
        $prefix = '/'.trim(config('api.path'), '/');
        $this->parser->answerWithFixture('storm-league.fingerprintOnly');
        $uploaded = $this->upload('x', 'hotsapi')->json();

        $this->get($prefix.'/uploader/replays/fingerprints/'.$uploaded['fingerprint'])->assertOk()
            ->assertExactJson(['exists' => true, 'replayID' => $uploaded['replayID']]);
        $this->assertSame('desktop', UploadedReplayData::where('replayID', $uploaded['replayID'])->value('uploaded_source'), 'checking promotes the source');

        $this->get($prefix.'/uploader/replays/fingerprints/'.self::NIL)->assertOk()->assertExactJson(['exists' => false, 'replayID' => null]);

        $this->get($prefix.'/uploader/replays/parsed?replayID='.$uploaded['replayID'])->assertOk()->assertContent('false');
        $this->get($prefix.'/uploader/replays/parsed?replayID='.self::REPLAY_ID)->assertOk()->assertContent('true');
        $this->get($prefix.'/uploader/replays/parsed?replayID=abc')->assertOk()->assertContent('false');
    }
}
