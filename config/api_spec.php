<?php

/*
|--------------------------------------------------------------------------
| External API specification
|--------------------------------------------------------------------------
|
| What `php artisan api:build-spec` cannot read off the route table.
|
| Paths, methods and grouping come from the routes; response schemas come from
| the fixtures. Only the parameters live here, because the rules that define them
| are built at runtime inside the delegated controllers — `Validator::make($request
| ->all(), $rules)` — where nothing can inspect them.
|
| Every routed endpoint needs an entry. The command fails if one is missing, so
| this file cannot silently fall behind the routes.
|
| Per endpoint: `summary`, `uses` to pull in a shared parameter set, `parameters`
| to add or override, `async` for the endpoints that can answer 202, and
| `responses` only where a response is not JSON and so cannot come from a fixture.
|
*/

return [

    'info' => [
        'title' => 'Heroes Profile API',
        'version' => '1.0.0',
        'description' => 'Heroes of the Storm statistics, replay data, talent builds and player profiles.',
    ],

    /*
    | One server, deliberately. `api.heroesprofile.com` serves the old API until it is
    | retired and then redirects here — it is never a second base URL, so listing it
    | would point generated clients at a host that redirects rather than answers.
    |
    | Keep in step with `api.path` in `config/api.php`.
    */

    'servers' => [
        ['url' => 'https://www.heroesprofile.com/api/external/v1', 'description' => 'Production'],
    ],

    /*
    | `?mode=csv` is applied by ConvertResponseToCsv to every endpoint answering
    | JSON, so it is documented once here rather than repeated on all fifty.
    */
    'csv' => [
        'enum' => ['json', 'csv'],
        'description' => 'Response format. `csv` returns the same data as a downloadable file, flattened to one row per record.',
        'example' => 'csv',
    ],

    /*
    | Endpoints `mode` does not apply to: a file download and two that answer plain
    | text on purpose, which deployed clients string-compare.
    */
    'csv_exempt' => [
        'api.external.replay.download',
        'api.external.replays.parsed',
        'api.external.replays.fingerprint',
        'api.external.prematch',
        'api.external.upload',
        'api.external.ngs.games.upload',
        'api.external.ngs.games.delete',
    ],

    /*
    | Shared by every global statistics endpoint, from
    | `GlobalsInputValidationController::globalsValidationRules()`.
    */
    'globals' => [
        'timeframe_type' => [
            'required' => true,
            'enum' => ['minor', 'major', 'major_grouped'],
            'description' => 'How `timeframe` is read. `minor` is a build, `major` a patch, `major_grouped` several patches together.',
            'example' => 'minor',
        ],
        'timeframe' => [
            'required' => true,
            'multi' => true,
            'description' => 'One patch or build, or several comma-separated.',
            'example' => '2.55.17.97771',
        ],
        'game_type' => [
            'required' => true,
            'multi' => true,
            'enum' => ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'],
            'description' => 'Game type, by short name or display name — `sl` and `Storm League` both work, case-insensitive. Comma-separated for several.',
            'example' => 'Storm League',
        ],
        'region' => ['multi' => true, 'description' => 'Region, by name or id — `NA` and `1` both work. NA/1, EU/2, KR/3, CN/5.', 'example' => 'NA'],
        'hero' => ['description' => 'Hero name.', 'example' => 'Anduin'],
        'role' => ['description' => 'Role name.', 'example' => 'Healer'],
        'game_map' => ['multi' => true, 'description' => 'Playable map name, case-insensitive, or several comma-separated. Every name must be recognised.', 'example' => 'Alterac Pass'],
        'hero_level' => ['multi' => true, 'description' => 'Hero level band, not a level. One of the band codes — see Variables. Every code must be recognised.', 'example' => '25'],
        'league_tier' => ['multi' => true, 'description' => 'Player league tier id. Every id must be recognised.'],
        'hero_league_tier' => ['multi' => true, 'description' => 'Hero league tier id. Every id must be recognised.'],
        'role_league_tier' => ['multi' => true, 'description' => 'Role league tier id. Every id must be recognised.'],
        'mirror' => ['enum' => ['0', '1'], 'description' => 'Include mirror matches.'],
        'groupsize' => ['multi' => true, 'enum' => ['Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party sizes to report on, comma-separated for several. Omit for every game regardless of party size, which is also the only form carrying ban and win rate change data.'],
        'statfilter' => [
            'enum' => ['win_rate', 'game_time', 'kills', 'takedowns', 'deaths', 'siege_damage', 'hero_damage', 'healing', 'damage_taken', 'experience_contribution', 'assists', 'highest_kill_streak', 'structure_damage', 'minion_damage', 'creep_damage', 'summon_damage', 'self_healing', 'town_kills', 'time_spent_dead', 'merc_camp_captures', 'watch_tower_captures', 'protection_Allies', 'silencing_enemies', 'rooting_enemies', 'stunning_enemies', 'clutch_heals', 'escapes', 'vengeance', 'outnumbered_deaths', 'teamfight_escapes', 'teamfight_healing', 'teamfight_damage_taken', 'teamfight_hero_damage', 'multikill', 'physical_damage', 'spell_damage', 'regen_globes'],
            'description' => 'Which statistic to report. Defaults to `win_rate`. Anything other than `win_rate` needs `timeframe_type=minor` and at most five timeframes.',
            'example' => 'win_rate',
        ],
    ],

    /*
    | Documentation sections, in the order they are shown.
    |
    | Grouping by URL segment puts `party` and `draft` in sections of their own
    | when they belong with the rest of the hero statistics. These buckets follow
    | the site's own navigation instead — the talent builder sits under Tools
    | there, and leaderboards stand alone — so the docs match how the pages are
    | already organised.
    |
    | Endpoints are listed alphabetically within each. Every routed endpoint must
    | appear in exactly one section, or `api:build-spec` fails.
    */
    'groups' => [

        'Reference' => [
            'api.external.heroes',
            'api.external.heroes.talents',
            'api.external.maps',
            'api.external.mmr.tier',
            'api.external.patches',
        ],

        'Global Hero Stats' => [
            'api.external.compositions',
            'api.external.compositions.heroes',
            'api.external.draft',
            'api.external.heroes.maps',
            'api.external.heroes.matchups',
            'api.external.heroes.matchups.talents',
            'api.external.heroes.stats',
            'api.external.heroes.talents.builds',
            'api.external.heroes.talents.builds.all',
            'api.external.heroes.talents.details',
            'api.external.party',
        ],

        'Player Stats' => [
            'api.external.players',
            'api.external.players.awards',
            'api.external.players.awards.games',
            'api.external.players.friendfoe',
            'api.external.players.heroes',
            'api.external.players.heroes.single',
            'api.external.players.maps',
            'api.external.players.maps.single',
            'api.external.players.matches',
            'api.external.players.matchups',
            'api.external.players.mmr',
            'api.external.players.mmr.heroes',
            'api.external.players.mmr.history',
            'api.external.players.mmr.history.heroes',
            'api.external.players.mmr.history.roles',
            'api.external.players.mmr.roles',
            'api.external.players.privacy.changes',
            'api.external.players.roles',
            'api.external.players.roles.single',
            'api.external.players.talents.build',
        ],

        'Leaderboards' => [
            'api.external.leaderboard',
        ],

        'Replays' => [
            'api.external.replay.bans',
            'api.external.replay.draft',
            'api.external.replay.show',
            'api.external.replay.download',
            'api.external.replays.index',
        ],

        'NGS' => [
            'api.external.ngs.division',
            'api.external.ngs.division.matches',
            'api.external.ngs.divisions',
            'api.external.ngs.heroes.stats',
            'api.external.ngs.heroes.talents.stats',
            'api.external.ngs.matches',
            'api.external.ngs.player',
            'api.external.ngs.player.hero',
            'api.external.ngs.player.map',
            'api.external.ngs.player.matches',
            'api.external.ngs.players.search',
            'api.external.ngs.replay',
            'api.external.ngs.standings',
            'api.external.ngs.team',
            'api.external.ngs.team.matches',
            'api.external.ngs.teams',
        ],

        'NGS Replay Upload' => [
            'api.external.ngs.games.delete',
            'api.external.ngs.games.upload',
        ],

        'Tools' => [
            'api.external.heroes.talents.builder',
            'api.external.heroes.talents.builder.replays',
            'api.external.tools.activity.players.unique',
            'api.external.tools.randomize',
        ],

        'Uploading Replays' => [
            'api.external.prematch',
            'api.external.replays.fingerprint',
            'api.external.replays.parsed',
            'api.external.upload',
        ],

        'Job Results' => [
            'api.external.jobs',
        ],

    ],

    /*
    | Descriptions for response fields, applied by name wherever they appear in a
    | derived schema.
    |
    | Schemas come from the fixtures, which carry types but no prose — so without
    | this there is nowhere to say what a number means. Units especially: a bare
    | `game_length: 727` reads as minutes to anyone who does not check.
    */
    'fields' => [
        'game_length' => 'Match length in seconds, excluding the pre-game period.',
        'avg_game_length' => 'Mean match length in seconds.',
        'max_game_length' => 'Longest match length in seconds.',
        'sum_game_length' => 'Total match length in seconds.',
        'length' => 'Match length in seconds.',
        'time_spent_dead' => 'Seconds spent dead.',
        'time_cc_enemy_heroes' => 'Seconds of crowd control applied to enemy heroes.',
        'time_on_fire' => 'Seconds spent on fire.',
        'game_date' => 'When the match was played, as `YYYY-MM-DD HH:MM:SS` UTC.',
        'replayID' => 'The match id. Pass it to `/replay/{replayID}` for full detail.',
        'blizz_id' => 'Blizzard account id. Stable per region, and not a battletag.',
        'region' => 'Region id. 1 NA, 2 EU, 3 KR, 5 CN.',
        'next_after' => 'Pass as `after` to get the following page. Null once you have caught up.',
        'max_replay_id' => 'The highest replay id stored, so you know how far there is to go.',
        'downloadable' => 'Whether the replay file is still within the retention window and can be fetched from `/download/replay`.',
        'win_rate' => 'Percentage, 0 to 100.',
        'popularity' => 'Percentage of matches in which this appeared, 0 to 100.',
        'leaderboard_group' => 'Leaderboard group, 0 = Group A. Players in a lower number always rank above players in a higher one; each group needs fewer games played than the group above it.',
        'min_games_required' => 'Games played needed to be in this leaderboard group. Null for rows calculated before groups existed.',
    ],

    /* The time window player stats cover. Seasons or dates, not both. */
    'player_dates' => [
        'season' => [
            'type' => 'integer',
            'multi' => true,
            'description' => 'Season id, or several comma-separated. Ignored if `start_date` or `end_date` is sent. See Variables for season ids.',
        ],
        'start_date' => ['description' => 'Only matches on or after this date, `YYYY-MM-DD`. Use instead of `season`.', 'example' => '2026-01-01'],
        'end_date' => ['description' => 'Only matches on or before this date, `YYYY-MM-DD`. Use instead of `season`.', 'example' => '2026-06-30'],
    ],

    /* Every player endpoint identifies its subject this way. */
    'player' => [
        'battletag' => [
            'required' => true,
            'description' => 'Full battletag including the discriminator.',
            'example' => 'Zemill#1940',
        ],
        'region' => [
            'required' => true,
            'enum' => ['NA', 'EU', 'KR', 'CN', '1', '2', '3', '5'],
            'description' => 'Region, by name or id — `NA` and `1` both work. NA/1, EU/2, KR/3, CN/5.',
            'example' => 'NA',
        ],
    ],

    /*
    | Shared by the NGS endpoints. Values for both are listed under Variables.
    */
    'ngs' => [
        'season' => ['type' => 'integer', 'description' => 'NGS season number. Defaults to the latest.'],
        'division' => ['description' => 'NGS division name, as NGS spells it. Omit for every division.'],
    ],

    /*
    | NGS players, by battletag or blizz_id. One of the two is required.
    */
    'ngs_player' => [
        'battletag' => ['description' => 'Full battletag, or the name before the `#` if only one NGS player has it. Needed unless `blizz_id` is sent.', 'example' => 'Zemill#1940'],
        'blizz_id' => ['type' => 'integer', 'description' => 'Blizzard account id, from `ngs/players/search`. Takes precedence over `battletag`.'],
    ],

    'endpoints' => [

        /*
        | Reference data. Large, slow-moving, and effectively free.
        */

        'api.external.maps' => [
            'summary' => 'Every map, with its id and rotation status.',
            'parameters' => [],
        ],

        'api.external.heroes' => [
            'summary' => 'Every hero, with role, type and release date.',
            'parameters' => [
                'hero' => ['description' => 'Restrict to one hero, by name or short name.', 'example' => 'Anduin'],
                'role' => ['description' => 'Restrict to one role, by name.', 'example' => 'Healer'],
            ],
        ],

        'api.external.heroes.talents' => [
            'summary' => 'Every talent for every hero.',
            'parameters' => [
                'hero' => ['description' => 'Restrict to one hero by name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.patches' => [
            'summary' => 'Game versions, with the season each belongs to.',
            'parameters' => [],
        ],

        'api.external.mmr.tier' => [
            'summary' => 'The league tier a rating falls in.',
            'parameters' => [
                'game_type' => ['required' => true, 'description' => 'One game type, by short name or display name — `sl` and `Storm League` both work, case-insensitive.', 'example' => 'Storm League'],
                'mmr' => ['required' => true, 'type' => 'integer', 'description' => 'The rating to place. A whole number.', 'example' => 2400],
            ],
        ],

        /*
        | Players. `hero`, `map` and `role` are always names here, never ids —
        | the wrappers translate for the controllers that want ids.
        */

        'api.external.players' => [
            'summary' => 'Profile, ratings and career totals for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'game_type' => ['multi' => true, 'description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
            ],
        ],

        'api.external.players.awards' => [
            'summary' => 'How often one player earns each end of match award, with their latest five.',
            'description' => 'Only matches after award tracking began are counted. `rate` is the percentage of those matches in which the award was earned. Pass an `award_id` from here to `players/awards/games` for every match with that award.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Awards',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.', 'example' => 'Anduin'],
                'role' => ['description' => 'Restrict to one role by name.', 'example' => 'Healer'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.awards.games' => [
            'summary' => 'Every match in which one player earned a given award, newest first.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Awards',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'award_id' => ['required' => true, 'type' => 'integer', 'description' => 'The award, by `award_id` from `players/awards`.', 'example' => 1],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results, 100 per page. Defaults to 1.'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.', 'example' => 'Anduin'],
                'role' => ['description' => 'Restrict to one role by name.', 'example' => 'Healer'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.matches' => [
            'summary' => 'Match history, with the full stat line for each game.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Match/History',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results. Defaults to 1.'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'role' => ['description' => 'Filter to one role, by name.', 'example' => 'Healer'],
                'stack_size' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size the player queued at.'],
            ],
        ],

        'api.external.players.heroes' => [
            'summary' => 'Per-hero performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Hero',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop heroes below this many games.'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.heroes.single' => [
            'summary' => 'One hero, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Hero/{hero}',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'game_map' => ['multi' => true, 'description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.maps' => [
            'summary' => 'Per-map performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Map',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop rows below this many games.'],
            ],
        ],

        'api.external.players.maps.single' => [
            'summary' => 'One map, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Map/{map}',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'map' => ['required' => true, 'description' => 'Map name.', 'example' => 'Alterac Pass'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
            ],
        ],

        'api.external.players.roles' => [
            'summary' => 'Per-role performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Role',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop rows below this many games.'],
            ],
        ],

        'api.external.players.roles.single' => [
            'summary' => 'One role, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Role/{role}',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'game_map' => ['multi' => true, 'description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.mmr' => [
            'summary' => 'Current rating per game type, with games played and league tier. What the old API returned from `/Player/MMR`.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'extra_mmr_info' => ['type' => 'boolean', 'description' => 'Adds `conservative_rating`, `mean` and `standard_deviation` to each rating.', 'example' => 'false'],
            ],
        ],

        'api.external.players.mmr.history' => [
            'summary' => 'Rating over time for the account, one entry per match.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League. A list is refused.', 'example' => 'Storm League'],
            ],
        ],

        'api.external.players.mmr.heroes' => [
            'summary' => 'Current rating on one hero, per game type.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'extra_mmr_info' => ['type' => 'boolean', 'description' => 'Adds `conservative_rating`, `mean` and `standard_deviation` to each rating.', 'example' => 'false'],
            ],
        ],

        'api.external.players.mmr.history.heroes' => [
            'summary' => 'Rating over time on one hero, one entry per match.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
            ],
        ],

        'api.external.players.mmr.roles' => [
            'summary' => 'Current rating in one role, per game type.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'extra_mmr_info' => ['type' => 'boolean', 'description' => 'Adds `conservative_rating`, `mean` and `standard_deviation` to each rating.', 'example' => 'false'],
            ],
        ],

        'api.external.players.mmr.history.roles' => [
            'summary' => 'Rating over time in one role, one entry per match.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/MMR',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
            ],
        ],

        'api.external.players.talents.build' => [
            'summary' => 'A player, most played builds on one hero.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Talents',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'game_map' => ['multi' => true, 'description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'fromdate' => ['deprecated' => true, 'description' => 'Deprecated: use `start_date`. Still accepted, and treated as `start_date` when that is not sent.', 'example' => '2024-01-01'],
            ],
        ],

        'api.external.players.matchups' => [
            'summary' => 'Opponents this player meets most, and how they fare.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Matchups',
            'uses' => ['player', 'player_dates'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.friendfoe' => [
            'summary' => 'Team-mates and opponents this player sees repeatedly.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/FriendFoe',
            'uses' => ['player', 'player_dates'],
            'async' => true,
            'parameters' => [
                'type' => ['required' => true, 'enum' => ['friend', 'enemy'], 'description' => 'Which side to report: `friend` for team-mates, `enemy` for opponents. One call answers one side.'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to every game type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'game_map' => ['multi' => true, 'description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size the player queued at. `All`, or omitted, for every party size.'],
            ],
        ],

        /*
        | The privacy feed. Not identified by battletag: it answers with whoever
        | changed, not with a player the caller named.
        */

        'api.external.players.privacy.changes' => [
            'summary' => 'Players whose profile privacy has changed, so cached copies can be purged.',
            'description' => 'Every other endpoint refuses a private player at the point of the call. This is the only way to reach a copy already stored in your own database. The terms of service require polling it at least daily and removing a player within 24 hours of them going private.

Each account appears at most once, carrying its current state — the column holds one timestamp, overwritten on each change, so this is a snapshot rather than an event log. A player who went private and public again between two polls appears once, as public.

Page with the cursor: pass `next_since` and `next_after_id` from one response as `since` and `after_id` on the next, and keep going while `has_more` is true. Both are needed because a timestamp alone cannot separate accounts that changed in the same second.',
            'parameters' => [
                'since' => ['description' => 'Only changes after this time. Omit for a first sync, which returns every account that has ever changed state.', 'example' => '2026-09-01T00:00:00+00:00'],
                'after_id' => ['type' => 'integer', 'description' => 'Tie-breaker for `since`. Pass the `next_after_id` from the previous response.'],
                'limit' => ['type' => 'integer', 'description' => 'Rows per page, 1 to 5000. Defaults to 1000.'],
            ],
        ],

        /*
        | Matches.
        */

        'api.external.replay.show' => [
            'summary' => 'Full detail for one match, including every stat line.',
            'page' => '/Match/Single/{replayID}',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        'api.external.replay.bans' => [
            'summary' => 'Hero bans for one match.',
            'page' => '/Match/Single/{replayID}',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        'api.external.replay.draft' => [
            'summary' => 'The draft for one match, in the order it happened. Every ban and pick as one sequence — a draft with its bans taken out is not a draft, so they are included here as well as on `replay/{replayID}/bans`.',
            'page' => '/Match/Single/{replayID}',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        'api.external.replays.index' => [
            'summary' => 'A page of replays, for building a local copy of the data. Paged by replay id: pass the `next_after` from one response as the `after` of the next, and stop when it comes back null.',
            'parameters' => [
                'after' => ['type' => 'integer', 'description' => 'Return replays with an id greater than this. Omit to start from the beginning.', 'example' => 0],
                'timeframe_type' => ['enum' => ['minor', 'major'], 'description' => 'How `timeframe` is read. Needs `timeframe`.'],
                'timeframe' => ['description' => 'One patch or build. Read as a build unless `timeframe_type` is `major`.', 'example' => '2.55.17.97771'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work, case-insensitive. Comma-separated for several. Omit for every type. An unrecognised value is refused.', 'example' => 'Storm League'],
                'game_map' => ['description' => 'Map names, case-insensitive, comma-separated. Omit for every map. An unrecognised name is refused.', 'example' => 'Cursed Hollow'],
            ],
        ],

        'api.external.replay.download' => [
            'summary' => 'The original .StormReplay file for a match.',
            'page' => '/Match/Single/{replayID}',
            'parameters' => [
                'replayID' => ['required' => true, 'type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
            'responses' => [
                '200' => [
                    'description' => 'The replay file.',
                    'content' => ['application/octet-stream' => ['schema' => ['type' => 'string', 'format' => 'binary']]],
                ],
                '403' => ['description' => 'Outside the retention window, or otherwise unavailable.'],
                '404' => ['description' => 'No replay with that id.'],
            ],
        ],

        /*
        | Global statistics. Each can answer 202 with a job id instead of data,
        | so each carries `async`.
        */

        'api.external.heroes.stats' => [
            'summary' => 'Win rate, popularity and ban rate for every hero.',
            'page' => '/Global/Hero',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'statfilter' => [
                    'enum' => ['win_rate', 'game_time', 'kills', 'takedowns', 'deaths', 'siege_damage', 'hero_damage', 'healing', 'damage_taken', 'experience_contribution', 'assists', 'highest_kill_streak', 'structure_damage', 'minion_damage', 'creep_damage', 'summon_damage', 'self_healing', 'town_kills', 'time_spent_dead', 'merc_camp_captures', 'watch_tower_captures', 'protection_Allies', 'silencing_enemies', 'rooting_enemies', 'stunning_enemies', 'clutch_heals', 'escapes', 'vengeance', 'outnumbered_deaths', 'teamfight_escapes', 'teamfight_healing', 'teamfight_damage_taken', 'teamfight_hero_damage', 'multikill', 'physical_damage', 'spell_damage', 'regen_globes'],
                    'description' => 'Which statistic to report. Defaults to `win_rate`. Anything other than `win_rate` needs `timeframe_type=minor` and at most five timeframes, and is ignored when `groupsize` is sent — party-size data carries win rate only.',
                    'example' => 'win_rate',
                ],
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
            ],
        ],

        'api.external.heroes.matchups' => [
            'summary' => 'How one hero performs with and against every other.',
            'page' => '/Global/Matchups',
            'uses' => ['globals'],
            // `role` narrows the matchup table; the controller reads no party-size or
            // stat filter.
            'except' => ['groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.heroes.maps' => [
            'summary' => 'One hero, win rate per map.',
            'page' => '/Global/Hero/Maps',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            // `game_map` is refused outright: the answer is already one row per map.
            'except' => ['role', 'groupsize', 'statfilter', 'game_map'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.heroes.matchups.talents' => [
            'summary' => 'Talent performance for one hero against or alongside another.',
            'page' => '/Global/Matchups/Talents',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter', 'region', 'hero_level', 'hero_league_tier', 'role_league_tier', 'mirror'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'ally_enemy' => ['required' => true, 'description' => 'The other hero, by name.', 'example' => 'Johanna'],
                'type' => ['enum' => ['Enemy', 'Ally'], 'description' => 'Whether the other hero is an opponent or a team-mate. Defaults to `Enemy`.'],
                'talent_view' => ['enum' => ['hero', 'ally_enemy'], 'description' => 'Whose talents to report. Defaults to `hero`.'],
            ],
        ],

        'api.external.heroes.talents.details' => [
            'summary' => 'Win rate and popularity for every talent.',
            'page' => '/Global/Talents',
            'uses' => ['globals'],
            // `statfilter` chooses the statistic ranked; role and party size are not read.
            'except' => ['role', 'groupsize'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.heroes.talents.builds' => [
            'summary' => 'The most played complete builds for one hero.',
            'page' => '/Global/Talents',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'talentbuildtype' => [
                    'enum' => ['Popular', 'HP Algorithm', 'Unique Lvl 1', 'Unique Lvl 4', 'Unique Lvl 7', 'Unique Lvl 10', 'Unique Lvl 13', 'Unique Lvl 16', 'Unique Lvl 20'],
                    'description' => 'Which ranking decides the builds returned. Defaults to `Popular`.',
                    'example' => 'Popular',
                ],
                'total_builds' => ['type' => 'integer', 'description' => 'How many builds to return, 1 to 25. Defaults to 7.'],
            ],
        ],

        'api.external.heroes.talents.builds.all' => [
            'summary' => 'Every hero, most played builds, in one call. Takes the same filters as `heroes/talents/builds` and answers one entry per hero — several game types are one query, not separate groupings. A hero whose query cannot be completed answers with `{"error": "..."}` in place of its builds rather than failing the whole set.',
            'page' => '/Global/Talents',
            'uses' => ['globals'],
            // `hero` is the parameter this endpoint exists in order not to need.
            // Role and party size are validated by the shared rules and read by
            // nothing here.
            'except' => ['hero', 'role', 'groupsize'],
            'async' => true,
            'parameters' => [
                // Redeclared rather than inherited from the set, which marks these
                // three required. This endpoint accepted no parameters at all until
                // the filters were added, so every one of them has to stay optional
                // or a caller relying on that would break.
                'timeframe_type' => [
                    'enum' => ['minor', 'major', 'major_grouped'],
                    'description' => 'How `timeframe` is read. `minor` is a build, `major` a patch, `major_grouped` several patches together. Defaults to `minor`.',
                    'example' => 'minor',
                ],
                'timeframe' => [
                    'multi' => true,
                    'description' => 'One patch or build, or several comma-separated. Defaults to the current patch.',
                    'example' => '2.55.17.97771',
                ],
                'game_type' => [
                    'multi' => true,
                    'enum' => ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'],
                    'description' => 'Game type, by short name or display name — `sl` and `Storm League` both work, case-insensitive. Comma-separated for several, which are counted together rather than reported apart. Defaults to `qm,sl,ar`.',
                    'example' => 'Storm League',
                ],
                'talentbuildtype' => [
                    'enum' => ['Popular', 'HP Algorithm', 'Unique Lvl 1', 'Unique Lvl 4', 'Unique Lvl 7', 'Unique Lvl 10', 'Unique Lvl 13', 'Unique Lvl 16', 'Unique Lvl 20'],
                    'description' => 'Which ranking decides the builds returned. Defaults to `Popular`.',
                    'example' => 'Popular',
                ],
                'total_builds' => ['type' => 'integer', 'description' => 'How many builds to return per hero, 1 to 25. Defaults to 7.'],
            ],
        ],

        'api.external.heroes.talents.builder' => [
            'summary' => 'Win rates for a partially chosen build, to evaluate the next talent. Call it with no talents selected and it returns the full talent list for that hero instead, which is how you get the `talent_id` values to send back.',
            'page' => '/Global/Talents/Builder',
            'uses' => ['globals'],
            // The builder page offers no role, party-size or stat filter, and the
            // controller reads none of them.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'selectedtalents[1]' => ['type' => 'integer', 'description' => 'Talent chosen at level 1. Each value is a `talent_id` from `/heroes/talents` — that endpoint lists every talent for a hero with its `talent_id` and the `level` it belongs to. Send only the levels you have picked; the rest are treated as open.'],
                'selectedtalents[4]' => ['type' => 'integer', 'description' => 'Talent chosen at level 4. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[7]' => ['type' => 'integer', 'description' => 'Talent chosen at level 7. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[10]' => ['type' => 'integer', 'description' => 'Talent chosen at level 10. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[13]' => ['type' => 'integer', 'description' => 'Talent chosen at level 13. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[16]' => ['type' => 'integer', 'description' => 'Talent chosen at level 16. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[20]' => ['type' => 'integer', 'description' => 'Talent chosen at level 20. A `talent_id` from `/heroes/talents`.'],
            ],
        ],

        'api.external.heroes.talents.builder.replays' => [
            'summary' => 'The replays behind a talent-builder result. Send at least one selected talent — with none, it returns the full talent list for that hero rather than replays. Always answers directly, never with a job.',
            'page' => '/Global/Talents/Builder',
            'uses' => ['globals'],
            // The replay query filters only by version, game type, hero, map and region.
            'except' => ['role', 'groupsize', 'statfilter', 'league_tier', 'hero_league_tier', 'role_league_tier', 'hero_level', 'mirror'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'selectedtalents[1]' => ['type' => 'integer', 'description' => 'Talent chosen at level 1. Each value is a `talent_id` from `/heroes/talents` — that endpoint lists every talent for a hero with its `talent_id` and the `level` it belongs to. Send only the levels you have picked; the rest are treated as open.'],
                'selectedtalents[4]' => ['type' => 'integer', 'description' => 'Talent chosen at level 4. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[7]' => ['type' => 'integer', 'description' => 'Talent chosen at level 7. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[10]' => ['type' => 'integer', 'description' => 'Talent chosen at level 10. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[13]' => ['type' => 'integer', 'description' => 'Talent chosen at level 13. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[16]' => ['type' => 'integer', 'description' => 'Talent chosen at level 16. A `talent_id` from `/heroes/talents`.'],
                'selectedtalents[20]' => ['type' => 'integer', 'description' => 'Talent chosen at level 20. A `talent_id` from `/heroes/talents`.'],
            ],
        ],

        'api.external.compositions' => [
            'summary' => 'Which team compositions win, and how often.',
            'page' => '/Global/Compositions',
            'uses' => ['globals'],
            // A composition is already five roles, so there is nothing to filter one by.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'minimum_games' => ['type' => 'integer', 'description' => 'Drop compositions below this many games. Defaults to 100.'],
            ],
        ],

        'api.external.compositions.heroes' => [
            'summary' => 'The heroes making up one composition.',
            'page' => '/Global/Compositions',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter', 'hero'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'composition_id' => ['required' => true, 'type' => 'integer', 'description' => 'A `composition_id` from the `/compositions` response.', 'example' => 1],
            ],
        ],

        'api.external.draft' => [
            'summary' => 'Draft order and pick position for one hero.',
            'page' => '/Global/Draft',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter', 'mirror'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.party' => [
            'summary' => 'How party size affects win rate.',
            'page' => '/Global/Party',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'group_by_map' => ['enum' => ['true', 'false'], 'description' => 'Report one result set per playable map rather than one across all of them, keyed by map name. Answers with a job id like any other global query, and counts as one call however many maps it covers — a multiplier may be applied later if that turns out to be abused.'],
                'teamoneparty' => ['description' => 'Party combination code for the first team — see Variables.', 'example' => '00005'],
                'teamtwoparty' => ['description' => 'Party combination code for the second team — see Variables.', 'example' => '00005'],
            ],
        ],

        'api.external.leaderboard' => [
            'summary' => 'Season leaderboards by player, hero or role.',
            'page' => '/Global/Leaderboard',
            'parameters' => [
                'season' => ['type' => 'integer', 'description' => 'Season id. Defaults to the current season — the current match prediction season when `type` is `match prediction`.'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
                'type' => ['enum' => ['player', 'hero', 'role', 'match prediction'], 'description' => 'What the board ranks. Defaults to `player`. `match prediction` takes only `season` and `game_type`; anything else is refused.'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size. Defaults to `Solo`.'],
                'hero' => ['description' => 'Hero name. Required when `type` is `hero`.'],
                'role' => ['description' => 'Role name. Required when `type` is `role`.'],
                'region' => ['description' => 'Region, by name or id — `NA` and `1` both work.', 'example' => 'NA'],
                'tierrank' => ['description' => 'League tier id — see Variables.'],
            ],
        ],

        'api.external.jobs' => [
            'summary' => 'Collect the result of a job returned by a 202. Costs no quota.',
            'parameters' => [
                'jobId' => ['description' => 'The `job_id` from a 202 response.'],
            ],
            'responses' => [
                '200' => ['description' => 'The finished result, in the shape the originating endpoint documents.'],
                '202' => ['description' => 'Still running. Poll again.'],
            ],
        ],

        /*
        | NGS reads, one per section of the site's NGS pages. Metered like any
        | other read.
        */

        'api.external.ngs.standings' => [
            'summary' => 'League standings, grouped by division.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
        ],

        'api.external.ngs.divisions' => [
            'summary' => 'Every division in a season, with its game count.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
            'except' => ['division'],
        ],

        'api.external.ngs.teams' => [
            'summary' => 'Teams with their record and win rate.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
        ],

        'api.external.ngs.players.search' => [
            'summary' => 'Find NGS players by battletag.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'battletag' => ['required' => true, 'description' => 'A full battletag, or just the part before the `#` to match every discriminator.', 'example' => 'Zemill'],
            ],
        ],

        'api.external.ngs.matches' => [
            'summary' => 'Recent matches, newest first, with the heroes in each.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
            'parameters' => [
                'hero' => ['description' => 'Only matches this hero played in, by name.', 'example' => 'Anduin'],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results. Defaults to 1.'],
            ],
        ],

        'api.external.ngs.heroes.stats' => [
            'summary' => 'Win, ban and popularity rates per hero.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
        ],

        'api.external.ngs.heroes.talents.stats' => [
            'summary' => 'Talent pick and win rates, and the top builds, for one hero.',
            'page' => '/Esports/NGS',
            'uses' => ['ngs'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.ngs.division' => [
            'summary' => 'One division: totals, top players and teams, heroes and maps.',
            'page' => '/Esports/NGS/Division/{division}',
            'uses' => ['ngs'],
            'parameters' => [
                'division' => ['required' => true, 'description' => 'NGS division name, as NGS spells it.'],
            ],
        ],

        'api.external.ngs.division.matches' => [
            'summary' => 'Every match played in one division.',
            'page' => '/Esports/NGS/Division/{division}/Match/History',
            'uses' => ['ngs'],
            'parameters' => [
                'division' => ['required' => true, 'description' => 'NGS division name, as NGS spells it.'],
            ],
        ],

        'api.external.ngs.team' => [
            'summary' => 'One team: roster, heroes, maps, and the teams it lost to.',
            'page' => '/Esports/NGS/Team/{team}',
            'uses' => ['ngs'],
            'parameters' => [
                'team' => ['required' => true, 'description' => 'Team name, exactly as `ngs/teams` returns it.'],
                'season' => ['type' => 'integer', 'description' => 'NGS season number. Omit for every season.'],
            ],
        ],

        'api.external.ngs.team.matches' => [
            'summary' => 'One team\'s matches, newest first.',
            'page' => '/Esports/NGS/Team/{team}/Match/History',
            'uses' => ['ngs'],
            'parameters' => [
                'team' => ['required' => true, 'description' => 'Team name, exactly as `ngs/teams` returns it.'],
                'season' => ['type' => 'integer', 'description' => 'NGS season number. Omit for every season.'],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results, 100 per page. Defaults to 1.'],
            ],
        ],

        'api.external.ngs.player' => [
            'summary' => 'One player\'s NGS record: heroes, maps, teams and talents.',
            'page' => '/Esports/NGS/Player/{battletag}/{blizz_id}',
            'uses' => ['ngs_player', 'ngs'],
            'parameters' => [
                'season' => ['type' => 'integer', 'description' => 'NGS season number. Omit for every season.'],
            ],
        ],

        'api.external.ngs.player.hero' => [
            'summary' => 'One player\'s NGS record on one hero.',
            'page' => '/Esports/NGS/Player/{battletag}/{blizz_id}/Hero/{hero}',
            'uses' => ['ngs_player', 'ngs'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'season' => ['type' => 'integer', 'description' => 'NGS season number. Omit for every season.'],
            ],
        ],

        'api.external.ngs.player.map' => [
            'summary' => 'One player\'s NGS record on one map.',
            'page' => '/Esports/NGS/Player/{battletag}/{blizz_id}/Map/{game_map}',
            'uses' => ['ngs_player', 'ngs'],
            'parameters' => [
                'game_map' => ['required' => true, 'description' => 'Map name.', 'example' => 'Alterac Pass'],
                'season' => ['type' => 'integer', 'description' => 'NGS season number. Omit for every season.'],
            ],
        ],

        'api.external.ngs.player.matches' => [
            'summary' => 'One player\'s NGS matches, newest first, with talents.',
            'page' => '/Esports/NGS/Player/{battletag}/{blizz_id}/Match/History',
            'uses' => ['ngs_player'],
            'parameters' => [
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results, 100 per page. Defaults to 1.'],
            ],
        ],

        'api.external.ngs.replay' => [
            'summary' => 'Full detail for one NGS match.',
            'page' => '/Esports/NGS/Match/Single/{replayID}',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'NGS replay id, as the match endpoints return it.'],
            ],
        ],

        /*
        | NGS ingestion. Upload needs both `n_approved` and `n_upload_approved`.
        | Delete is admin only, and is left out of the published spec — only an
        | admin in admin mode sees it on the docs page.
        */

        'api.external.ngs.games.delete' => [
            'summary' => 'Remove one NGS game. Admin only.',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'NGS replay id.'],
                'mode' => ['required' => true, 'enum' => ['prod', 'dev'], 'description' => 'Which NGS schema to delete from.', 'example' => 'dev'],
            ],
            'responses' => [
                '200' => [
                    'description' => 'The game and its players, talents, scores, bans and draft are gone.',
                    'content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'deleted' => ['type' => 'integer', 'description' => 'The replay id removed.'],
                        ],
                    ]]],
                ],
            ],
        ],

        'api.external.ngs.games.upload' => [
            'summary' => 'Ingest one NGS custom game.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'replay_url' => ['required' => true, 'description' => 'Where the replay sits in the NGS bucket. Pinned to that bucket — any other host is refused.'],
                'mode' => ['required' => true, 'description' => 'Which NGS schema to write. `prod` or `dev`.', 'example' => 'prod'],
                'season' => ['required' => true, 'type' => 'integer', 'description' => 'NGS season number.'],
                'round' => ['required' => true, 'description' => 'Round within the season.'],
                'game' => ['required' => true, 'description' => 'Game number within the match.'],
                'team_one_name' => ['required' => true, 'description' => 'First team name.'],
                'team_two_name' => ['required' => true, 'description' => 'Second team name.'],
                'team_one_player' => ['required' => true, 'description' => 'A battletag on the first team, used to work out which side it played. Must appear in the replay.', 'example' => 'Player#1234'],
                'team_two_player' => ['required' => true, 'description' => 'The same, for the second team.'],
                'team_one_map_ban_1' => ['required' => true, 'description' => 'Map name, banned by the first team.'],
                'team_one_map_ban_2' => ['required' => true, 'description' => 'Second map banned by the first team.'],
                'team_two_map_ban_1' => ['required' => true, 'description' => 'Map name, banned by the second team.'],
                'team_two_map_ban_2' => ['required' => true, 'description' => 'Second map banned by the second team.'],
                'team_one_image_url' => ['description' => 'First team logo. Defaults to a placeholder.'],
                'team_two_image_url' => ['description' => 'Second team logo. Defaults to a placeholder.'],
                'tournament' => ['description' => 'Defaults to `NGS`.'],
                'team_one_division' => ['description' => 'Defaults to `NGS`.'],
                'team_two_division' => ['description' => 'Defaults to `NGS`.'],
            ],
            'responses' => [
                '200' => [
                    'description' => 'The stored match, plus a profile link per player. Keyed by team name, as the NGS tooling expects.',
                    'content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'url' => ['type' => 'string', 'description' => 'The match page for the game just stored.'],
                        ],
                    ]]],
                ],
                '422' => ['description' => 'The replay could not be ingested — not a custom game, an unknown map, or a named player absent from it.'],
            ],
        ],

        /*
        | Tools.
        */

        'api.external.tools.randomize' => [
            'summary' => 'A random talent build for one hero.',
            'page' => '/Tools/RandomizeMe',
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.tools.activity.players.unique' => [
            'summary' => 'Unique players seen per month.',
            'page' => '/Tools/Activity',
            'parameters' => [
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Omit for every type.', 'example' => 'Storm League'],
                'region' => ['description' => 'One region, by name or id — `NA` and `1` both work. NA/1, EU/2, KR/3, CN/5. Omit for every region.', 'example' => 'NA'],
            ],
        ],

        /*
        | Ingestion. Anonymous permanently: the uploader is a public repository,
        | so a bundled key would be extractable from source.
        |
        | Two of these answer plain text and MUST NOT be documented as JSON. The
        | deployed client compares one as a string and Int32.TryParses the other,
        | so an envelope kills the feature with only a client-side log line.
        */

        'api.external.upload' => [
            'summary' => 'Upload a replay.',
            'page' => '/Upload',
            'parameters' => [
                'source' => ['description' => 'Which client is uploading. `desktop` and `electron` own a replay source; anything else defers to them.', 'example' => 'desktop'],
                'fingerprint' => ['description' => 'The client own fingerprint. Accepted and ignored: the server derives its own.'],
                'version' => ['description' => 'Uploader version.'],
                'compiled' => ['description' => 'Uploader build number.'],
            ],
            'request_body' => [
                'required' => true,
                'content' => ['multipart/form-data' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'file' => ['type' => 'string', 'format' => 'binary', 'description' => 'The .StormReplay file. 10 MB at most.'],
                    ],
                    'required' => ['file'],
                ]]],
            ],
            'responses' => [
                '200' => [
                    'description' => 'A frozen three-field body. `status` is `Success`, `Duplicate`, or a failure string. Deployed clients read nothing else. When no file arrives, or it is over 10 MB, the body is instead `{"success": false, "Error": "..."}`, still with 200.',
                    'content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => [
                            'fingerprint' => ['type' => 'string', 'description' => 'A GUID, nil when none could be derived.'],
                            'replayID' => ['type' => 'integer'],
                            'status' => ['type' => 'string'],
                        ],
                        'required' => ['fingerprint', 'status'],
                    ]]],
                ],
            ],
        ],

        'api.external.replays.fingerprint' => [
            'summary' => 'Whether a replay with this fingerprint is already stored.',
            'parameters' => [
                'fingerprint' => ['description' => 'The replay fingerprint.'],
            ],
            'responses' => [
                '200' => [
                    'description' => 'Whether the replay is known.',
                    'content' => ['application/json' => ['schema' => [
                        'type' => 'object',
                        'properties' => ['exists' => ['type' => 'boolean']],
                        'required' => ['exists'],
                    ]]],
                ],
            ],
        ],

        'api.external.replays.parsed' => [
            'summary' => 'Whether a replay has been parsed and stored yet.',
            'parameters' => [
                'replayID' => ['required' => true, 'type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
            'responses' => [
                '200' => [
                    'description' => 'The literal word `true` or `false`, as plain text. Not JSON: the uploader compares the body as a string, and an envelope stops the post-match page opening, silently. A missing or non-numeric `replayID` answers `false` rather than an error.',
                    'content' => ['text/plain' => ['schema' => ['type' => 'string', 'enum' => ['true', 'false']]]],
                ],
            ],
        ],

        'api.external.prematch' => [
            'summary' => 'Record the players in a game that is starting.',
            'parameters' => [],
            'responses' => [
                '200' => [
                    'description' => 'A bare integer as plain text, the pre-match id. Not JSON: the uploader runs Int32.TryParse over the body.',
                    'content' => ['text/plain' => ['schema' => ['type' => 'integer']]],
                ],
                '400' => [
                    'description' => 'Plain text describing what was wrong with `data`.',
                    'content' => ['text/plain' => ['schema' => ['type' => 'string']]],
                ],
            ],
        ],

    ],
];
