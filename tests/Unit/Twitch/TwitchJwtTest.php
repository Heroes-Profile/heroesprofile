<?php

namespace Tests\Unit\Twitch;

use App\Services\Twitch\TwitchJwt;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

class TwitchJwtTest extends TestCase
{
    private const CURRENT = 'current-secret-bytes-0123456789abcdef';

    private const PREVIOUS = 'previous-secret-bytes-0123456789abcdef';

    public function test_it_accepts_a_token_signed_with_the_current_secret(): void
    {
        $claims = $this->jwt()->verify($this->token(self::CURRENT));

        $this->assertSame('12345', $claims['channel_id']);
        $this->assertSame('viewer', $claims['role']);
    }

    public function test_it_still_accepts_the_previous_secret_during_a_rotation(): void
    {
        $this->assertNotNull($this->jwt()->verify($this->token(self::PREVIOUS)));
    }

    public function test_it_rejects_a_token_signed_with_anything_else(): void
    {
        $this->assertNull($this->jwt()->verify($this->token('someone-elses-secret-0123456789abcdef')));
    }

    public function test_it_rejects_an_expired_token(): void
    {
        $this->assertNull($this->jwt()->verify($this->token(self::CURRENT, ['exp' => time() - 120])));
    }

    public function test_it_rejects_a_token_without_a_channel(): void
    {
        $this->assertNull($this->jwt()->verify($this->token(self::CURRENT, ['channel_id' => null])));
    }

    public function test_it_rejects_garbage(): void
    {
        $this->assertNull($this->jwt()->verify('not.a.jwt'));
    }

    public function test_external_tokens_are_signed_with_the_current_secret_and_verify(): void
    {
        $token = $this->jwt()->signExternal('999');

        $claims = (array) JWT::decode($token, new Key(self::CURRENT, 'HS256'));

        $this->assertSame('external', $claims['role']);
        $this->assertSame('999', $claims['channel_id']);
        $this->assertSame(['broadcast'], (array) $claims['pubsub_perms']->send);
        $this->assertLessThanOrEqual(time() + 60, $claims['exp']);
    }

    private function jwt(): TwitchJwt
    {
        return new TwitchJwt([base64_encode(self::CURRENT), base64_encode(self::PREVIOUS)], '555');
    }

    private function token(string $secret, array $overrides = []): string
    {
        $claims = array_filter(array_merge([
            'exp' => time() + 300,
            'opaque_user_id' => 'U123',
            'channel_id' => '12345',
            'role' => 'viewer',
        ], $overrides), fn ($value) => $value !== null);

        return JWT::encode($claims, $secret, 'HS256');
    }
}
