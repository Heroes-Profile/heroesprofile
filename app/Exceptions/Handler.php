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
