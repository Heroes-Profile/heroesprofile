<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Models\Api\TwitchChannel;
use App\Services\Twitch\TwitchEntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\Config;
use Throwable;

/**
 * The Twitch extension section of the API portal's account page.
 *
 * A streamer connects the Twitch channel the extension runs on, connects the
 * Battle.net account they play on (so their team is shown first), generates an
 * uploader key, and sets their delay and listing preferences. Access comes from
 * any API plan, so billing stays on the existing billing page.
 *
 * Both OAuth callbacks are their own URLs and must be registered as extra redirect
 * URIs on the Twitch extension app and the Blizzard app respectively. The Battle.net
 * one only records the player — unlike the main site's, it never signs anyone in.
 */
class TwitchController extends Controller
{
    private const REGIONS = [1 => 'NA', 2 => 'EU', 3 => 'KR', 5 => 'CN'];

    /** The Twitch section of the account page, where both sign-in flows return. */
    private const SECTION_URL = '/Api/Account#twitch';

    /**
     * What the Twitch section of the account page starts with. The section lives on
     * /Api/Account rather than a page of its own; AccountController calls this.
     *
     * @return array<string, mixed>
     */
    public static function section(ApiAccount $account, TwitchEntitlementService $entitlements): array
    {
        $channel = $account->twitchChannel;

        return [
            'channel' => $channel ? self::present($channel, $entitlements) : null,
            'regions' => self::REGIONS,
            'max_delay' => (int) config('twitch.max_delay'),
        ];
    }

    public function redirectToTwitch()
    {
        return Socialite::driver('twitch')
            ->redirectUrl($this->twitchCallbackUrl())
            ->redirect();
    }

