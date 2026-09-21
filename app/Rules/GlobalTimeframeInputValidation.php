<?php

namespace App\Rules;

use App\Services\GlobalDataService;
use Illuminate\Contracts\Validation\Rule;

/**
 * Only the timeframes the filter dropdowns offer this user.
 */
class GlobalTimeframeInputValidation implements Rule
{
    protected $timeframeType;

    public function __construct($timeframeType)
    {
        $this->timeframeType = $timeframeType;
    }

    public function passes($attribute, $value)
    {
        if (! is_array($value)) {
            $value = explode(',', $value);
        }

        $filters = app(GlobalDataService::class)->getFilterData();

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

        return array_diff($value, $allowed) === [];
    }

    public function message()
    {
        return 'The selected game versions are invalid.';
    }
}
