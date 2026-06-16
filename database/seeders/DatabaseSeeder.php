<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AwardsTableSeeder::class,
            BattletagsTableSeeder::class,
            CompositionsTableSeeder::class,
            GameTypesSeeder::class,
            GlobalCompositionsSeeder::class,
            GlobalHeroDraftOrderTableSeeder::class,
            GlobalHeroMatchupsAllySeeder::class,
            GlobalHeroMatchupsEnemyTableSeeder::class,
            GlobalHeroStackSizeSeeder::class,
            GlobalHeroStatsBansTableSeeder::class,
            GlobalHeroStatsTableSeeder::class,
            TalentCombinationsSeeder::class,
            GlobalHeroTalentsDetailsSeeder::class,
            GlobalHeroTalentsTableSeeder::class,
            GlobalHeroTalentsVersusHeroesSeeder::class,
            GlobalHeroTalentsWithHeroesSeeder::class,
            HeroesDataAbilitiesSeeder::class,
            HeroesDataTalentsSeeder::class,
            HeroesSeeder::class,
            HeroesTranslationsSeeder::class,
            LeaderboardSeeder::class,
            LeagueBreakdownsSeeder::class,
            LeagueTiersSeeder::class,
            MapsSeeder::class,
            MapsTranslationsSeeder::class,
            MasterGamesPlayedDataSeeder::class,
            MasterGamesPlayedDataGroupsSeeder::class,
            MasterMmrDataArSeeder::class,
            MasterMmrDataCuSeeder::class,
            MasterMmrDataHlSeeder::class,
            MasterMmrDataQmSeeder::class,
            MasterMmrDataSlSeeder::class,
            MasterMmrDataTlSeeder::class,
            MasterMmrDataUdSeeder::class,
            MatchPredictionSeasonSeeder::class,
            MmrTypeIdsSeeder::class,
            ReplaySeeder::class,
            PlayerSeeder::class,
            ReplayBansSeeder::class,
            ReplayDraftOrderSeeder::class,
            ReplayExperienceBreakdownBlobSeeder::class,
            ScoresSeeder::class,
            TalentsSeeder::class,
            ReplayCCLSeeder::class,
            PlayerCCLSeeder::class,
            SeasonDatesSeeder::class,
            SeasonGameVersionsSeeder::class,
        ]);
    }
}
