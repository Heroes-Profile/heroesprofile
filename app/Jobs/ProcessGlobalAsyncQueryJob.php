<?php

namespace App\Jobs;

use App\Services\GlobalAsyncQueryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessGlobalAsyncQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public int $tries = 1;

    public function __construct(
        public string $jobId,
        public string $statusKey,
        public string $cacheKey,
        public string $handlerClass,
        public string $handlerMethod,
        public array $requestData,
        public int $cacheFreshSeconds,
        public int $cacheStaleSeconds
    ) {
    }

    public function handle(GlobalAsyncQueryService $globalAsyncQueryService): void
    {
        ini_set('max_execution_time', '900');

        $jobPayload = [
            'status' => 'processing',
            'cache_key' => $this->cacheKey,
            'status_key' => $this->statusKey,
            'handler_class' => $this->handlerClass,
            'handler_method' => $this->handlerMethod,
            'request' => $this->requestData,
            'cache_fresh_seconds' => $this->cacheFreshSeconds,
            'cache_stale_seconds' => $this->cacheStaleSeconds,
        ];

        $globalAsyncQueryService->markProcessing($this->jobId, $this->statusKey, $jobPayload);

        try {
            $handler = app($this->handlerClass);

            if (! method_exists($handler, $this->handlerMethod)) {
                throw new \RuntimeException("Handler method {$this->handlerMethod} not found.");
            }

            $request = new Request($this->requestData);
            $data = $handler->{$this->handlerMethod}($request);

            $globalAsyncQueryService->markComplete(
                $this->jobId,
                $this->statusKey,
                $this->cacheKey,
                $data,
                $this->cacheFreshSeconds,
                $this->cacheStaleSeconds
            );
        } catch (\Throwable $exception) {
            Log::error('Global async query failed', [
                'job_id' => $this->jobId,
                'cache_key' => $this->cacheKey,
                'handler' => $this->handlerClass.'@'.$this->handlerMethod,
                'error' => $exception->getMessage(),
            ]);

            $globalAsyncQueryService->markFailed(
                $this->jobId,
                $this->statusKey,
                $this->cacheKey,
                $exception->getMessage()
            );

            throw $exception;
        }
    }
}
