<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /** Every timestamp the seeders write, so a database built today matches one built any other day. */
    private const SEEDED_AT = '2024-02-01 00:00:00';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Carbon::setTestNow(Carbon::parse(self::SEEDED_AT, 'UTC'));

        try {
            $this->seedTables();
        } finally {
            Carbon::setTestNow();
        }
    }

    private function seedTables(): void
    {
        // Rows like game_types 0 (Custom) and league_tiers 0 (wood) keep their 0 instead of taking the next auto-increment id
        DB::statement("SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");

        $this->call([
            ApiEndpointRegistrySeeder::class,
            ApiAccountsSeeder::class,
            AbilityLinksSeeder::class,
            AwardsTableSeeder::class,
            HeaderAlertSeeder::class,
            BattletagsTableSeeder::class,
            CompositionsTableSeeder::class,
            GameTypesSeeder::class,
            GlobalCompositionsSeeder::class,
            GlobalHeroDraftOrderTableSeeder::class,
            GlobalHeroDraftOrderSummarySeeder::class,
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

            // Patch 2.55.12.94786: the oldest matchup talents accepts
            SeasonGameVersionsPatch94786Seeder::class,
            GlobalHeroStatsPatch94786Seeder::class,
            GlobalHeroStatsBansPatch94786Seeder::class,
            GlobalHeroTalentsPatch94786Seeder::class,
            GlobalHeroTalentsDetailsPatch94786Seeder::class,
            GlobalHeroMatchupsAllyPatch94786Seeder::class,
            GlobalHeroMatchupsEnemyPatch94786Seeder::class,
            GlobalHeroTalentsWithHeroesPatch94786Seeder::class,
            GlobalHeroTalentsVersusHeroesPatch94786Seeder::class,

            // Esports: each league's latest 40 replays
            BattletagsNGSSeeder::class,
            TeamsNGSSeeder::class,
            StandingsNGSSeeder::class,
            ReplayNGSSeeder::class,
            PlayerNGSSeeder::class,
            ScoresNGSSeeder::class,
            TalentsNGSSeeder::class,
            ReplayBansNGSSeeder::class,

            BattletagsCCLSeeder::class,
            PlayersListCCLSeeder::class,
            MasterMmrDataCCLSeeder::class,
            TeamsCCLSeeder::class,
            ScoresCCLSeeder::class,
            TalentsCCLSeeder::class,
            ReplayBansCCLSeeder::class,

            BattletagsMCLSeeder::class,
            TeamsMCLSeeder::class,
            ReplayMCLSeeder::class,
            PlayerMCLSeeder::class,
            ScoresMCLSeeder::class,
            TalentsMCLSeeder::class,
            ReplayBansMCLSeeder::class,

            BattletagsHISeeder::class,
            TeamsHISeeder::class,
            ReplayHISeeder::class,
            PlayerHISeeder::class,
            ScoresHISeeder::class,
            TalentsHISeeder::class,
            ReplayBansHISeeder::class,

            BattletagsHINCSeeder::class,
            TeamsHINCSeeder::class,
            ReplayHINCSeeder::class,
            PlayerHINCSeeder::class,
            ScoresHINCSeeder::class,
            TalentsHINCSeeder::class,
            ReplayBansHINCSeeder::class,

            SeriesMLSeeder::class,
            RegionsMLSeeder::class,
            BattletagsMLSeeder::class,
            TeamsMLSeeder::class,
            ReplayMLSeeder::class,
            PlayerMLSeeder::class,
            ScoresMLSeeder::class,
            TalentsMLSeeder::class,
            ReplayBansMLSeeder::class,
            ReplayDraftOrderMLSeeder::class,

            BattletagsNutCupSeeder::class,
            ReplayNutCupSeeder::class,
            PlayerNutCupSeeder::class,
            ScoresNutCupSeeder::class,
            TalentsNutCupSeeder::class,
            ReplayBansNutCupSeeder::class,
        ]);
    }
}
