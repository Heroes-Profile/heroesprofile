<?php

namespace App\Auth;

use App\Models\Api\ApiAccount;
use App\Services\Api\ApiKeyResolver;
use Illuminate\Http\Request;

class ApiKeyGuard
{
    /** Where downstream middleware reads the resolved context from. */
    public const REQUEST_ATTRIBUTE = 'apiKeyContext';

    public function __construct(private readonly ApiKeyResolver $resolver) {}

    public function __invoke(Request $request): ?ApiAccount
    {
        $plainKey = $this->keyFromRequest($request);

        if ($plainKey === null) {
            return null;
        }

        $context = $this->resolver->resolve($plainKey);

        if ($context === null) {
            return null;
        }

        $request->attributes->set(self::REQUEST_ATTRIBUTE, $context);

        return $context->account;
    }

    /**
     * `?api_token=` is accepted for compatibility with existing clients but is
     * deprecated — query strings end up in logs, referrers and browser history.
     */
    private function keyFromRequest(Request $request): ?string
    {
        $bearer = $request->bearerToken();

        if (is_string($bearer) && $bearer !== '') {
            return $bearer;
        }

        $queryToken = $request->input('api_token');

        return is_string($queryToken) && $queryToken !== '' ? $queryToken : null;
    }
}