    public function handleTwitchCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect(self::SECTION_URL)->withErrors(['twitch' => 'Twitch sign-in was cancelled.']);
        }

        try {
            $twitchUser = Socialite::driver('twitch')
                ->redirectUrl($this->twitchCallbackUrl())
                ->user();
        } catch (InvalidStateException $e) {
            Log::error('InvalidStateException linking Twitch to an API account: '.$e->getMessage());

            return redirect(self::SECTION_URL)->withErrors(['twitch' => 'That Twitch sign-in did not complete. Please try again.']);
        }

        if (empty($twitchUser->id) || empty($twitchUser->nickname)) {
            return redirect(self::SECTION_URL)->withErrors(['twitch' => 'Twitch did not tell us which channel you signed in with. Please try again.']);
        }

        $account = $this->account();
        $twitchUserId = (string) $twitchUser->id;

        $existing = TwitchChannel::where('twitch_user_id', $twitchUserId)->first();

        // One channel, one API account. The row itself is kept forever, so a
        // channel that was unlinked comes back with its trial already used.
        if ($existing && $existing->user_id !== null && $existing->user_id !== $account->id) {
            return redirect(self::SECTION_URL)->withErrors([
                'twitch' => 'That Twitch channel is already connected to a different Heroes Profile API account. '
                    .'Sign in to that account and disconnect it there first. '
                    .'If you no longer have access to it, email zemill@heroesprofile.com.',
            ]);
        }

        DB::connection('heroesprofile_api')->transaction(function () use ($account, $existing, $twitchUser, $twitchUserId) {
            // Switching channels releases the old one rather than deleting it.
            TwitchChannel::where('user_id', $account->id)
                ->where('twitch_user_id', '!=', $twitchUserId)
                ->update(['user_id' => null, 'uploader_key_hash' => null, 'uploader_key_last4' => null, 'listing_opt_in' => false]);

            $channel = $existing ?? new TwitchChannel(['twitch_user_id' => $twitchUserId]);

            $channel->fill([
                'user_id' => $account->id,
                'twitch_login' => $twitchUser->nickname,
                'twitch_display_name' => $twitchUser->name,
            ])->save();

            $account->forceFill(['twitch_extension' => 1])->save();
        });

        return redirect(self::SECTION_URL)->with('twitch_status', 'Twitch channel connected.');
    }

    public function redirectToBattlenet(Request $request)
    {
        $validated = $request->validate([
            'region' => ['required', 'integer', 'in:'.implode(',', array_keys(self::REGIONS))],
        ]);

        if (! $this->account()->twitchChannel) {
            return redirect(self::SECTION_URL)->withErrors(['battlenet' => 'Connect your Twitch channel first.']);
        }

        $request->session()->put('twitch_battlenet_region', (int) $validated['region']);

        return Socialite::driver('battlenet')
            ->setConfig($this->battlenetConfig())
            ->redirect();
    }

    public function handleBattlenetCallback(Request $request)
    {
        $region = $request->session()->pull('twitch_battlenet_region');
        $channel = $this->account()->twitchChannel;

        if ($request->filled('error') || ! $request->filled('code') || $region === null || ! $channel) {
            return redirect(self::SECTION_URL)->withErrors(['battlenet' => 'Battle.net sign-in did not complete. Please try again.']);
        }

        try {
            $user = Socialite::driver('battlenet')->setConfig($this->battlenetConfig())->user();
        } catch (InvalidStateException $e) {
            return redirect(self::SECTION_URL)->withErrors(['battlenet' => 'Battle.net sign-in did not complete. Please try again.']);
        }

        $blizzId = $this->globalDataService->getBlizzIDGivenFullBattletag($user->nickname, $region);

        if (! $blizzId) {
            return redirect(self::SECTION_URL)->withErrors([
                'battlenet' => "We have no games for {$user->nickname} in ".self::REGIONS[$region].' yet. '
                    .'Upload a replay from that region and try again, or pick the region you play in.',
            ]);
        }

        $channel->forceFill([
            'blizz_id' => $blizzId,
            'region' => $region,
            'battletag' => $user->nickname,
        ])->save();

        return redirect(self::SECTION_URL)->with('twitch_status', 'Battle.net account connected.');
    }

    /** Shown once. The previous key stops working immediately. */
    public function rotateKey(TwitchEntitlementService $entitlements)
    {
        $channel = $this->channelOrFail();

        return response()->json([
            'key' => $channel->rotateUploaderKey(),
            'channel' => self::present($channel->refresh(), $entitlements),
        ]);
    }

    public function saveSettings(Request $request, TwitchEntitlementService $entitlements)
    {
        $validated = $request->validate([
            'delay_seconds' => ['required', 'integer', 'min:0', 'max:'.(int) config('twitch.max_delay')],
            'show_stats' => ['required', 'boolean'],
        ]);

        $channel = $this->channelOrFail();

        // Takes effect from the next snapshot: each one is scheduled with the
        // delay in force when it arrives.
        $channel->forceFill($validated)->save();

        return response()->json(['channel' => self::present($channel, $entitlements)]);
    }

    /**
     * Appearing on the public streamer page. Opting in requires accepting the
     * current streamer code of conduct; the version accepted is recorded, so a
     * change to the rules asks everyone listed to accept again.
     */
    public function saveListing(Request $request, TwitchEntitlementService $entitlements)
    {
        $validated = $request->validate([
            'listing_opt_in' => ['required', 'boolean'],
            'accept_terms' => ['required_if:listing_opt_in,true', 'boolean'],
        ]);

        $channel = $this->channelOrFail();

        if ($validated['listing_opt_in'] && ! ($validated['accept_terms'] ?? false)) {
            return response()->json(['error' => 'Accept the streamer code of conduct to be listed.'], 422);
        }

        $channel->forceFill($validated['listing_opt_in']
            ? [
                'listing_opt_in' => true,
                'listing_terms_version' => (int) config('twitch.listing_terms_version'),
                'listing_terms_accepted_at' => now(),
            ]
            : ['listing_opt_in' => false]
        )->save();

        // Show the change on the public page now rather than at the next refresh.
        Cache::forget('twitch_directory');

        return response()->json(['channel' => self::present($channel, $entitlements)]);
    }

    /** Releases the channel. The row, and with it the used trial, is kept. */
    public function unlink()
    {
        $account = $this->account();
        $channel = $this->channelOrFail();

        $channel->forceFill([
            'user_id' => null,
            'uploader_key_hash' => null,
            'uploader_key_last4' => null,
            'listing_opt_in' => false,
        ])->save();

        $account->forceFill(['twitch_extension' => 0])->save();
        Cache::forget('twitch_directory');

        return response()->json(['channel' => null]);
    }

    /** @return array<string, mixed> */
    private static function present(TwitchChannel $channel, TwitchEntitlementService $entitlements): array
    {
        try {
            $entitlement = $entitlements->for($channel)->toArray();
        } catch (Throwable $e) {
            report($e);
            $entitlement = null;
        }

        return [
            'twitch_login' => $channel->twitch_login,
            'twitch_display_name' => $channel->twitch_display_name,
            'battletag' => $channel->battletag,
            'region' => $channel->region,
            'region_name' => self::REGIONS[$channel->region] ?? null,
            'has_key' => $channel->uploader_key_hash !== null,
            'key_last4' => $channel->uploader_key_last4,
            'uploader_last_seen_at' => $channel->uploader_last_seen_at?->toIso8601String(),
            'delay_seconds' => $channel->delay_seconds,
            'show_stats' => $channel->show_stats,
            'trial_started_at' => $channel->trial_started_at?->toIso8601String(),
            'trial_ends_at' => $channel->trial_ends_at?->toIso8601String(),
            'listing_opt_in' => $channel->listing_opt_in,
            'listing_terms_current' => $channel->listing_terms_version === (int) config('twitch.listing_terms_version'),
            'listing_hidden' => $channel->listing_hidden_at !== null,
            'suspended' => $channel->suspended_at !== null,
            'suspension_reason' => $channel->suspension_reason,
            'entitlement' => $entitlement,
        ];
    }

    private function channelOrFail(): TwitchChannel
    {
        $channel = $this->account()->twitchChannel;

        abort_if($channel === null, 404, 'Connect a Twitch channel first.');

        return $channel;
    }

    private function account(): ApiAccount
    {
        return Auth::guard('api_web')->user();
    }

    private function battlenetConfig(): Config
    {
        return new Config(
            config('services.battlenet.client_id'),
            config('services.battlenet.client_secret'),
            url('/Api/Twitch/Battlenet/Callback'),
            ['region' => 'us'],
        );
    }

    private function twitchCallbackUrl(): string
    {
        return url('/Api/Twitch/Callback');
    }
}
