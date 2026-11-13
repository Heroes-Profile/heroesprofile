<?php

namespace App\Http\Middleware;

use App\Services\LocalizedGameStrings;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/** Adds localized talent text to the site's JSON responses, after any cached payload has been read. */
class LocalizeGameStrings
{
    public function __construct(private readonly LocalizedGameStrings $strings) {}

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $locale = App::getLocale();

        if ($locale === 'en' || ! $response instanceof JsonResponse) {
            return $response;
        }

        $data = $response->getData(true);

        if (is_array($data)) {
            $response->setData($this->strings->localizeTalents($data, $locale));
        }

        return $response;
    }
}
