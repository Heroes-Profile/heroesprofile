<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\Api\PreMatchService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pre-match lobby capture.
 *
 * Anonymous permanently, and the body is a bare integer on success. The uploader
 * runs `Int32.TryParse` over whatever comes back and opens the pre-match page
 * with the result, so a JSON envelope would kill the feature outright and leave
 * nothing but a client-side log line behind.
 *
 * Failures answer plain text too, which the client cannot parse as an integer —
 * that is how it knows to give up, and it matches the old site exactly.
 */
class PreMatchController extends Controller
{
    public function store(Request $request, PreMatchService $prematch): Response
    {
        $raw = $request->input('data');

        // Not a string covers both a missing field and `data[]` posted as an
        // array, which the old site let through into a TypeError.
        if (! is_string($raw) || $raw === '') {
            return $this->text('Missing data', 400);
        }

        $players = json_decode($raw, true);

        if (! is_array($players) || $players === []) {
            return $this->text('Invalid player data', 400);
        }

        $prematchReplayID = $prematch->store($players);

        if ($prematchReplayID === null) {
            return $this->text('No valid players in data', 400);
        }

        return $this->text((string) $prematchReplayID);
    }

    private function text(string $body, int $status = 200): Response
    {
        return response($body, $status, ['Content-Type' => 'text/plain']);
    }
}
