<?php

namespace App\Services;

use App\Models\MasterMMRDataAR;
use App\Models\MasterMMRDataHL;
use App\Models\MasterMMRDataQM;
use App\Models\MasterMMRDataSL;
use App\Models\MasterMMRDataTL;
use App\Models\MasterMMRDataUD;
use App\Models\Replay;
use App\Support\ApiParameters;

/**
 * A player's current rating per game type — the summary the old API's
 * `/Player/MMR` returned.
 *
 * The site has no page for this, so there is no controller to delegate to: its own
 * MMR page shows the rating *history*, which is a different question. The query is
 * therefore written here rather than borrowed.
 *
 * Ratings live in one table per game type (`master_mmr_data_qm` and its siblings),
 * each row a player-and-type pairing. `type_value` selects what is being rated:
 * the account overall, one hero, or one role.
 */
class PlayerMmrService
{
    /** One ratings table per game type. */
    private const TABLES = [
        'qm' => MasterMMRDataQM::class,
        'ud' => MasterMMRDataUD::class,
        'hl' => MasterMMRDataHL::class,
        'tl' => MasterMMRDataTL::class,
        'sl' => MasterMMRDataSL::class,
        'ar' => MasterMMRDataAR::class,
    ];

    /** `mmr_type_ids` row naming an account-wide rating rather than a hero or role. */
    private const OVERALL = 'player';

    /** What `getRankTiers()` calls the account-wide tier breakdown. */
    private const OVERALL_TIER_TYPE = 10000;

    private const RECENT_DAYS = 90;

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * @param  array<int, string>  $gameTypes  short names; every type when empty
     * @return array<string, array<string, mixed>> keyed by game type display name
     */
    public function summary(
        int $blizzId,
        int $region,
        array $gameTypes = [],
        ?string $subject = null,
        bool $extended = false
    ): array {
        $typeValue = $this->globalDataService->getMMRTypeValue($subject ?? self::OVERALL);

        if ($typeValue === null) {
            return [];
        }

        $wanted = $gameTypes === [] ? ApiParameters::GAME_TYPES : $gameTypes;
        $summary = [];

        foreach ($wanted as $shortName) {
            $model = self::TABLES[$shortName] ?? null;

            if ($model === null) {
                continue;
            }

            $typeId = $this->globalDataService->getGameTypeFilterValues($shortName);

            $row = $model::query()
                ->filterByType($typeValue)
                ->filterByGametype($typeId)
                ->filterByBlizzID($blizzId)
                ->filterByRegion($region)
                ->first();

            if ($row === null) {
                continue;
            }

            $summary[$this->displayName($shortName)] = $this->row($row, $typeId, $blizzId, $region, $typeValue, $extended);
        }

        return $summary;
    }

    /** @return array<string, mixed> */
    private function row(object $row, int $typeId, int $blizzId, int $region, int $typeValue, bool $extended): array
    {
        // The same formula the site uses in six other places. Kept as a literal
        // rather than a constant because that is where every other copy lives, and
        // one of seven being different is worse than seven being the same.
        $mmr = (int) round(1800 + 40 * $row->conservative_rating);

        $data = ['mmr' => $mmr];

        if ($extended) {
            $data['conservative_rating'] = $row->conservative_rating;
            $data['mean'] = $row->mean;
            $data['standard_deviation'] = $row->standard_deviation;
        }

        $data['games_played'] = (int) $row->win + (int) $row->loss;
        $data['games_played_last_90_days'] = $this->recentGames($typeId, $blizzId, $region);
        $data['league_tier'] = $this->leagueTier($typeId, $mmr, $typeValue);

        return $data;
    }

    /**
     * Games in the last 90 days, which `master_mmr_data_*` cannot answer — those
     * rows carry lifetime totals with no notion of when the games happened.
     */
    private function recentGames(int $typeId, int $blizzId, int $region): int
    {
        return Replay::join('player', 'player.replayID', '=', 'replay.replayID')
            ->where('game_type', $typeId)
            ->where('blizz_id', $blizzId)
            ->where('region', $region)
            ->whereDate('game_date', '>', now()->subDays(self::RECENT_DAYS))
            ->count();
    }

    private function leagueTier(int $typeId, int $mmr, int $typeValue): ?string
    {
        $tiers = $this->globalDataService->getRankTiers(
            $typeId,
            $typeValue === $this->globalDataService->getMMRTypeValue(self::OVERALL)
                ? self::OVERALL_TIER_TYPE
                : $typeValue
        );

        return $this->globalDataService->calculateSubTier($tiers, $mmr);
    }

    /**
     * Keyed by display name — `Storm League`, not `sl` — as the old API's response
     * was, so a porting caller reads the same keys.
     */
    private function displayName(string $shortName): string
    {
        return $this->globalDataService->getGameTypes()
            ->firstWhere('short_name', $shortName)?->name ?? $shortName;
    }
}
