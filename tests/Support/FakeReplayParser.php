<?php

namespace Tests\Support;

use App\Services\Api\ReplayParserClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;

/**
 * The replay parser service, answered from Guzzle mock handlers so ReplayParserClient itself runs
 * for real: the request it builds, the Cloud Run identity token it fetches from the metadata server,
 * and how it reads the parser's answer.
 *
 * Answers are the parser's own output for real replays, recorded with the parser run locally
 * (tests/Fixtures/replay-parser).
 */
class FakeReplayParser
{
    public const URL = 'https://replay-parser.test/parse';

    /** Each parser request: url, authorization header, decoded JSON body. */
    public array $requests = [];

    /** Each metadata request, as a URL. */
    public array $tokenRequests = [];

    private MockHandler $parser;

    public function __construct()
    {
        $this->parser = new MockHandler;
        $stack = HandlerStack::create($this->parser);
        $stack->push(Middleware::mapRequest(function ($request) {
            $this->requests[] = [
                'url' => (string) $request->getUri(),
                'authorization' => $request->getHeaderLine('Authorization'),
                'body' => json_decode((string) $request->getBody(), true),
            ];

            return $request;
        }));

        $metadata = HandlerStack::create(function ($request) {
            $this->tokenRequests[] = (string) $request->getUri();

            // A JWT the client can read `exp` out of
            $claims = rtrim(strtr(base64_encode(json_encode(['exp' => time() + 3600])), '+/', '-_'), '=');

            return Create::promiseFor(new Response(200, [], 'eyJhbGciOiJSUzI1NiJ9.'.$claims.'.signature'));
        });

        app()->instance(ReplayParserClient::class, new ReplayParserClient(new Client(['handler' => $stack]), new Client(['handler' => $metadata])));
        config(['services.replay_parser.url' => self::URL]);
    }

    /** A recorded parser answer, as the parser sent it. */
    public function answerWithFixture(string $name, array $overrides = []): static
    {
        $body = json_decode(file_get_contents(__DIR__.'/../Fixtures/replay-parser/'.$name.'.json'), true);

        return $this->answer(200, json_encode(array_replace($body, $overrides)));
    }

    public function answer(int $status, string $body): static
    {
        $this->parser->append(new Response($status, ['Content-Type' => 'application/json'], $body));

        return $this;
    }

    public function fixture(string $name): array
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/replay-parser/'.$name.'.json'), true);
    }
}
