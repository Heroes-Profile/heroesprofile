<?php

namespace App\Http\Controllers\Api;

use App\Auth\ApiKeyContext;
use App\Auth\ApiKeyGuard;
use App\Http\Controllers\Controller;
use App\Services\Api\ApiKeyResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route as Router;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Executes one public API call on behalf of the signed-in portal account.
 *
 * Keys are hashed and unrecoverable, so the test client cannot hold one to send.
 * Instead the account's own key is resolved server-side and the call is dispatched
 * through the real route — the same middleware, the same quota, the same fixture
 * gate. A call made here counts exactly as the caller's own would.
 *
 * Two things keep this from being a way to reach anything else:
 *
 *  - it takes a **route name**, never a URL, and refuses any name that is not a
 *    public API route;
 *  - it only ever issues GET, so nothing here can upload a replay or record a
 *    pre-match on someone's behalf.
 */
class TryItController extends Controller
{
    /** Long enough for a cache hit; a cold global query answers 202 anyway. */
    private const TIMEOUT_SECONDS = 30;

    public function __invoke(Request $request, ApiKeyResolver $resolver): JsonResponse
    {
        $validated = $request->validate([
            'route' => ['required', 'string'],
            'parameters' => ['sometimes', 'array'],
        ]);

        $route = $this->publicRoute($validated['route']);

        if ($route === null) {
            return response()->json(['error' => 'That is not a public API endpoint.'], 422);
        }

        $account = Auth::guard('api_web')->user();
        $context = $resolver->resolveForAccount((int) $account->id);

        // An admin exercising their grant needs no key: quota is skipped
        // downstream, and the key id only buckets the rate limiter.
        if ($context === null && $account->actingAsAdmin()) {
            $context = new ApiKeyContext(
                account: $account,
                keyId: 0,
                planIds: [],
                planName: null,
                subscriptionActive: false,
                comped: false,
            );
        }

        if ($context === null) {
            return response()->json([
                'error' => 'Create an API key first — calls made here are charged to it.',
            ], 422);
        }

        return $this->dispatch($route, $validated['parameters'] ?? [], $context);
    }

    /**
     * The named route, if it is a public API GET. Path parameters are filled from
     * the supplied values; anything left over becomes the query string.
     */
    private function publicRoute(string $name): ?Route
    {
        if (! str_starts_with($name, 'api.external.')) {
            return null;
        }

        foreach (Router::getRoutes() as $route) {
            if ($route->getName() === $name && in_array('GET', $route->methods(), true)) {
                return $route;
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $parameters */
    private function dispatch(Route $route, array $parameters, $context): JsonResponse
    {
        // The endpoint path with no mount prefix — `maps`, `matches/{replayID}`.
        $path = $this->stripPrefix($route->uri());
        $query = array_filter($parameters, fn ($value) => $value !== null && $value !== '');

        foreach ($route->parameterNames() as $name) {
            $value = (string) ($query[$name] ?? '');
            unset($query[$name]);
            $path = str_replace(['{'.$name.'}', '{'.$name.'?}'], rawurlencode($value), $path);
        }

        $suffix = $query === [] ? '' : '?'.http_build_query($query);

        // Dispatched against the path mount, not the subdomain one. The subdomain
        // routes carry a domain constraint, and a request synthesized here has
        // this app's host — so those would never match and would fall through to
        // the web fallback as a 404.
        $url = '/'.trim((string) config('api.path'), '/').'/'.$path.$suffix;

        $call = Request::create($url, 'GET');
        // The guard reads a key off the request and there is none to read, so the
        // context it would have produced is placed directly. Everything
        // downstream — quota, fixtures, the per-key limiter — reads it from here.
        $call->attributes->set(ApiKeyGuard::REQUEST_ATTRIBUTE, $context);
        $call->headers->set('Accept', 'application/json');

        set_time_limit(self::TIMEOUT_SECONDS);

        try {
            $response = app()->handle($call);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'status' => 500,
                'headers' => [],
                'body' => 'The call failed before it reached the endpoint.',
                'url' => $url,
            ]);
        }

        return response()->json([
            'status' => $response->getStatusCode(),
            'headers' => $this->headers($response),
            'body' => $this->body($response),
            'url' => $url,
            // What a caller's own code would request. Not what was dispatched:
            // the subdomain is where these live permanently, but it is reached
            // through a domain-constrained route this proxy cannot use.
            'public_url' => rtrim((string) config('api.domain'), '/').'/v1/'.$path.$suffix,
        ]);
    }

    /** @return array<string, string> the headers a caller would care about */
    private function headers(Response $response): array
    {
        $keep = ['content-type', 'location', 'retry-after', 'x-hp-data-source', 'x-ratelimit-remaining'];
        $headers = [];

        foreach ($keep as $name) {
            if ($response->headers->has($name)) {
                $headers[$name] = $response->headers->get($name);
            }
        }

        return $headers;
    }

    /**
     * Decoded when it is JSON, and left as text when it is not — two endpoints
     * answer plain text on purpose and must be shown as they really are.
     */
    private function body(Response $response): mixed
    {
        $content = $response->getContent();

        if (! is_string($content)) {
            return null;
        }

        $decoded = json_decode($content, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $content;
    }

    private function stripPrefix(string $uri): string
    {
        foreach ([config('api.path').'/', 'v1/'] as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return substr($uri, strlen($prefix));
            }
        }

        return $uri;
    }
}
