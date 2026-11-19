<?php

namespace Tests\Unit\Support;

use App\Services\CloudTasksDispatcher;
use Google\ApiCore\ApiException;
use Google\ApiCore\Call;
use Google\ApiCore\InsecureCredentialsWrapper;
use Google\ApiCore\Transport\TransportInterface;
use Google\Cloud\Tasks\V2\Client\CloudTasksClient;
use Google\Cloud\Tasks\V2\Task;
use Google\Rpc\Code;
use GuzzleHttp\Promise\Create;
use Tests\TestCase;

class CloudTasksDispatcherTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'global.cloud_tasks.project_id' => 'test-project',
            'global.cloud_tasks.location' => 'us-central1',
            'global.cloud_tasks.queue' => 'global-queries',
            'global.cloud_tasks.handler_url' => 'https://worker.test/api/v1/internal/global/process',
            'global.cloud_tasks.service_account' => 'tasks@test-project.iam.gserviceaccount.com',
        ]);
    }

    public function test_the_task_is_named_after_the_job_and_sent_with_a_short_timeout(): void
    {
        $transport = $this->transport([null]);

        $this->dispatcher($transport)->dispatch('job-1');

        $this->assertCount(1, $transport->calls);
        $this->assertSame(
            'projects/test-project/locations/us-central1/queues/global-queries/tasks/job-1',
            $transport->calls[0]['call']->getMessage()->getTask()->getName()
        );
        $this->assertSame(5000, $transport->calls[0]['options']['timeoutMillis']);
    }

    public function test_a_retry_refused_as_a_duplicate_counts_as_enqueued(): void
    {
        $transport = $this->transport([
            new ApiException('Deadline exceeded', Code::DEADLINE_EXCEEDED, 'DEADLINE_EXCEEDED'),
            new ApiException('Task already exists', Code::ALREADY_EXISTS, 'ALREADY_EXISTS'),
        ]);

        $this->dispatcher($transport)->dispatch('job-1');

        $this->assertCount(2, $transport->calls);
    }

    public function test_it_gives_up_after_three_unavailable_tries(): void
    {
        $unavailable = new ApiException('The service is currently unavailable.', Code::UNAVAILABLE, 'UNAVAILABLE');
        $transport = $this->transport([$unavailable, $unavailable, $unavailable]);

        try {
            $this->dispatcher($transport)->dispatch('job-1');
            $this->fail('Expected the last error to be thrown');
        } catch (ApiException $e) {
            $this->assertSame('UNAVAILABLE', $e->getStatus());
        }

        $this->assertCount(3, $transport->calls);
    }

    private function dispatcher(TransportInterface $transport): CloudTasksDispatcher
    {
        return new class($transport) extends CloudTasksDispatcher
        {
            public function __construct(private TransportInterface $transport) {}

            protected function newClient(): CloudTasksClient
            {
                return new CloudTasksClient([
                    'credentials' => new InsecureCredentialsWrapper,
                    'transport' => $this->transport,
                ]);
            }
        };
    }

    /** @param  array<int, ?ApiException>  $outcomes  one per try: null succeeds, an exception fails it */
    private function transport(array $outcomes): TransportInterface
    {
        return new class($outcomes) implements TransportInterface
        {
            /** @var array<int, array{call: Call, options: array}> */
            public array $calls = [];

            public function __construct(private array $outcomes) {}

            public function startUnaryCall(Call $call, array $options)
            {
                $this->calls[] = ['call' => $call, 'options' => $options];
                $error = array_shift($this->outcomes);

                return $error ? Create::rejectionFor($error) : Create::promiseFor(new Task);
            }

            public function startBidiStreamingCall(Call $call, array $options)
            {
                throw new \LogicException('Not used');
            }

            public function startClientStreamingCall(Call $call, array $options)
            {
                throw new \LogicException('Not used');
            }

            public function startServerStreamingCall(Call $call, array $options)
            {
                throw new \LogicException('Not used');
            }

            public function close() {}
        };
    }
}
