<?php

namespace App\Http\Middleware;

use App\Support\CsvResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * `?mode=csv` on any endpoint that answers with JSON.
 *
 * Done here rather than per endpoint because the old API did it per endpoint, which
 * is how only fourteen of them ever got it and none of them got documented. One place
 * means a new endpoint supports CSV the day it is routed, without being asked to.
 *
 * Deliberately not applied to:
 *   - anything that is not a 2xx, so an error stays a readable JSON envelope
 *   - anything not answering JSON: the replay download is a file, and two uploader
 *     endpoints answer plain text that deployed clients string-compare
 */
class ConvertResponseToCsv
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->wants($request) || ! $this->convertible($response)) {
            return $response;
        }

        $payload = $response->getData(true);

        if (! is_array($payload)) {
            return $response;
        }

        $rows = CsvResponse::rowsFromPayload($payload);

        if ($rows === null) {
            return $response;
        }

        $csv = CsvResponse::stream($rows, $this->filename($request));

        // Carried over so a caller on fixtures can still tell what they are looking
        // at, and so quota accounting stays visible in CSV as it is in JSON.
        foreach (['X-HP-Data-Source', 'X-HP-Quota-Limit', 'X-HP-Quota-Remaining', 'X-HP-Quota-Reset'] as $header) {
            if ($response->headers->has($header)) {
                $csv->headers->set($header, $response->headers->get($header));
            }
        }

        return $csv;
    }

    private function wants(Request $request): bool
    {
        return strtolower((string) $request->query('mode')) === 'csv';
    }

    private function convertible(Response $response): bool
    {
        return $response instanceof JsonResponse
            && $response->getStatusCode() >= 200
            && $response->getStatusCode() < 300;
    }

    /** Named after the endpoint, so several downloads do not all land as the same file. */
    private function filename(Request $request): string
    {
        $path = trim((string) $request->path(), '/');
        $prefix = trim((string) config('api.path'), '/');

        if (str_starts_with($path, $prefix)) {
            $path = trim(substr($path, strlen($prefix)), '/');
        }

        return $path === '' ? 'export' : $path;
    }
}
