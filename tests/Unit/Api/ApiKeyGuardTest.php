<?php

namespace Tests\Unit\Api;

use App\Auth\ApiKeyContext;
use App\Auth\ApiKeyGuard;
use App\Models\Api\ApiAccount;
use App\Services\Api\ApiKeyResolver;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class ApiKeyGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_null_when_no_key_is_present(): void
    {
        $resolver = Mockery::mock(ApiKeyResolver::class);
        $resolver->shouldNotReceive('resolve');

        $guard = new ApiKeyGuard($resolver);

        $this->assertNull($guard(Request::create('/v1/maps')));
    }

    public function test_it_prefers_the_bearer_token_over_the_query_string(): void
    {
        $context = $this->context();

        $resolver = Mockery::mock(ApiKeyResolver::class);
        $resolver->shouldReceive('resolve')->once()->with('hp_bearer_key')->andReturn($context);

        $request = Request::create('/v1/maps', 'GET', ['api_token' => 'hp_query_key']);
        $request->headers->set('Authorization', 'Bearer hp_bearer_key');

        $account = (new ApiKeyGuard($resolver))($request);

        $this->assertSame($context->account, $account);
    }

    public function test_it_falls_back_to_the_query_string(): void
    {
        $context = $this->context();

        $resolver = Mockery::mock(ApiKeyResolver::class);
        $resolver->shouldReceive('resolve')->once()->with('hp_query_key')->andReturn($context);

        $request = Request::create('/v1/maps', 'GET', ['api_token' => 'hp_query_key']);

        $this->assertSame($context->account, (new ApiKeyGuard($resolver))($request));
    }

    public function test_it_attaches_the_resolved_context_to_the_request(): void
    {
        $context = $this->context();

        $resolver = Mockery::mock(ApiKeyResolver::class);
        $resolver->shouldReceive('resolve')->andReturn($context);

        $request = Request::create('/v1/maps', 'GET', ['api_token' => 'hp_query_key']);
        (new ApiKeyGuard($resolver))($request);

        $this->assertSame($context, $request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE));
    }

    public function test_it_returns_null_when_the_key_does_not_resolve(): void
    {
        $resolver = Mockery::mock(ApiKeyResolver::class);
        $resolver->shouldReceive('resolve')->andReturn(null);

        $request = Request::create('/v1/maps', 'GET', ['api_token' => 'revoked']);
        $guard = new ApiKeyGuard($resolver);

        $this->assertNull($guard($request));
        $this->assertNull($request->attributes->get(ApiKeyGuard::REQUEST_ATTRIBUTE));
    }

    public function test_entitlement_requires_an_active_subscription_or_a_comped_flag(): void
    {
        $this->assertTrue($this->context(active: true, comped: false)->isEntitled());
        $this->assertTrue($this->context(active: false, comped: true)->isEntitled());
        $this->assertFalse($this->context(active: false, comped: false)->isEntitled());
    }

    private function context(bool $active = true, bool $comped = false): ApiKeyContext
    {
        return new ApiKeyContext(
            account: new ApiAccount(['name' => 'Test', 'email' => 'test@example.com']),
            keyId: 1,
            planId: 2,
            planName: 'intermediate',
            subscriptionActive: $active,
            comped: $comped,
        );
    }
}
