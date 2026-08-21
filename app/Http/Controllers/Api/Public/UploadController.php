<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\Api\ReplayUploadService;
use App\Services\ClientIpService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replay ingestion.
 *
 * Anonymous permanently. The uploader is a public repository, so a bundled key
 * would be extractable from source — a shared secret, not authentication. Volume
 * is capped per IP on the route instead.
 *
 * Rejections answer 200 with a body the client cannot read as a status, which is
 * what the old site did and what the deployed uploaders expect: anything they
 * cannot parse a status out of becomes an upload error client-side.
 */
class UploadController extends Controller
{
    /** The width of `uploaded_replay_data.uploaded_source`. */
    private const SOURCE_LENGTH = 255;

    public function store(Request $request, ReplayUploadService $uploads, string $source): Response
    {
        $source = substr(trim($source), 0, self::SOURCE_LENGTH) ?: 'unknown';
        $ip = ClientIpService::getClientIp($request);

        if (! $request->hasFile('file')) {
            $uploads->reject($ip, $source, 'No file specified');

            return response()->json(['success' => false, 'Error' => 'no file specified']);
        }

        $file = $request->file('file');

        if ($file->getSize() > ReplayUploadService::MAX_BYTES) {
            $uploads->reject($ip, $source, 'File too large', $file);

            return response()->json(['success' => false, 'Error' => 'File too large']);
        }

        return response()->json($uploads->upload(
            $file,
            $source,
            $ip,
            // Both are positional to the deployed client, which always sends them.
            // Empty ones arrive null: ConvertEmptyStringsToNull runs globally.
            (string) ($request->input('version') ?: 'unknown'),
            (string) ($request->input('compiled') ?: '0'),
        ));
    }

    /**
     * Whether a replay with this fingerprint is already stored.
     *
     * The client parses this as JSON and reads `exists` as a bool, so this one
     * keeps an envelope. It also promotes the replay's source — see the service.
     */
    public function fingerprint(ReplayUploadService $uploads, string $fingerprint): Response
    {
        return response()->json(['exists' => $uploads->fingerprintExists($fingerprint)]);
    }

    /**
     * Whether a replay has been parsed and stored yet.
     *
     * Answers the literal words `true` and `false` as plain text, because the
     * client compares the body as a string. Wrap this in JSON and the post-match
     * page silently stops opening, with only a client-side log line to show for
     * it. Nothing here is allowed to throw for the same reason: an unparseable
     * `replayID` answers `false` rather than a validation envelope.
     */
    public function parsed(Request $request, ReplayUploadService $uploads): Response
    {
        $parsed = $uploads->isParsed((int) $request->query('replayID'));

        return response($parsed ? 'true' : 'false', 200, ['Content-Type' => 'text/plain']);
    }
}
