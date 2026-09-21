<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Api\TwitchChannel;
use App\Services\Twitch\TwitchEntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Admin console: Twitch extension channels.
 *
 * Hiding a listing takes a channel off the public streamer page and nothing else.
 * Suspending switches the extension off for the channel. Neither touches the API
 * account or its billing — those have their own enforcement ladder.
 */
class TwitchAdminController extends Controller
{
    public function index(Request $request, TwitchEntitlementService $entitlements)
    {
        $search = trim((string) $request->query('q', ''));

        $channels = TwitchChannel::with('account')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('twitch_login', 'like', '%'.$search.'%')
                    ->orWhere('battletag', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('uploader_last_seen_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (TwitchChannel $channel) => $this->present($channel, $entitlements))
            ->all();

        return response()->json(['channels' => $channels]);
    }

    public function setListingHidden(Request $request, int $id, TwitchEntitlementService $entitlements)
    {
        $validated = $request->validate([
            'hidden' => ['required', 'boolean'],
            'reason' => ['required_if:hidden,true', 'nullable', 'string', 'max:255'],
        ]);

        $channel = TwitchChannel::findOrFail($id);

        $channel->forceFill($validated['hidden']
            ? ['listing_hidden_at' => now(), 'listing_hidden_reason' => $validated['reason']]
            : ['listing_hidden_at' => null, 'listing_hidden_reason' => null]
        )->save();

        $this->refreshDirectory();

        return response()->json(['channel' => $this->present($channel, $entitlements)]);
    }

    public function setSuspended(Request $request, int $id, TwitchEntitlementService $entitlements)
    {
        $validated = $request->validate([
            'suspended' => ['required', 'boolean'],
            'reason' => ['required_if:suspended,true', 'nullable', 'string', 'max:255'],
        ]);

        $channel = TwitchChannel::findOrFail($id);

        $channel->forceFill($validated['suspended']
            ? ['suspended_at' => now(), 'suspension_reason' => $validated['reason']]
            : ['suspended_at' => null, 'suspension_reason' => null]
        )->save();

        $this->refreshDirectory();

        return response()->json(['channel' => $this->present($channel, $entitlements)]);
    }

    /** Free access until a date, for partners and the like. Null removes it. */
    public function setComp(Request $request, int $id, TwitchEntitlementService $entitlements)
    {
        $validated = $request->validate([
            'comped_until' => ['nullable', 'date', 'after:today'],
        ]);

        $channel = TwitchChannel::findOrFail($id);
        $channel->forceFill(['comped_until' => $validated['comped_until'] ?? null])->save();

        $this->refreshDirectory();

        return response()->json(['channel' => $this->present($channel, $entitlements)]);
    }

    /** @return array<string, mixed> */
    private function present(TwitchChannel $channel, TwitchEntitlementService $entitlements): array
    {
        try {
            $entitlement = $entitlements->for($channel)->toArray();
        } catch (Throwable $e) {
            report($e);
            $entitlement = null;
        }

        return [
            'id' => $channel->id,
            'twitch_login' => $channel->twitch_login,
            'email' => $channel->account?->email,
            'api_account_id' => $channel->user_id,
            'battletag' => $channel->battletag,
            'uploader_last_seen_at' => $channel->uploader_last_seen_at?->toDateTimeString(),
            'trial_ends_at' => $channel->trial_ends_at?->toDateString(),
            'comped_until' => $channel->comped_until?->toDateString(),
            'listing_opt_in' => $channel->listing_opt_in,
            'listing_hidden' => $channel->listing_hidden_at !== null,
            'listing_hidden_reason' => $channel->listing_hidden_reason,
            'suspended' => $channel->suspended_at !== null,
            'suspension_reason' => $channel->suspension_reason,
            'entitlement' => $entitlement,
        ];
    }

    /** A moderation change should reach the public page now, not in three minutes. */
    private function refreshDirectory(): void
    {
        Cache::forget('twitch_directory');
    }
}
