<?php

namespace App\Http\Controllers\Global\Concerns;

use App\Models\SeasonGameVersion;

// Change values come from CalculateChange, which only covers one unfiltered patch + game type, and skips Unranked Draft.
trait ResolvesStatChange
{
    private function checkIfChange($timeframe, $region, $game_type, $map, $league_tier, $hero_league_tier, $role_league_tier, $hero_level)
    {
        $timeframe = (array) $timeframe;
        $game_type = (array) $game_type;

        if (
            count($timeframe) == 1 &&
            count($game_type) == 1 &&
            $game_type[0] != 2 &&
            empty($region) &&
            empty($map) &&
            empty($league_tier) &&
            empty($hero_league_tier) &&
            empty($role_league_tier) &&
            empty($hero_level)
        ) {
            return true;
        }

        return false;
    }

    private function calculateGameVersionsForHeroChange($version)
    {
        try {
            [$major, $minor, $patch, $build] = explode('.', $version[0]);
            $major = (int) $major;
            $minor = (int) $minor;
            $patch = (int) $patch;
            $build = (int) $build;

            $previousVersion = SeasonGameVersion::where('valid_globals', 1)
                ->where(function ($query) use ($major, $minor, $patch, $build) {
                    $query->where('major', '<', $major)
                        ->orWhere(function ($q) use ($major, $minor) {
                            $q->where('major', '=', $major)
                                ->where('minor', '<', $minor);
                        })
                        ->orWhere(function ($q) use ($major, $minor, $patch) {
                            $q->where('major', '=', $major)
                                ->where('minor', '=', $minor)
                                ->where('patch', '<', $patch);
                        })
                        ->orWhere(function ($q) use ($major, $minor, $patch, $build) {
                            $q->where('major', '=', $major)
                                ->where('minor', '=', $minor)
                                ->where('patch', '=', $patch)
                                ->where('build', '<', $build);
                        });
                })
                ->orderByDesc('major')
                ->orderByDesc('minor')
                ->orderByDesc('patch')
                ->orderByDesc('build')
                ->first();

            return $previousVersion ? [$previousVersion->game_version] : ['2.55.3.90670'];
        } catch (\Exception $e) {
            return ['2.55.3.90670'];
        }
    }
}
