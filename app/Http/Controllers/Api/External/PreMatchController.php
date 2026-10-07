<?php

namespace App\Http\Controllers\Api\External;

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
    /** A lobby is ten players; anything bigger is not from the uploader. */
    private const MAX_PLAYERS = 10;

    public function store(Request $request, PreMatchService $prematch): Response
    {
        $raw = $request->input('data');

        // Not a string covers both a missing field and `data[]` posted as an
        // array, which the old site let through into a TypeError.
        if (! is_string($raw) || $raw === '') {
            return $this->text('Missing data', 400);
        }

        $players = json_decode($raw, true);

        if (! is_array($players) || $players === [] || count($players) > self::MAX_PLAYERS) {
            return $this->text('Invalid player data', 400);
        }

        $prematchReplayID = $prematch->store($players);

        if ($prematchReplayID === null) {
            return $this->text('No valid players in data', 400);
        }

        return $this->text((string) $prematchReplayID);
    }

    /**
     * The game's mode, sent once the game has started. The uploader only logs the
     * answer, and treats any 4xx as final rather than retrying on the next save.
     */
    public function mode(Request $request, PreMatchService $prematch, string $prematchID): Response
    {
        $mode = $request->input('mode');

        if (! is_string($mode) || $mode === '' || strlen($mode) > 32) {
            return $this->text('Missing mode', 400);
        }

        return match ($prematch->setGameMode((int) $prematchID, $mode)) {
            'set' => response()->noContent(),
            'unknown_mode' => $this->text('Unknown mode', 422),
            'already_set' => $this->text('Mode already set', 409),
            default => $this->text('No such prematch', 404),
        };
    }

    private function text(string $body, int $status = 200): Response
    {
        return response($body, $status, ['Content-Type' => 'text/plain']);
    }
}
