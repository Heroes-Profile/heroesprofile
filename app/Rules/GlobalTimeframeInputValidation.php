<?php

namespace App\Rules;

use App\Services\GlobalDataService;
use Illuminate\Contracts\Validation\Rule;

/**
 * Only the timeframes the filter dropdowns offer this user, and no more than one
 * major patch of them, or the newest two while the newest is still new.
 */
class GlobalTimeframeInputValidation implements Rule
{
    protected $timeframeType;

    private bool $tooWide = false;

    /** @param  ?string  $minimumPatch  The page's own floor. Null for the default one. */
    public function __construct($timeframeType, private ?string $minimumPatch = null)
    {
        $this->timeframeType = $timeframeType;
    }

    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
            $value = explode(',', $value);
        }

        $filters = app(GlobalDataService::class)->getFilterData($this->minimumPatch !== null, $this->minimumPatch);

        $options = match ($this->timeframeType) {
            'minor' => $filters->timeframes,
            'major' => $filters->timeframes_grouped,
            'major_grouped' => $filters->timeframes_sub_grouped,
            default => null,
        };

        if ($options === null) {
            return false;
        }

        $allowed = collect($options)->pluck('code')->all();

        if (array_diff($value, $allowed) !== []) {
            return false;
        }

        $this->tooWide = ! self::withinOneMajorPatch($value, $filters->combinable_major_patches ?? []);

        return ! $this->tooWide;
    }

    /**
     * Whether every timeframe sits in the same major patch, e.g. 2.55. Holds for
     * builds, sub patches and major patches alike, so a `major` selection is one patch.
     *
     * @param  array<int, string>  $timeframes
     * @param  array<int, string>  $combinable  Major patches allowed together. See
     *                                          `GlobalDataService::combinableMajorPatches()`.
     */
    public static function withinOneMajorPatch(array $timeframes, array $combinable = []): bool
    {
        $majors = [];

        foreach ($timeframes as $timeframe) {
            $majors[implode('.', array_slice(explode('.', trim((string) $timeframe)), 0, 2))] = true;
        }

        return count($majors) === 1 || array_diff(array_keys($majors), $combinable) === [];
    }

    public function message()
    {
        if ($this->tooWide) {
            return 'The selected timeframes must all be within one major patch.';
        }

        return 'The selected game versions are invalid.';
    }
}
