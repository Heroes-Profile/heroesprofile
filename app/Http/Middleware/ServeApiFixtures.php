<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use App\Support\CsvResponse;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves a canned response instead of live data while an account has not migrated
 * or has test mode switched on, so a customer can build against the new API
 * without pulling live data from both sites and without spending quota.
 *
 * Runs in front of the endpoint controller rather than inside it: no database
 * work happens at all, and a new endpoint cannot forget the gate.
 *
 * Takes the registry key, same as the quota middleware:
 * ->middleware('api.fixtures:heroes_stats')
 */
class ServeApiFixtures
{
    public const HEADER = 'X-HP-Data-Source';

    public const DIRECTORY = 'api-fixtures';

    /**
     * Endpoints that answer with a file rather than JSON. The fixture is the file
     * itself, served as a download so a test-mode caller exercises the same code
     * path a live one does.
     */
    private const BINARY_EXTENSIONS = ['StormReplay'];

    public function handle(Request $request, Closure $next, string $endpoint): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null || ! $context->servesFixtures()) {
            return $next($request);
        }

        if ($binary = $this->binaryFixture($endpoint)) {
            return response()
                ->download($binary, basename($binary))
                ->withHeaders([self::HEADER => 'fixture']);
        }

        $fixture = $this->read($endpoint);

        // Never fall through to live data. A missing or unreadable fixture is the
        // one failure that would silently defeat the gate, so it fails the call.
        if ($fixture === null) {
            report(new RuntimeException("Missing or invalid API fixture for endpoint [{$endpoint}]."));

            return response()->json([
                'error' => [
                    'code' => 'fixture_unavailable',
                    'message' => 'Example data for this endpoint is unavailable. Please contact support.',
                    'endpoint' => $endpoint,
                ],
            ], 500)->header(self::HEADER, 'fixture');
        }

        // `?mode=csv` has to work here too, or an account on fixtures asks for CSV
        // and silently gets JSON — a difference that disappears on activation.
        if ($request->input('mode') === 'csv') {
            $rows = CsvResponse::rowsFromPayload($fixture);

            if ($rows !== null) {
                return CsvResponse::stream($rows, $endpoint)
                    ->withHeaders([self::HEADER => 'fixture']);
            }
        }

        // Otherwise served verbatim. The body must be shape-identical to the live
        // one, so a consumer coding against a fixture does not find a field that
        // disappears the moment they activate. `X-HP-Data-Source` is the signal.
        return response()
            ->json($fixture)
            ->header(self::HEADER, 'fixture');
    }

    public static function path(string $endpoint): string
    {
        return resource_path(self::DIRECTORY.'/'.$endpoint.'.json');
    }

    /** A fixture exists for an endpoint if it has either a JSON or a binary one. */
    public static function exists(string $endpoint): bool
    {
        if (is_file(self::path($endpoint))) {
            return true;
        }

        return self::binaryPath($endpoint) !== null;
    }

    private static function binaryPath(string $endpoint): ?string
    {
        foreach (self::BINARY_EXTENSIONS as $extension) {
            $candidate = resource_path(self::DIRECTORY.'/'.$endpoint.'.'.$extension);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function binaryFixture(string $endpoint): ?string
    {
        return self::binaryPath($endpoint);
    }

    private function read(string $endpoint): ?array
    {
        $path = self::path($endpoint);

        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }
}
