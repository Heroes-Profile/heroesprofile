<?php

namespace App\Http\Middleware;

use App\Services\Api\ApiResponseLocalizer;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds `_localized` names to a successful external API response, after any cached
 * payload or fixture has been read, so one cached answer serves every language.
 * Listed after ConvertResponseToCsv, so CSV gets the extra columns too.
 */
class LocalizeApiResponse
{
    public function __construct(private readonly ApiResponseLocalizer $localizer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $locale = App::getLocale();

        if ($locale === 'en' || ! $response instanceof JsonResponse || $response->getStatusCode() !== 200) {
            return $response;
        }

        $data = $response->getData(true);

        if (is_array($data)) {
            $response->setData($this->localizer->localize($data, $locale));
        }

        return $response;
    }
}
