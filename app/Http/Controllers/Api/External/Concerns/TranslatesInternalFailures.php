<?php

namespace App\Http\Controllers\Api\External\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * The site's controllers answer bad input three ways, none of them a usable API
 * error: HTTP 200 with `status: "failure to validate inputs"`, a redirect to `/` in
 * production, or a bare `{errors}` 422. All three become one 422.
 */
trait TranslatesInternalFailures
{
    private const INTERNAL_FAILURE_STATUS = 'failure to validate inputs';

    protected function internalFailure(mixed $result): ?JsonResponse
    {
        if ($result instanceof RedirectResponse) {
            return $this->invalidParameters();
        }

        $payload = $result;

        if ($result instanceof JsonResponse) {
            $payload = $result->getData(true);

            if ($result->getStatusCode() === 422 && is_array($payload) && ! isset($payload['error'])) {
                return $this->invalidParameters((array) ($payload['errors'] ?? []));
            }
        }

        if (is_array($payload) && ($payload['status'] ?? null) === self::INTERNAL_FAILURE_STATUS) {
            // `data` is deliberately dropped: it echoes the request, resolved
            // blizz_id included.
            return $this->invalidParameters((array) ($payload['errors'] ?? []));
        }

        return null;
    }

    /** @param  array<int|string, mixed>  $errors */
    protected function invalidParameters(array $errors = []): JsonResponse
    {
        $error = [
            'code' => 'invalid_parameters',
            'message' => 'One or more parameters are invalid.',
        ];

        $messages = array_values(array_filter(
            array_merge(...array_map(fn ($value) => (array) $value, array_values($errors))),
            'is_string'
        ));

        if ($messages !== []) {
            $error['errors'] = $messages;
        }

        return response()->json(['error' => $error], 422);
    }
}
