<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
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

    /** Visible in a debugger without having to read response headers. */
    private const NOTE_KEY = '_test_data';

    public function handle(Request $request, Closure $next, string $endpoint): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null || ! $context->servesFixtures()) {
            return $next($request);
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

        return response()
            ->json($this->annotate($fixture))
            ->header(self::HEADER, 'fixture');
    }

    public static function path(string $endpoint): string
    {
        return resource_path(self::DIRECTORY.'/'.$endpoint.'.json');
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

    /**
     * Only an object-shaped fixture can carry the note; a top-level list is served
     * untouched so the response shape still matches the real endpoint.
     */
    private function annotate(array $fixture): array
    {
        if (array_is_list($fixture)) {
            return $fixture;
        }

        return [self::NOTE_KEY => 'Example data, not live results. This account is in test mode or has not activated live data. No quota was consumed.'] + $fixture;
    }
}
