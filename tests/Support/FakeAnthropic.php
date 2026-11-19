<?php

namespace Tests\Support;

use GuzzleHttp\Psr7\Response;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\DiscoveryStrategy;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Stands in for Anthropic's API as the SDK's HTTP transport. The SDK finds its client
 * through php-http discovery, so this is put first in line; the SDK itself runs as normal.
 */
class FakeAnthropic implements ClientInterface, DiscoveryStrategy
{
    private static ?self $instance = null;

    private static array $savedStrategies = [];

    /** Each request's decoded JSON body. */
    public array $requests = [];

    /** Answers in order; the last one repeats. Each is [status, body array]. */
    public array $responses = [];

    public static function install(): self
    {
        self::$instance = new self;
        self::$savedStrategies = iterator_to_array(ClassDiscovery::getStrategies());
        ClassDiscovery::prependStrategy(self::class);

        return self::$instance;
    }

    public static function uninstall(): void
    {
        if (self::$instance !== null) {
            ClassDiscovery::setStrategies(self::$savedStrategies);
            self::$instance = null;
        }
    }

    public static function getCandidates($type)
    {
        return self::$instance !== null && is_a(self::class, $type, true)
            ? [['class' => fn () => self::$instance, 'condition' => true]]
            : [];
    }

    /** A finished reply with this text. */
    public function reply(string $text, string $stopReason = 'end_turn'): static
    {
        $this->responses[] = [200, [
            'id' => 'msg_test', 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-haiku-5-5',
            'content' => [['type' => 'text', 'text' => $text]],
            'stop_reason' => $stopReason, 'stop_sequence' => null,
            'usage' => ['input_tokens' => 100, 'output_tokens' => 20],
        ]];

        return $this;
    }

    public function fail(int $status, string $type): static
    {
        $this->responses[] = [$status, ['type' => 'error', 'error' => ['type' => $type, 'message' => 'fake '.$type]]];

        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = [
            'url' => (string) $request->getUri(),
            'authorization' => $request->getHeaderLine('Authorization'),
            'body' => json_decode((string) $request->getBody(), true),
        ];

        [$status, $body] = count($this->responses) > 1 ? array_shift($this->responses) : ($this->responses[0] ?? [500, ['error' => 'no fake reply']]);

        // Errors are final, so the SDK doesn't back off and retry
        return new Response($status, ['Content-Type' => 'application/json', 'request-id' => 'req_test', 'x-should-retry' => 'false'], json_encode($body));
    }
}
