<?php

namespace Tests\Feature\Characterization;

/** Values that exist in the seeded test database. */
trait SeedFixtures
{
    // Best-covered player in PlayerSeeder (1,213 games)
    public const PLAYER_BATTLETAG = 'Zemill';

    public const PLAYER_BLIZZ_ID = 67280;

    public const PLAYER_REGION = 1;

    public const REPLAY_ID = 52026698;

    // Global stats fixtures
    public const HERO = 'Abathur';

    public const HERO_LEVEL_ONE_TALENT = 2421;

    // The seeded replays on GAME_VERSION are all ARAM; Gazlowe took this level 1 talent in 16 of them
    public const BUILDER_REPLAYS_HERO = 'Gazlowe';

    public const BUILDER_REPLAYS_TALENT = 5102;

    // The seeded player's highest kill count in one game
    public const PLAYER_MAX_KILLS = 21;

    // The seeded player's most-played hero
    public const PLAYER_HERO = 'Deckard';

    public const PLAYER_HERO_ID = 80;

    public const MAP = 'Cursed Hollow';

    public const GAME_VERSION = '2.55.4.91418';

    // Oldest patch matchup talents accepts; seeded for Abathur and Auriel only
    public const MATCHUP_TALENTS_GAME_VERSION = '2.55.12.94786';

    // Esports slices (each league's latest 40 replays)
    public const NGS_SEASON = 9;

    public const NGS_DIVISION = 'A East';

    public const NGS_TEAM = 'ParsleySnagRosemary&Line';

    public const NGS_PLAYER_BATTLETAG = 'Linehouse#1400';

    public const NGS_PLAYER_BLIZZ_ID = 621300;

    public const NGS_REPLAY_ID = 4781;

    public const CCL_SEASON = 4;

    public const CCL_TEAM = '30k';

    public const MCL_SEASON = 1;

    public const MCL_TEAM = '30k';

    public const HI_SEASON = 1;

    public const HI_TEAM = '30k';

    public const NUTCUP_SEASON = 2;

    public const OTHER_SERIES = 'HGC';

    public const OTHER_SERIES_ID = 9;
}
