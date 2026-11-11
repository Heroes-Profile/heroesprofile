<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Api\ApiAccount;
use App\Services\Api\BattlenetPlayerLink;
use App\Services\Api\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The Player Analytics section of the API portal's account page. Any paid plan (Stripe, or a
 * Patreon pledge that earns a tier) unlocks Player Analytics for one linked Battle.net account,
 * in one region. Each Battle.net account can be linked to one API account at most. Without a link
 * of its own, the Battle.net account connected for the Twitch extension is used.
 */
class AnalyticsLinkController extends Controller
{
    private const SECTION_URL = '/Api/Account#analytics';

    private const CALLBACK = '/Api/Analytics/Battlenet/Callback';

    /** @return array<string, mixed> */
    public static function section(ApiAccount $account): array
    {
        $channel = $account->twitchChannel;

        return [
            'battletag' => $account->analytics_battletag,
            'region_name' => BattlenetPlayerLink::REGIONS[$account->analytics_region] ?? null,
            'region' => $account->analytics_region,
            // Used when there's no link here: the player connected in the Twitch extension section
            'twitch_battletag' => $channel?->blizz_id ? $channel->battletag : null,
            'twitch_region_name' => $channel?->blizz_id ? (BattlenetPlayerLink::REGIONS[$channel->region] ?? null) : null,
            'regions' => BattlenetPlayerLink::REGIONS,
            'paid_plan' => app(PlanService::class)->holdsPaidPlan($account),
        ];
    }

    public function redirectToBattlenet(Request $request, BattlenetPlayerLink $battlenet)
    {
        $validated = $request->validate([
            'region' => ['required', 'integer', 'in:'.implode(',', array_keys(BattlenetPlayerLink::REGIONS))],
        ]);

        $request->session()->put('analytics_battlenet_region', (int) $validated['region']);

        return $battlenet->redirect(self::CALLBACK);
    }

    public function handleBattlenetCallback(Request $request, BattlenetPlayerLink $battlenet)
    {
        $region = $request->session()->pull('analytics_battlenet_region');

        if ($region === null) {
            return redirect(self::SECTION_URL)->withErrors(['analytics' => 'Battle.net sign-in did not complete. Please try again.']);
        }

        $player = $battlenet->player($request, self::CALLBACK, $region);

        if (is_string($player)) {
            return redirect(self::SECTION_URL)->withErrors(['analytics' => $player]);
        }

        $account = $this->account();
        $taken = ApiAccount::where('analytics_blizz_id', $player['blizz_id'])
            ->where('analytics_region', $region)
            ->where('id', '!=', $account->id)
            ->exists();

        if ($taken) {
            return redirect(self::SECTION_URL)->withErrors([
                'analytics' => "{$player['battletag']} is already linked to another API account. Unlink it there first.",
            ]);
        }

        $account->forceFill([
            'analytics_blizz_id' => $player['blizz_id'],
            'analytics_region' => $region,
            'analytics_battletag' => $player['battletag'],
        ])->save();

        return redirect(self::SECTION_URL)->with('analytics_status', 'Battle.net account linked.');
    }

    public function unlink()
    {
        $this->account()->forceFill([
            'analytics_blizz_id' => null,
            'analytics_region' => null,
            'analytics_battletag' => null,
        ])->save();

        return response()->json(['unlinked' => true]);
    }

    private function account(): ApiAccount
    {
        return Auth::guard('api_web')->user();
    }
}
