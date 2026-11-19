<?php

namespace Tests\Support;

use App\Services\CloudTasksDispatcher;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

/**
 * Plays Cloud Tasks: the app enqueues into FakeCloudTasks, and runQueuedTasks() delivers each
 * task to its route the way Google does, with a signed OIDC token the real middleware verifies
 * against a Google cert set served by Http::fake.
 */
trait SimulatesCloudTasks
{
    protected FakeCloudTasks $cloudTasks;

    /** @var array<int, array<string, mixed>> tasks addressed to other services, kept for assertions */
    protected array $externalTasks = [];

    private string $taskSigningKey = '';

    private const TASK_KEY_ID = 'test-key';

    private const TASK_SERVICE_ACCOUNT = 'global-tasks@test-project.iam.gserviceaccount.com';

    private const TASK_HANDLER_URL = 'https://worker.test/api/v1/internal/global/process';

    protected function setUpCloudTasks(): void
    {
        config([
            'global.cloud_tasks.project_id' => 'test-project',
            'global.cloud_tasks.handler_url' => self::TASK_HANDLER_URL,
            'global.cloud_tasks.service_account' => self::TASK_SERVICE_ACCOUNT,
        ]);

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $this->taskSigningKey);
        $details = openssl_pkey_get_details($key);

        Http::fake([
            'https://www.googleapis.com/oauth2/v3/certs' => Http::response(['keys' => [[
                'kty' => 'RSA',
                'alg' => 'RS256',
                'use' => 'sig',
                'kid' => self::TASK_KEY_ID,
                'n' => rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '='),
                'e' => rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '='),
            ]]]),
        ]);

        $this->cloudTasks = new FakeCloudTasks;
        $this->app->instance(CloudTasksDispatcher::class, $this->cloudTasks);
    }

    /** The OIDC token Google attaches to a task. */
    protected function taskToken(?string $audience = null, string $email = self::TASK_SERVICE_ACCOUNT): string
    {
        return JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => $audience ?? self::TASK_HANDLER_URL,
            'email' => $email,
            'email_verified' => true,
            'iat' => time(),
            'exp' => time() + 3600,
        ], $this->taskSigningKey, 'RS256', self::TASK_KEY_ID);
    }

    /** @return array<string, string> */
    protected function taskHeaders(int $retryCount = 0, ?string $token = null): array
    {
        return [
            'X-CloudTasks-TaskName' => 'task-'.bin2hex(random_bytes(4)),
            'X-CloudTasks-TaskRetryCount' => (string) $retryCount,
            'Authorization' => 'Bearer '.($token ?? $this->taskToken()),
        ];
    }

    /**
     * Delivers queued tasks, including any they enqueue, until the queue is empty.
     *
     * @return array<int, TestResponse> one response per delivered task
     */
    protected function runQueuedTasks(int $limit = 200): array
    {
        $responses = [];

        while ($task = array_shift($this->cloudTasks->queued)) {
            if (count($responses) >= $limit) {
                $this->fail("More than {$limit} Cloud Tasks queued; a task is probably re-queueing itself.");
            }

            // Tasks for other services (the Deep Analytics parser, Twitch's push target) aren't ours to run
            if ($task['type'] === 'http' && parse_url($task['url'], PHP_URL_HOST) !== parse_url(self::TASK_HANDLER_URL, PHP_URL_HOST)) {
                $this->externalTasks[] = $task;

                continue;
            }

            $responses[] = $task['type'] === 'global'
                ? $this->postJson(parse_url(self::TASK_HANDLER_URL, PHP_URL_PATH), ['job_id' => $task['job_id']], $this->taskHeaders())
                : $this->postJson(parse_url($task['url'], PHP_URL_PATH), $task['body'], $this->taskHeaders(0, $this->taskToken($task['audience'])));
        }

        return $responses;
    }
}
