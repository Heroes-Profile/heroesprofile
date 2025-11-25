<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\SeasonGameVersion;
use App\Rules\GameMapInputValidation;
use App\Rules\GameTypeInputValidation;
use App\Rules\HeroInputValidation;
use App\Rules\HeroLevelInputValidation;
use App\Rules\RegionInputValidation;
use App\Rules\RoleInputValidation;
use App\Rules\StatFilterInputValidation;
use App\Rules\TierInputByIDValidation;
use App\Rules\TierInputByNameValidation;
use App\Rules\TimeframeMinorInputValidation;

class GlobalsInputValidationController extends Controller
{
    public function globalValidationRulesURLParam($timeframeType, $timeframe)
    {
        return [
            'timeframe_type' => 'sometimes|in:minor,major,major_grouped,last_update',
            'timeframe' => ['sometimes', 'nullable', new TimeframeMinorInputValidation($timeframeType)],
            'game_type' => ['sometimes', 'nullable', new GameTypeInputValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByNameValidation],
            'mirror' => 'sometimes|in:null,0,1',
            'minimum_games' => 'sometimes|nullable|integer',
        ];
    }

    public function globalsValidationRules($timeframeType, $timeframe)
    {
        return [
            'timeframe_type' => 'required|in:minor,major,major_grouped,last_update',
            'timeframe' => $timeframeType !== 'last_update' ? ['required', new TimeframeMinorInputValidation($timeframeType)] : 'nullable',
            'game_type' => ['required', new GameTypeInputValidation],
            'region' => ['sometimes', 'nullable', new RegionInputValidation],
            'statfilter' => ['sometimes', 'nullable', new StatFilterInputValidation($timeframeType, $timeframe)],
            'hero_level' => ['sometimes', 'nullable', new HeroLevelInputValidation],
            'hero' => ['sometimes', 'nullable', new HeroInputValidation],
            'role' => ['sometimes', 'nullable', new RoleInputValidation],
            'game_map' => ['sometimes', 'nullable', new GameMapInputValidation],
            'league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'hero_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'role_league_tier' => ['sometimes', 'nullable', new TierInputByIDValidation],
            'mirror' => 'sometimes|in:null,0,1',
        ];
    }

    /**
     * Split game versions by their ID from season_game_versions table
     * Versions with ID >= threshold go to new table, others go to old table
     *
     * @param  array  $gameVersions  Array of game version strings
     * @param  int  $idThreshold  The ID threshold (default 250)
     * @return array [oldTableVersions, newTableVersions]
     */
    protected function splitGameVersionsByPatch($gameVersions, $idThreshold = 250)
    {
        $oldTableVersions = [];
        $newTableVersions = [];

        // Look up all game versions in the season_game_versions table to get their IDs
        $versionIds = SeasonGameVersion::whereIn('game_version', $gameVersions)
            ->pluck('id', 'game_version')
            ->toArray();

        foreach ($gameVersions as $version) {
            try {
                // Check if we have an ID for this version
                if (isset($versionIds[$version])) {
                    $versionId = $versionIds[$version];
                    if ($versionId >= $idThreshold) {
                        $newTableVersions[] = $version;
                    } else {
                        $oldTableVersions[] = $version;
                    }
                } else {
                    // If version not found in table, default to old table
                    $oldTableVersions[] = $version;
                }
            } catch (\Exception $e) {
                // If anything fails, default to old table
                $oldTableVersions[] = $version;
            }
        }

        return [$oldTableVersions, $newTableVersions];
    }
}
