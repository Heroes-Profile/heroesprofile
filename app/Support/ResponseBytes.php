<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

/**
 * How many bytes a response puts on the wire, for egress accounting.
 *
 * A StreamedResponse has no body to measure — `getContent()` returns false, so
 * `strlen((string) $response->getContent())` is 0 for every replay download, the
 * one endpoint that costs real egress money. Content-Length carries the real
 * figure: Laravel's FilesystemAdapter::response() fills it from `size()` before
 * handing back the stream, and Response::prepare() has already run by the time
 * middleware sees the response on the way out.
 *
 * This is the object's declared size, not bytes actually delivered — a client
 * that disconnects halfway still counts for the whole file. That is deliberate.
 * Counting real bytes means wrapping the output buffer, which breaks streaming.
 */
class ResponseBytes
{
    public static function of(Response $response): int
    {
        $content = $response->getContent();

        if (is_string($content) && $content !== '') {
            return strlen($content);
        }

        return max(0, (int) $response->headers->get('Content-Length', 0));
    }
}
