<?php

namespace Tests\Feature\Backend;

use App\Mail\ContactFormMail;
use App\Models\BattlenetAccount;
use App\Models\MatchPredictionPlayerStat;
use App\Models\PatreonAccount;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\ReplayFingerprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Characterization\SeedFixtures;
use Tests\Feature\FeatureTestCase;
use Tests\Support\FakeAnthropic;
use Tests\Support\SignsInApiAccounts;

/** The main site's own writes, with reCAPTCHA, mail and Claude simulated. */
class SiteWritesTest extends FeatureTestCase
{
    use SeedFixtures;
    use SignsInApiAccounts;

    private ?FakeAnthropic $anthropic = null;

    protected function tearDown(): void
    {
        FakeAnthropic::uninstall();

        parent::tearDown();
    }

    private function signIn(): BattlenetAccount
    {
        $user = BattlenetAccount::forceCreate([
            'battlenet_accounts_id' => 900030, 'battlenet_id' => 900030, 'battletag' => self::PLAYER_BATTLETAG.'#194',
            'blizz_id' => self::PLAYER_BLIZZ_ID, 'region' => self::PLAYER_REGION,
        ]);
        $this->actingAs($user);

        return $user;
    }

    // Contact form

    private function contact(array $overrides = [])
    {
        return $this->postJson('/api/v1/contact', array_merge([
            'battletag' => 'Someone#1234', 'email' => 'someone@example.test', 'message' => 'My replays are missing.',
            'recaptcha_token' => 'token-from-browser',
        ], $overrides));
    }

