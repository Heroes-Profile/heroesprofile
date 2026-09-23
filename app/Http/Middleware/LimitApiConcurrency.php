<?php

namespace App\Http\Middleware;

use App\Auth\ApiKeyGuard;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caps how many requests one key can have in flight at once.
 *
 * The per-minute throttle counts requests, not overlap: a key allowed 500 a minute
 * can fire all 500 in the same second. API and site share Cloud Run instances and
 * the Cloud SQL connector's per-instance connection budget, so a burst like that
 * degrades the site for everyone.
 *
 * Slots are MySQL named locks rather than cache rows. The cache is the database
 * either way, and a named lock is released when its connection closes, so a
 * container that dies mid-request cannot leave a slot held.
 */
class LimitApiConcurrency
{
    private const CONNECTION = 'heroesprofile_api';

    private const SLOT_ATTRIBUTE = 'apiConcurrencySlot';

    public const LIMIT_HEADER = 'X-HP-Concurrency-Limit';

    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE);

        if ($context === null || $request->routeIs(...RouteServiceProvider::UPLOADER_ROUTES)) {
            return $next($request);
        }

        $limit = (int) config('api.rate_limits.concurrent');
        $slot = $this->acquire($context->keyId, $limit);

        if ($slot === null) {
            throw new ThrottleRequestsException(
                'Too many requests in flight: at most '.$limit.' at a time per key. Wait for one to finish before sending another.',
                null,
                ['Retry-After' => 1, self::LIMIT_HEADER => $limit]
            );
        }

        $request->attributes->set(self::SLOT_ATTRIBUTE, $slot);

        return $next($request);
    }

    /**
     * Released here rather than in handle(): a streamed replay download is still
     * sending after handle() returns. Anything that never reaches terminate(), like
     * the docs Try It console, is released when its connection closes.
     */
    public function terminate(Request $request, Response $response): void
    {
        $slot = $request->attributes->get(self::SLOT_ATTRIBUTE);

        if ($slot !== null) {
            DB::connection(self::CONNECTION)->select('SELECT RELEASE_LOCK(?)', [$slot]);
        }
    }

    /** The name of the slot taken, or null when all of them are held. */
    private function acquire(int $keyId, int $limit): ?string
    {
        // A random first slot, so a busy key is not always probed from slot zero.
        $start = random_int(0, $limit - 1);

        for ($i = 0; $i < $limit; $i++) {
            $name = 'hp-api-inflight:'.$keyId.':'.(($start + $i) % $limit);

            $row = DB::connection(self::CONNECTION)->selectOne('SELECT GET_LOCK(?, 0) AS acquired', [$name]);

            if ((int) $row->acquired === 1) {
                return $name;
            }
        }

        return null;
    }
}
