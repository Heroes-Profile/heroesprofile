<?php

namespace App\Services\Api;

use Illuminate\Support\Collection;

/**
 * Per-account markers in global stats, so data that turns up somewhere else can
 * be traced to the key that pulled it.
 *
 * A row is identified by what it describes (a hero, talent, map, composition...),
 * never by its position, so the same row is marked whichever filters, patch or
 * endpoint it comes back under. About one row in RATE is picked per account, and
 * its `win_rate` moves by 0.05-0.15. The same account always gets the same nudge
 * on the same row; another account gets a different set.
 */
class PoisonPill
{
    private const RATE = 25;

    private const FIELD = 'win_rate';

    private const LEVELS = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

    /**
     * @param  array<int, array{identity: string, label: string, real: float, sent: float}>  $marks
     *                                                                                               Filled with every value changed.
     */
    public function apply(mixed $payload, int $accountId, array &$marks = []): mixed
    {
        if (! is_array($payload)) {
            return $payload;
        }

        foreach ($payload as $key => $value) {
            $payload[$key] = $this->apply($value, $accountId, $marks);
        }

        if (! array_key_exists(self::FIELD, $payload) || ! is_numeric($payload[self::FIELD])) {
            return $payload;
        }

        $identity = $this->identity($payload);
        $delta = $identity === null ? null : $this->delta($accountId, $identity);

        if ($delta === null) {
            return $payload;
        }

        $original = (float) $payload[self::FIELD];

        // 0 and 100 are exact by nature, and 100.1 gives itself away.
        if ($original <= 0 || $original >= 100) {
            return $payload;
        }

        $marked = round(min(99.99, max(0.01, $original + $delta)), 2);
        $payload[self::FIELD] = is_string($payload[self::FIELD]) ? (string) $marked : $marked;

        $marks[] = ['identity' => $identity, 'label' => $this->label($payload, $identity), 'real' => $original, 'sent' => $marked];

        return $payload;
    }

    /** The nudge this account gets on a row, or null when the row is left alone. */
    public function delta(int $accountId, string $identity): ?float
    {
        $hash = hexdec(substr(hash('sha256', $accountId.'|'.$identity), 0, 12));

        if ($hash % self::RATE !== 0) {
            return null;
        }

        $hash = intdiv($hash, self::RATE);
        $size = ($hash % 11 + 5) / 100;

        return intdiv($hash, 11) % 2 === 0 ? $size : -$size;
    }

    /**
     * Heroes this account sees nudged, for the admin console.
     *
     * @param  Collection<int, object>  $heroes  id and name
     * @return array<int, array{hero: string, delta: float}>
     */
    public function heroMarkers(int $accountId, Collection $heroes): array
    {
        return $heroes
            ->map(fn ($hero) => ['hero' => $hero->name, 'delta' => $this->delta($accountId, 'hero:'.$hero->id)])
            ->filter(fn (array $row) => $row['delta'] !== null)
            ->sortBy('hero')
            ->values()
            ->all();
    }

    /** What the console shows for a row. The filters it came under are logged beside it. */
    private function label(array $row, string $identity): string
    {
        if (str_starts_with($identity, 'build:')) {
            return trim(($row['hero']['name'] ?? '').' build '.substr($identity, 6));
        }

        $label = $row['talentInfo']['title'] ?? $row['title'] ?? $row['battletag'] ?? $row['stack_size_name']
            ?? $row['map']['name'] ?? $row['hero']['name'] ?? $row['name'] ?? null;

        if (str_starts_with($identity, 'pick:')) {
            $label = 'Pick '.substr($identity, 5);
        } elseif (str_starts_with($identity, 'composition:')) {
            $label = 'Composition '.substr($identity, 12);
        }

        return mb_substr(is_string($label) && $label !== '' ? $label : $identity, 0, 150);
    }

    /** Most specific first: a talent row carries its hero too. */
    private function identity(array $row): ?string
    {
        $builds = array_filter(array_map(fn ($level) => $row[$level]['talent_id'] ?? null, self::LEVELS));

        if ($builds !== []) {
            return 'build:'.implode('-', $builds);
        }

        if ($talent = $row['talent_id'] ?? $row['talentInfo']['talent_id'] ?? $row['talent'] ?? null) {
            return 'talent:'.$talent;
        }

        if (isset($row['composition_id'])) {
            return 'composition:'.$row['composition_id'];
        }

        if (isset($row['map']['map_id'])) {
            return 'map:'.$row['map']['map_id'];
        }

        if (isset($row['pick_number'])) {
            return 'pick:'.$row['pick_number'];
        }

        if (isset($row['ally_combo'], $row['enemy_combo'])) {
            return 'party:'.$row['ally_combo'].'|'.$row['enemy_combo'];
        }

        if (isset($row['blizz_id'])) {
            return 'player:'.$row['blizz_id'];
        }

        $hero = $row['hero_id'] ?? $row['hero']['id'] ?? (is_numeric($row['hero'] ?? null) ? $row['hero'] : null);

        return $hero === null ? null : 'hero:'.$hero;
    }
}