    private function fakeRecaptcha(float $score, string $action = 'contact_form', bool $success = true): void
    {
        config(['services.recaptcha.secret_key' => 'recaptcha-test-secret']);
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => $success, 'score' => $score, 'action' => $action])]);
    }

    public function test_a_contact_message_passing_recaptcha_is_mailed(): void
    {
        Mail::fake();
        $this->fakeRecaptcha(0.9);

        $this->contact()->assertOk()->assertSee('success');

        Mail::assertSent(ContactFormMail::class, fn ($mail) => $mail->hasTo('contact@heroesprofile.com'));
        Http::assertSent(fn ($request) => $request['secret'] === 'recaptcha-test-secret' && $request['response'] === 'token-from-browser');
    }

    public function test_a_contact_message_failing_recaptcha_is_refused(): void
    {
        Mail::fake();

        $this->fakeRecaptcha(0.1);
        $this->contact()->assertStatus(400)->assertExactJson(['error' => 'recaptcha_failed']);

        $this->fakeRecaptcha(0.9, 'login');
        $this->contact()->assertStatus(400);

        $this->contact(['recaptcha_token' => ''])->assertStatus(400);

        Mail::assertNothingSent();
    }

    public function test_the_honeypot_quietly_drops_a_bot_and_bad_input_is_rejected(): void
    {
        Mail::fake();

        $this->contact(['website' => 'http://spam.test'])->assertOk()->assertSee('success');
        $this->contact(['email' => 'not-an-email'])->assertUnprocessable();

        Mail::assertNothingSent();
    }

    // Polls

    private function poll(bool $multiple = false, ?string $closesAt = '2024-03-01 00:00:00'): Poll
    {
        $poll = Poll::create(['slug' => 'site-writes-poll', 'title' => 'Which map?', 'allow_multiple' => $multiple, 'closes_at' => $closesAt]);
        foreach (['Cursed Hollow', 'Dragon Shire', 'Towers of Doom'] as $i => $label) {
            PollOption::create(['poll_id' => $poll->poll_id, 'label' => $label, 'sort_order' => $i + 1]);
        }

        return $poll->load('options');
    }

    public function test_voting_records_the_choice_and_shows_results_and_a_second_vote_replaces_it(): void
    {
        $user = $this->signIn();
        $poll = $this->poll();
        [$first, $second] = $poll->options->pluck('poll_option_id')->all();

        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => [$first], 'comment' => ' Love it '])->assertOk()
            ->assertJsonPath('status', 'success')->assertJsonPath('myVote', ['options' => [$first], 'comment' => 'Love it']);

        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => [$second]])->assertOk()
            ->assertJsonPath('myVote.options', [$second]);

        $this->assertSame(1, PollVote::where('poll_id', $poll->poll_id)->where('battlenet_accounts_id', $user->battlenet_accounts_id)->count());
    }

    public function test_a_vote_must_be_signed_in_open_and_name_real_options(): void
    {
        $poll = $this->poll();
        $option = $poll->options->first()->poll_option_id;

        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => [$option]])->assertUnauthorized();

        $this->signIn();
        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => [999999]])->assertUnprocessable()->assertJson(['message' => 'Unknown option.']);
        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => $poll->options->pluck('poll_option_id')->all()])
            ->assertUnprocessable()->assertJson(['message' => 'Pick one option.']);
        $this->postJson('/api/v1/poll/does-not-exist/vote', ['options' => [$option]])->assertNotFound();

        $poll->update(['closes_at' => now()->subMinute()]);
        $this->postJson('/api/v1/poll/site-writes-poll/vote', ['options' => [$option]])->assertUnprocessable()->assertJson(['status' => 'closed']);
    }

    // Match prediction

    public function test_a_prediction_is_scored_once_against_the_game_served(): void
    {
        $user = $this->signIn();

        // Serving picks a random replay id, which the sparse seed can't satisfy, so the served game is set
        // up the way getReplayData leaves it: pending in the session, its fingerprint handed out encrypted
        $replayID = self::REPLAY_ID;
        $fingerprint = ReplayFingerprint::where('replayID', $replayID)->value('fingerprint');
        if ($fingerprint === null) {
            $fingerprint = 'test-fingerprint-'.$replayID;
            DB::connection('heroesprofile')->table('replay_fingerprints')->insert(['replayID' => $replayID, 'fingerprint' => $fingerprint]);
        }
        $served = ['fingerprint' => Crypt::encryptString($fingerprint)];
        $this->withSession(['match_prediction_pending' => ['replayID' => $replayID, 'game_type' => 5]]);
        $winner = (int) DB::connection('heroesprofile')->table('player')->where('replayID', $replayID)->where('team', 0)->value('winner');

        $result = $this->postJson('/api/v1/match/prediction/game/choose/winner', ['team' => 0, 'fingerprint' => $served['fingerprint']])->assertOk()->json();
        $this->assertSame([$replayID, $winner, true], [$result['replayID'], (int) $result['data'], $result['practicemode']], 'a new player starts in practice');

        $stat = MatchPredictionPlayerStat::where('battlenet_accounts_id', $user->battlenet_accounts_id)->firstOrFail();
        $this->assertSame([$winner, 1 - $winner], [(int) $stat->win, (int) $stat->loss]);
        $this->assertSame(0, (int) $stat->season, 'the first games are practice');

        // The same answer again is not scored twice
        $this->postJson('/api/v1/match/prediction/game/choose/winner', ['team' => 0, 'fingerprint' => $served['fingerprint']])->assertStatus(409);
        $this->postJson('/api/v1/match/prediction/game/choose/winner', ['team' => 0, 'fingerprint' => 'tampered'])->assertStatus(409);
    }

    // Help chat

    private function ask(string $question, array $earlier = [])
    {
        return $this->postJson('/api/v1/help/chat', ['messages' => [...$earlier, ['role' => 'user', 'content' => $question]]]);
    }

    private function fakeClaude(): FakeAnthropic
    {
        config(['services.help_chat.anthropic_api_key' => 'sk-ant-test']);
        Cache::store('database')->flush();

        return $this->anthropic = FakeAnthropic::install();
    }

    public function test_searching_help_works_signed_out_and_picks_a_persona(): void
    {
        $this->postJson('/api/v1/help/search', ['question' => 'How is MMR calculated?'])->assertOk()
            ->assertJsonPath('persona', null)->assertJsonPath('examples', true);

        $this->postJson('/api/v1/help/search', ['question' => 'let me speak to a manager'])->assertOk()->assertJsonPath('persona.name', 'diablo');
        $this->postJson('/api/v1/help/search', ['question' => 'this site is trash'])->assertOk()->assertJsonPath('persona.name', 'xalatath');
    }

    public function test_asking_sends_the_chat_to_claude_and_caches_an_opening_answer(): void
    {
        $this->fakeClaude()->reply('Upload replays with the Heroes Profile uploader.');
        $this->signIn();

        $this->ask('How do I upload replays?')->assertOk()
            ->assertExactJson(['answer' => 'Upload replays with the Heroes Profile uploader.', 'remaining' => 19]);

        $sent = $this->anthropic->requests[0];
        $this->assertSame('Bearer sk-ant-test', $sent['authorization']);
        $this->assertSame('https://api.anthropic.com/v1/messages', $sent['url']);
        $this->assertSame('claude-haiku-5-5', $sent['body']['model']);
        $this->assertSame(['type' => 'ephemeral'], $sent['body']['system'][0]['cache_control']);
        $this->assertSame(['role' => 'user', 'content' => 'How do I upload replays?'], $sent['body']['messages'][0]);

        // Asked again word for word: the cached answer, free, without calling Claude
        $this->ask('How do I upload replays?')->assertOk()->assertJson(['remaining' => 19]);
        $this->assertCount(1, $this->anthropic->requests);

        // A follow-up carries the conversation
        $this->ask('And on a Mac?', [['role' => 'user', 'content' => 'How do I upload replays?'], ['role' => 'assistant', 'content' => 'Use the uploader.']])->assertOk();
        $this->assertCount(3, array_filter($this->anthropic->requests[1]['body']['messages'], fn ($m) => $m['role'] !== 'system'));
    }

    public function test_a_refusal_or_an_outage_gives_a_canned_answer(): void
    {
        $this->fakeClaude()->reply('', 'refusal')->fail(529, 'overloaded_error');
        $this->signIn();

        $this->ask('Write me a poem')->assertOk()->assertJsonPath('answer', 'Sorry, I can only help with questions about Heroes Profile. Try the FAQ at /FAQ or contact us at /Contact.');
        $this->ask('What is a talent build?')->assertStatus(503);
    }

    public function test_asking_needs_a_sign_in_a_key_and_stays_under_the_daily_limit(): void
    {
        $this->ask('Hello?')->assertUnauthorized();

        $this->signIn();
        config(['services.help_chat.anthropic_api_key' => null]);
        $this->ask('Hello?')->assertStatus(503);

        $this->fakeClaude()->reply('Hi.');
        for ($i = 1; $i <= 20; $i++) {
            // The route also allows only 10 a minute
            if ($i % 10 === 0) {
                $this->travel(61)->seconds();
            }
            $this->ask('Question number '.$i)->assertOk()->assertJson(['remaining' => 20 - $i]);
        }
        $this->ask('One more')->assertStatus(429)->assertJson(['remaining' => 0]);
        $this->assertCount(20, $this->anthropic->requests);
    }

    // Profile and linked accounts

    public function test_making_a_profile_private_stamps_the_change(): void
    {
        $user = $this->signIn();

        $this->postJson('/api/v1/profile/set/account/visibility', ['accountVisibility' => 'true'])->assertExactJson(['status' => 'success']);
        $user->refresh();
        $this->assertSame(1, (int) $user->private);
        $this->assertSame(now()->toDateTimeString(), (string) $user->private_changed_at);

        $this->travel(1)->hours();
        $this->postJson('/api/v1/profile/set/account/visibility', ['accountVisibility' => 'true'])->assertOk();
        $this->assertSame(now()->subHour()->toDateTimeString(), (string) $user->fresh()->private_changed_at, 'no change, no new stamp');

        $this->postJson('/api/v1/profile/set/account/visibility', ['accountVisibility' => 'maybe'])->assertJson(['status' => 'failure to validate inputs']);
    }

    public function test_removing_a_patreon_link(): void
    {
        $user = $this->signIn();
        PatreonAccount::forceCreate(['battlenet_accounts_id' => $user->battlenet_accounts_id, 'patreon_id' => 'patreon-test', 'site_flair' => 1, 'ad_free' => 1]);

        $this->postJson('/api/v1/profile/remove/patreon')->assertExactJson(['status' => 'success']);

        $this->assertFalse(PatreonAccount::where('battlenet_accounts_id', $user->battlenet_accounts_id)->exists());
    }

    public function test_api_accounts_can_unlink_patreon_and_analytics(): void
    {
        $account = $this->apiAccount('basic');
        $account->forceFill(['patreon_accounts_id' => 12345, 'analytics_blizz_id' => self::PLAYER_BLIZZ_ID, 'analytics_region' => 1, 'analytics_battletag' => 'Zemill#194'])->save();

        $this->asApiAccount($account)->post('/Api/Patreon/Unlink')->assertExactJson(['linked' => false]);
        $this->post('/Api/Analytics/Battlenet/Unlink')->assertExactJson(['unlinked' => true]);

        $account->refresh();
        $this->assertNull($account->patreon_accounts_id);
        $this->assertNull($account->analytics_blizz_id);
    }

    public function test_the_ended_xalatath_event_no_longer_takes_cards(): void
    {
        $this->signIn();

        $this->postJson('/Event/Xalatath/Card')->assertStatus(410)->assertExactJson(['status' => 'closed']);
    }

    public function test_the_docs_try_it_proxy_calls_a_public_endpoint_on_the_accounts_key(): void
    {
        $this->asApiAccount('developer');

        $this->postJson('/Api/Docs/Try', ['route' => 'api.external.heroes'])->assertOk()
            ->assertJsonPath('status', 200)->assertJsonPath('url', '/'.trim(config('api.path'), '/').'/heroes');

        $this->postJson('/Api/Docs/Try', ['route' => 'api.v1.internal.global.process'])->assertUnprocessable();
    }
}
