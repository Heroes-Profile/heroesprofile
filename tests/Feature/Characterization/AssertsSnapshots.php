<?php

namespace Tests\Feature\Characterization;

use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Responses recorded on one Laravel version and compared on the next.
 * A missing snapshot is written (and reported on stderr); UPDATE_SNAPSHOTS=1 rewrites them all.
 */
trait AssertsSnapshots
{
    protected function assertResponseMatchesSnapshot(string $name, TestResponse $response): void
    {
        $this->assertMatchesSnapshot($name, $this->describeResponse($response));
    }

    protected function assertMatchesSnapshot(string $name, array $actual): void
    {
        $path = __DIR__.'/snapshots/'.$this->snapshotFileName($name);
        $encoded = json_encode($actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)."\n";

        // In CI a missing snapshot is a forgotten commit, not a first run
        if (! file_exists($path) && getenv('CI')) {
            $this->fail("No snapshot for {$name}. Run the suite locally and commit tests/Feature/Characterization/snapshots/".$this->snapshotFileName($name));
        }

        if (getenv('UPDATE_SNAPSHOTS') || ! file_exists($path)) {
            file_put_contents($path, $encoded);
            fwrite(STDERR, "\nSnapshot written: {$name}");
        }

        // Pages' HTML is always recorded but only compared when asked (hp:test --html, for an upgrade), so a
        // markup change doesn't fail every run. Status, view, view data and JSON are always compared.
        $expected = file_get_contents($path);
        if (! getenv('SNAPSHOT_HTML')) {
            $expected = $this->withoutBody($expected);
            $encoded = $this->withoutBody($encoded);
        }

        $this->assertSame($expected, $encoded, "Response for {$name} no longer matches its snapshot");
    }

    private function withoutBody(string $snapshot): string
    {
        $decoded = json_decode($snapshot, true);
        if (! is_array($decoded) || ! array_key_exists('body', $decoded)) {
            return $snapshot;
        }
        unset($decoded['body']);

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)."\n";
    }

    private function snapshotFileName(string $name): string
    {
        return trim(preg_replace('/[^A-Za-z0-9]+/', '_', $name), '_').'.json';
    }

    private function describeResponse(TestResponse $response): array
    {
        $symfony = $response->baseResponse;
        $snapshot = [
            'status' => $symfony->getStatusCode(),
            'content_type' => strtok((string) $symfony->headers->get('Content-Type'), ';'),
        ];

        if ($symfony->isRedirection()) {
            $snapshot['location'] = $this->normalize((string) $symfony->headers->get('Location'));

            return $snapshot;
        }

        if (isset($symfony->original) && $symfony->original instanceof View) {
            $snapshot['view'] = $symfony->original->name();
            $snapshot['view_data_keys'] = collect($symfony->original->getData())->keys()->sort()->values()->all();
        }

        $content = $symfony instanceof StreamedResponse ? $response->streamedContent() : (string) $symfony->getContent();
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || $content === 'null')) {
            // APP_DEBUG error bodies carry a stack trace that changes with how PHPUnit nested the run
            if (is_array($decoded) && isset($decoded['exception'], $decoded['trace'])) {
                unset($decoded['exception'], $decoded['file'], $decoded['line'], $decoded['trace']);
            }
            $snapshot['json'] = $this->normalizeJson($decoded);
        } else {
            $snapshot['body'] = explode("\n", $this->normalize($content));
        }

        return $snapshot;
    }

    private function normalizeJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->normalizeJson($item), $value);
        }

        return is_string($value) ? $this->normalize($value) : $value;
    }

    /** Per-request noise: tokens, nonces and cache-busting hashes. */
    private function normalize(string $content): string
    {
        $content = str_replace("\r\n", "\n", $content);
        $content = preg_replace('/(name="csrf-token" content=")[^"]+/', '$1[csrf]', $content);
        $content = preg_replace('/(name="_token" value=")[^"]+/', '$1[csrf]', $content);
        $content = preg_replace('/(nonce=")[^"]+/', '$1[nonce]', $content);
        $content = preg_replace('/([?&]v=|[?&]id=)[0-9a-f]{8,}/', '$1[hash]', $content);
        $content = preg_replace('/\b[a-zA-Z0-9]{40}\b/', '[token40]', $content);

        return rtrim(preg_replace('/[ \t]+$/m', '', $content));
    }
}
