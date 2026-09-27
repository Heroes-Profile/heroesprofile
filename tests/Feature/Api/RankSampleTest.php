<?php

namespace Tests\Feature\Api;

use App\Services\Api\RankSampleService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** POST /api/external/v1/rank-samples - the uploader's loading-screen samples, into (fake) private storage. */
class RankSampleTest extends TestCase
{
    private const URL = '/api/external/v1/rank-samples';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'filesystems.disks.'.RankSampleService::DISK.'.bucket' => 'test-rank-samples',
            'api.rank_samples.collect' => true,
        ]);
        Storage::fake(RankSampleService::DISK);
    }

    private function png(string $name): UploadedFile
    {
        // A real 1x1 PNG, so the signature check sees one.
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    private function meta(): string
    {
        return json_encode(['gameMode' => 'StormLeague', 'players' => [['name' => 'Someone', 'battletag' => 1234]]]);
    }

    public function test_it_stores_the_frames_and_meta_in_a_folder_of_their_own(): void
    {
        $this->post(self::URL, [
            'meta' => $this->meta(),
            'frames' => [$this->png('frame-0-left.png'), $this->png('frame-0-right.png')],
        ])->assertOk()->assertJson(['stored' => true, 'collect' => true]);

        $files = Storage::disk(RankSampleService::DISK)->allFiles();
        $this->assertCount(3, $files);
        $folder = dirname($files[0]);
        $this->assertMatchesRegularExpression('#^\d{4}-\d{2}-\d{2}/[0-9a-f-]{36}$#', $folder);
        Storage::disk(RankSampleService::DISK)->assertExists([
            "{$folder}/frame-0-left.png", "{$folder}/frame-0-right.png", "{$folder}/meta.json",
        ]);

        $stored = json_decode(Storage::disk(RankSampleService::DISK)->get("{$folder}/meta.json"), true);
        $this->assertSame('StormLeague', $stored['meta']['gameMode']);
        $this->assertArrayHasKey('reporter', $stored);
        $this->assertStringNotContainsString('127.0.0.1', $stored['reporter']);
    }

    public function test_unexpected_file_names_are_replaced(): void
    {
        $this->post(self::URL, ['meta' => $this->meta(), 'frames' => [$this->png('../../evil.png')]])->assertOk();

        $files = Storage::disk(RankSampleService::DISK)->allFiles();
        $this->assertContains('frame-0.png', array_map('basename', $files));
    }

    public function test_when_collection_is_off_nothing_is_stored_and_the_uploader_is_told_to_stop(): void
    {
        config(['api.rank_samples.collect' => false]);

        $this->post(self::URL, ['meta' => $this->meta(), 'frames' => [$this->png('frame-0-left.png')]])
            ->assertOk()->assertJson(['stored' => false, 'collect' => false]);

        $this->assertEmpty(Storage::disk(RankSampleService::DISK)->allFiles());
    }

    public function test_it_is_not_collecting_without_a_bucket(): void
    {
        config(['filesystems.disks.'.RankSampleService::DISK.'.bucket' => null]);

        $this->post(self::URL, ['meta' => $this->meta(), 'frames' => [$this->png('frame-0-left.png')]])
            ->assertJson(['collect' => false]);
    }

    public function test_it_rejects_what_is_not_a_sample(): void
    {
        // Not a PNG
        $this->post(self::URL, ['meta' => $this->meta(), 'frames' => [UploadedFile::fake()->createWithContent('frame-0-left.png', 'not an image')]])
            ->assertStatus(422);
        // No frames
        $this->post(self::URL, ['meta' => $this->meta()])->assertStatus(422);
        // Too many frames
        $frames = array_map(fn ($i) => $this->png("frame-{$i}-left.png"), range(0, 12));
        $this->post(self::URL, ['meta' => $this->meta(), 'frames' => $frames])->assertStatus(422);
        // Meta that isn't a JSON object
        $this->post(self::URL, ['meta' => 'nope', 'frames' => [$this->png('frame-0-left.png')]])->assertStatus(422);

        $this->assertEmpty(Storage::disk(RankSampleService::DISK)->allFiles());
    }
}
