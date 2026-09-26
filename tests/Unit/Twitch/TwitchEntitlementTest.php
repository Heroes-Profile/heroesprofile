<?php

namespace Tests\Unit\Twitch;

use App\Services\Twitch\TwitchEntitlement;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class TwitchEntitlementTest extends TestCase
{
    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = CarbonImmutable::parse('2026-09-21 12:00:00');
    }

    public function test_any_api_plan_grants_access(): void
    {
        $result = $this->decide(planName: 'Basic');

        $this->assertTrue($result->isActive());
        $this->assertSame(TwitchEntitlement::PAID, $result->source);
        $this->assertSame('Basic', $result->planName);
    }

    public function test_a_plan_wins_over_a_running_trial(): void
    {
        $result = $this->decide(planName: 'Intermediate', trialStartedAt: $this->now->subDays(3), trialEndsAt: $this->now->addDays(27));

        $this->assertSame(TwitchEntitlement::PAID, $result->source);
    }

    public function test_a_channel_that_never_sent_a_game_is_pending_and_active(): void
    {
        $result = $this->decide();

        $this->assertSame(TwitchEntitlement::PENDING, $result->source);
        $this->assertTrue($result->isActive());
    }

    public function test_a_running_trial_is_active_until_it_ends(): void
    {
        $result = $this->decide(trialStartedAt: $this->now->subDays(10), trialEndsAt: $this->now->addDays(20));

        $this->assertSame(TwitchEntitlement::TRIAL, $result->source);
        $this->assertTrue($result->isActive());
    }

    public function test_an_ended_trial_with_no_plan_is_inactive(): void
    {
        $result = $this->decide(trialStartedAt: $this->now->subDays(31), trialEndsAt: $this->now->subDay());

        $this->assertSame(TwitchEntitlement::INACTIVE, $result->source);
        $this->assertFalse($result->isActive());
    }

    public function test_a_comp_grants_access_until_its_date(): void
    {
        $this->assertSame(TwitchEntitlement::COMP, $this->decide(
            compedUntil: $this->now->addMonth(), trialStartedAt: $this->now->subDays(40), trialEndsAt: $this->now->subDays(10),
        )->source);

        $this->assertSame(TwitchEntitlement::INACTIVE, $this->decide(
            compedUntil: $this->now->subDay(), trialStartedAt: $this->now->subDays(40), trialEndsAt: $this->now->subDays(10),
        )->source);
    }

    public function test_a_suspended_channel_is_off_even_when_paying(): void
    {
        $result = $this->decide(planName: 'Developer', channelSuspendedAt: $this->now->subHour());

        $this->assertSame(TwitchEntitlement::SUSPENDED, $result->source);
        $this->assertFalse($result->isActive());
    }

    public function test_a_suspended_api_account_turns_the_extension_off(): void
    {
        $this->assertFalse($this->decide(planName: 'Basic', accountSuspended: true)->isActive());
    }

    public function test_an_unlinked_channel_is_inactive(): void
    {
        $this->assertFalse($this->decide(accountLinked: false)->isActive());
    }

    private function decide(
        bool $accountLinked = true,
        bool $accountSuspended = false,
        ?string $planName = null,
        ?CarbonImmutable $channelSuspendedAt = null,
        ?CarbonImmutable $compedUntil = null,
        ?CarbonImmutable $trialStartedAt = null,
        ?CarbonImmutable $trialEndsAt = null,
    ): TwitchEntitlement {
        return TwitchEntitlement::decide(
            $accountLinked, $accountSuspended, $planName, $channelSuspendedAt,
            $compedUntil, $trialStartedAt, $trialEndsAt, $this->now,
        );
    }
}
