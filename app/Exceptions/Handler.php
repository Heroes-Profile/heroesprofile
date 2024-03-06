<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
    }
    public function shouldReport(Throwable $e)
    {
        // Customize this logic to exclude specific types of exceptions
        if ($e instanceof \Illuminate\Http\Client\ConnectionException) {
            return false; // Do not report ConnectionExceptions to Flare
        }

        return parent::shouldReport($e);
    }
}
