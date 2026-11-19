<?php

namespace Tests\Support;

use App\Services\CloudTasksDispatcher;

/**
 * Cloud Tasks without Google: keeps what the app enqueues so a test can deliver it.
 * Everything up to the createTask call (configuration checks, payloads) is the app's own.
 */
class FakeCloudTasks extends CloudTasksDispatcher
{
    /** @var array<int, array{type: string, job_id?: string, url?: string, body?: array, queue?: string, not_before?: ?\DateTimeInterface, audience?: ?string}> */
    public array $queued = [];

    /** When set, the next dispatch throws it, as a Cloud Tasks outage would. */
    public ?\Throwable $failNext = null;

    public function dispatch(string $jobId): void
    {
        $this->throwIfFailing();

        if (! config('global.cloud_tasks.project_id') || ! config('global.cloud_tasks.handler_url') || ! config('global.cloud_tasks.service_account')) {
            throw new \RuntimeException('Cloud Tasks is not configured. Set CLOUD_TASKS_* environment variables.');
        }

        $this->queued[] = ['type' => 'global', 'job_id' => $jobId];
    }

    public function dispatchHttp(string $url, array $body, string $queue, ?\DateTimeInterface $notBefore = null, ?string $audience = null): void
    {
        $this->throwIfFailing();

        $this->queued[] = [
            'type' => 'http',
            'url' => $url,
            'body' => $body,
            'queue' => $queue,
            'not_before' => $notBefore,
            'audience' => $audience ?? config('global.cloud_tasks.handler_url'),
        ];
    }

    private function throwIfFailing(): void
    {
        if ($this->failNext) {
            [$e, $this->failNext] = [$this->failNext, null];

            throw $e;
        }
    }
}
