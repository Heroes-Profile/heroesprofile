<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * `?lang=` on the external API: the language of error messages, and of the
 * `_localized` names LocalizeApiResponse adds. First in the group, so every error
 * after it, a 401 from the key guard included, is in that language.
 */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = self::resolve($request->query('lang'));
        App::setLocale($locale);

        $response = $next($request);

        if ($locale !== 'en') {
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }

    /** A supported locale, matched case-insensitively; anything else is English. */
    public static function resolve(mixed $lang): string
    {
        if (is_string($lang)) {
            foreach (array_keys(config('app.supported_locales')) as $locale) {
                if (strcasecmp($locale, trim($lang)) === 0) {
                    return $locale;
                }
            }
        }

        return 'en';
    }
}
