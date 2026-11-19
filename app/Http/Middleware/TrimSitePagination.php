<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The site's own lists page by number, so a paginated response keeps its rows and page numbers and loses
 * Laravel's link and range fields. Works on the response, so cached arrays are trimmed too. The public API
 * never passes through here and keeps Laravel's full shape.
 */
class TrimSitePagination
{
    private const DROPPED = ['links', 'first_page_url', 'last_page_url', 'next_page_url', 'prev_page_url', 'path', 'from', 'to'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof JsonResponse || ! str_contains((string) $response->getContent(), '"current_page"')) {
            return $response;
        }

        $data = $response->getData(true);

        if (is_array($data)) {
            $response->setData($this->trim($data, 2));
        }

        return $response;
    }

    /** A pagination block is any array with both page numbers, at the top or nested a level or two in. */
    private function trim(array $node, int $depth): array
    {
        if (array_key_exists('current_page', $node) && array_key_exists('last_page', $node)) {
            $node = array_diff_key($node, array_flip(self::DROPPED));
        }

        if ($depth > 0) {
            foreach ($node as $key => $value) {
                if (is_array($value)) {
                    $node[$key] = $this->trim($value, $depth - 1);
                }
            }
        }

        return $node;
    }
}
