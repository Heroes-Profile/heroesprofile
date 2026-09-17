<?php

namespace App\Exceptions;

use App\Services\RateLimitLoggingService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            return $this->shouldReport($e);
        });

        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            app(RateLimitLoggingService::class)->logFromThrottleException($request, $e);
        });
    }

    /**
     * The external API answers every error as JSON in its one envelope. Without this a
     * caller that does not send `Accept: application/json` gets a redirect.
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $this->isExternalApi($request) || parent::shouldReturnJson($request, $e);
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        if (! $this->isExternalApi($request)) {
            return parent::invalidJson($request, $exception);
        }

        return response()->json([
            'error' => [
                'code' => 'invalid_parameters',
                'message' => 'One or more parameters are invalid.',
                'errors' => array_values(array_merge(...array_values($exception->errors()))),
            ],
        ], $exception->status);
    }

    protected function prepareJsonResponse($request, Throwable $e)
    {
        if (! $this->isExternalApi($request)) {
            return parent::prepareJsonResponse($request, $e);
        }

        $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

        $code = match ($status) {
            404 => 'not_found',
            405 => 'method_not_allowed',
            429 => 'rate_limited',
            500 => 'server_error',
            default => 'http_'.$status,
        };

        $message = $status === 500 || ! $this->isHttpException($e) || $e->getMessage() === ''
            ? ($status === 500 ? 'Something went wrong on our side.' : 'Request could not be completed.')
            : $e->getMessage();

        return new JsonResponse(
            ['error' => ['code' => $code, 'message' => $message]],
            $status,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }

    private function isExternalApi(Request $request): bool
    {
        $route = $request->route();

        if ($route !== null && ! is_string($route)) {
            return in_array('api.external', $route->gatherMiddleware(), true);
        }

        return $request->is(trim((string) config('api.path'), '/').'/*');
    }

    public function shouldReport(Throwable $e)
    {
        if ($e instanceof ThrottleRequestsException) {
            return false;
        }

        // Customize this logic to exclude specific types of exceptions
        if ($e instanceof ConnectionException) {
            return false; // Do not report ConnectionExceptions to Flare
        }

        // Exclude reporting of certain SQL exceptions
        if ($e instanceof \PDOException &&
            strpos($e->getMessage(), 'SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock') !== false) {
            return false;
        }

        // Exclude reporting of SyntaxError with message "Unexpected end of input"
        if ($e instanceof \ErrorException &&
            $e->getCode() === 0 &&
            (strpos($e->getMessage(), 'Unexpected end of input') !== false ||
             strpos($e->getMessage(), "Label 'https' has already been declared") !== false)) {
            return false;
        }

        // Exclude reporting of the error message "Cannot redefine property: websredir"
        if ($e instanceof \ErrorException &&
            $e->getCode() === 0 &&
            strpos($e->getMessage(), 'Cannot redefine property: websredir') !== false) {
            return false;
        }

        // Exclude reporting of the error message "The operation was aborted."
        if ($e instanceof \ErrorException &&
            $e->getCode() === 0 &&
            strpos($e->getMessage(), 'The operation was aborted.') !== false) {
            return false;
        }

        // Exclude reporting of TypeError, NetworkError, and Load failed
        if ($e instanceof \TypeError ||
            ($e instanceof \ErrorException &&
             $e->getCode() === 0 &&
             (strpos($e->getMessage(), 'NetworkError') !== false || strpos($e->getMessage(), 'Load failed') !== false))) {
            return false;
        }

        return parent::shouldReport($e);
    }
}
