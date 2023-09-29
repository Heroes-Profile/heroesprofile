<?php

namespace App\Rules;

use App\Models\SeasonGameVersion;
use Illuminate\Contracts\Validation\Rule;

class TimeframeMinorInputValidationOutputID implements Rule
{
    public function passes($attribute, $value)
    {
        // Fetch the existing game_versions from the database along with their ids
        $existingVersions = SeasonGameVersion::pluck('id', 'game_version')->toArray();

        // Find the ids of the values that exist in the database
        $validVersionIds = array_keys(array_intersect($existingVersions, $value));

        if (empty($validVersionIds)) {
            // If no valid versions are found, return the latest version id as default
            $latestVersionId = SeasonGameVersion::latest('game_version')->value('id');
            return [$latestVersionId];
        }

        return $validVersionIds;
    }

    public function message()
    {
        return 'The selected :attribute contains invalid or missing timeframes.';
    }
}
