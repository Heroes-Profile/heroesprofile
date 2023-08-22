<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\SeasonGameVersion;

class TimeframeMinorInputValidation implements Rule
{
    public function passes($attribute, $value)
    {
        // Fetch the existing game_versions from the database
        $existingVersions = SeasonGameVersion::pluck('game_version')->toArray();

        // Find the values that exist in the database
        $validVersions = array_intersect($value, $existingVersions);

        if (empty($validVersions)) {
            // If no valid versions are found, return the latest version as default
            $latestVersion = SeasonGameVersion::latest('game_version')->value('game_version');
            return [$latestVersion];
        }

        return array_values($validVersions);
    }

    public function message()
    {
        return 'The selected :attribute contains invalid or missing timeframes.';
    }
}
