<?php

namespace App\Services;

use Google\Cloud\Tasks\V2\Client\CloudTasksClient;
use Google\Cloud\Tasks\V2\CreateTaskRequest;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\OidcToken;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Duration;
use Illuminate\Support\Facades\Log;

class CloudTasksDispatcher
{
    public function dispatch(string $jobId): void
    {
        $projectId = config('global.cloud_tasks.project_id');
        $location = config('global.cloud_tasks.location');
        $queue = config('global.cloud_tasks.queue');
        $handlerUrl = config('global.cloud_tasks.handler_url');
        $serviceAccount = config('global.cloud_tasks.service_account');

        if (! $projectId || ! $handlerUrl || ! $serviceAccount) {
            throw new \RuntimeException('Cloud Tasks is not configured. Set CLOUD_TASKS_* environment variables.');
        }

        $client = new CloudTasksClient;
        $queueName = $client->queueName($projectId, $location, $queue);

        $httpRequest = new HttpRequest([
            'url' => $handlerUrl,
            'http_method' => HttpMethod::POST,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['job_id' => $jobId]),
            'oidc_token' => new OidcToken([
                'service_account_email' => $serviceAccount,
                'audience' => $handlerUrl,
            ]),
        ]);

        $task = new Task([
            'http_request' => $httpRequest,
            'dispatch_deadline' => new Duration(['seconds' => 1800]),
        ]);

        $request = new CreateTaskRequest([
            'parent' => $queueName,
            'task' => $task,
        ]);

        $lastException = null;
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $client->createTask($request);
                Log::info('Cloud Task enqueued for global query', [
                    'job_id' => $jobId,
                    'queue' => $queue,
                    'attempt' => $attempt,
                ]);

                return;
            } catch (\Google\ApiCore\ApiException $e) {
                $lastException = $e;
                if ($attempt < 3) {
                    usleep(250000 * $attempt); // 250ms, 500ms
                }
            }
        }

        throw $lastException;
    }
}
