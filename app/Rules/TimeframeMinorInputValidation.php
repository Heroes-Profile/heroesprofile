<?php

namespace App\Rules;

use App\Models\SeasonGameVersion;
use Illuminate\Contracts\Validation\Rule;

class TimeframeMinorInputValidation implements Rule
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

        if ($this->timeframeType === 'minor') {
            $existingVersions = SeasonGameVersion::pluck('game_version')->toArray();
            $invalidVersions = array_diff($value, $existingVersions);
            if (! empty($invalidVersions)) {
                return false;
            }
        } elseif ($this->timeframeType === 'major') {
            foreach ($value as $timeframeValue) {
                $matchingVersions = SeasonGameVersion::where('game_version', 'like', trim($timeframeValue).'%')->count();
                if ($matchingVersions === 0) {
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return 'The selected game versions are invalid.';
    }
}
