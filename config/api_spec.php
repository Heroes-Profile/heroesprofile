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
    | Shared by every global statistics endpoint, from
    | `GlobalsInputValidationController::globalsValidationRules()`.
    */
    'globals' => [
        'timeframe_type' => [
            'required' => true,
            'enum' => ['minor', 'major', 'major_grouped', 'last_update'],
            'description' => 'How `timeframe` is read. `minor` is a build, `major` a patch.',
            'example' => 'minor',
        ],
        'timeframe' => [
            'required' => true,
            'description' => 'One patch or build, or several comma-separated. Not required when `timeframe_type` is `last_update`.',
            'example' => '2.55.17.97771',
        ],
        'game_type' => [
            'required' => true,
            'enum' => ['qm', 'ud', 'hl', 'tl', 'sl', 'ar'],
            'description' => 'Game type, by short name or display name — `sl` and `Storm League` both work, case-insensitive. Comma-separated for several.',
            'example' => 'Storm League',
        ],
        'region' => ['description' => 'Region, by name or id — `NA` and `1` both work. NA/1, EU/2, KR/3, CN/5.', 'example' => 'NA'],
        'hero' => ['description' => 'Hero name.', 'example' => 'Anduin'],
        'role' => ['description' => 'Role name.', 'example' => 'Healer'],
        'game_map' => ['description' => 'Map name.', 'example' => 'Alterac Pass'],
        'hero_level' => ['description' => 'Hero level band, not a level. One of the band codes — see Variables. Comma-separated for several.', 'example' => '25'],
        'league_tier' => ['description' => 'Player league tier id.'],
        'hero_league_tier' => ['description' => 'Hero league tier id.'],
        'role_league_tier' => ['description' => 'Role league tier id.'],
        'mirror' => ['enum' => ['0', '1'], 'description' => 'Include mirror matches.'],
        'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size filter.'],
        'statfilter' => ['description' => 'Statistic to filter on.'],
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
            'api.external.players.roles',
            'api.external.players.roles.single',
            'api.external.players.talents.build',
        ],

        'Leaderboards' => [
            'api.external.leaderboard',
        ],

        'Replays' => [
            'api.external.replay.bans',
            'api.external.replay.show',
            'api.external.replay.download',
            'api.external.replays.index',
        ],

        'NGS Stats' => [
            'api.external.ngs.hero.stat',
            'api.external.ngs.leaderboard.average',
            'api.external.ngs.leaderboard.total',
            'api.external.ngs.match',
            'api.external.ngs.player.profile',
            'api.external.ngs.replay.data',
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
        'replayID' => 'The match id. Pass it to `/matches/{replayID}` for full detail.',
        'blizz_id' => 'Blizzard account id. Stable per region, and not a battletag.',
        'region' => 'Region id. 1 NA, 2 EU, 3 KR, 5 CN.',
        'next_after' => 'Pass as `after` to get the following page. Null once you have caught up.',
        'max_replay_id' => 'The highest replay id stored, so you know how far there is to go.',
        'downloadable' => 'Whether the replay file is still within the retention window and can be fetched from `/replays/download`.',
        'win_rate' => 'Percentage, 0 to 100.',
        'popularity' => 'Percentage of matches in which this appeared, 0 to 100.',
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
            'enum' => ['NA', 'EU', 'KR', 'CN', 1, 2, 3, 5],
            'description' => 'Region, by name or id — `NA` and `1` both work. NA/1, EU/2, KR/3, CN/5.',
            'example' => 'NA',
        ],
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
            'parameters' => [],
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
                'game_type' => ['required' => true, 'description' => 'Game type, by short name or display name — `sl` and `Storm League` both work.', 'example' => 'Storm League'],
                'mmr' => ['required' => true, 'type' => 'integer', 'description' => 'The rating to place.', 'example' => 2400],
            ],
        ],

        /*
        | Players. `hero`, `map` and `role` are always names here, never ids —
        | the wrappers translate for the controllers that want ids.
        */

        'api.external.players' => [
            'summary' => 'Profile, ratings and career totals for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}',
            'uses' => ['player'],
        ],

        'api.external.players.matches' => [
            'summary' => 'Match history, with the full stat line for each game.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Match/History',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer', 'description' => 'Restrict to one season.'],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results. Defaults to 1.'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'role' => ['description' => 'Filter to one role, by name.', 'example' => 'Healer'],
                'stack_size' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size the player queued at.'],
            ],
        ],

        'api.external.players.heroes' => [
            'summary' => 'Per-hero performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Hero',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop heroes below this many games.'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.heroes.single' => [
            'summary' => 'One hero, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Hero/{hero}',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Filter to one map, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.maps' => [
            'summary' => 'Per-map performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Map',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop rows below this many games.'],
            ],
        ],

        'api.external.players.maps.single' => [
            'summary' => 'One map, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Map/{map}',
            'uses' => ['player'],
            'parameters' => [
                'map' => ['required' => true, 'description' => 'Map name.', 'example' => 'Alterac Pass'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.players.roles' => [
            'summary' => 'Per-role performance for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Role',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop rows below this many games.'],
            ],
        ],

        'api.external.players.roles.single' => [
            'summary' => 'One role, for one player.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Role/{role}',
            'uses' => ['player'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'hero' => ['description' => 'Filter to one hero, by name.', 'example' => 'Anduin'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
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
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
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
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
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
            'uses' => ['player'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.external.players.talents.build' => [
            'summary' => 'A player, most played builds on one hero.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Talents',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Map name.'],
                'fromdate' => ['description' => 'Only matches on or after this date, `YYYY-MM-DD`.', 'example' => '2024-01-01'],
            ],
        ],

        'api.external.players.matchups' => [
            'summary' => 'Opponents this player meets most, and how they fare.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/Matchups',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Filter to one map, or several comma-separated, by name.', 'example' => 'Alterac Pass'],
            ],
        ],

        'api.external.players.friendfoe' => [
            'summary' => 'Team-mates and opponents this player sees repeatedly.',
            'page' => '/Player/{battletag}/{blizz_id}/{region}/FriendFoe',
            'uses' => ['player'],
            'parameters' => [
                'type' => ['enum' => ['friend', 'enemy'], 'description' => 'Which side to report. Defaults to `friend`.'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Defaults to Storm League.', 'example' => 'Storm League'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Map name.'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size filter.'],
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

        'api.external.replays.index' => [
            'summary' => 'A page of replays, for building a local copy of the data. Paged by replay id: pass the `next_after` from one response as the `after` of the next, and stop when it comes back null.',
            'parameters' => [
                'after' => ['type' => 'integer', 'description' => 'Return replays with an id greater than this. Omit to start from the beginning.', 'example' => 0],
                'timeframe_type' => ['enum' => ['minor', 'major'], 'description' => 'How `timeframe` is read.'],
                'timeframe' => ['description' => 'One patch or build.', 'example' => '2.55.17.97771'],
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'game_map' => ['description' => 'Map names, comma-separated. Omit for every map.'],
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
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.external.heroes.maps' => [
            'summary' => 'One hero, win rate per map.',
            'page' => '/Global/Hero/Maps',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter'],
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
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
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
            'summary' => 'Every hero, most popular builds, for the current patch. Takes no parameters: the timeframe and build type come from the site defaults, not the request.',
            'page' => '/Global/Talents',
            'parameters' => [],
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
            'summary' => 'The replays behind a talent-builder result. Send at least one selected talent — with none, it returns the full talent list for that hero rather than replays.',
            'page' => '/Global/Talents/Builder',
            'uses' => ['globals'],
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

        'api.external.compositions' => [
            'summary' => 'Which team compositions win, and how often.',
            'page' => '/Global/Compositions',
            'uses' => ['globals'],
            // A composition is already five roles, so there is nothing to filter one by.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'minimum_games' => ['type' => 'integer', 'description' => 'Drop compositions below this many games. Defaults to 100.'],
            ],
        ],

        'api.external.compositions.heroes' => [
            'summary' => 'The heroes making up one composition.',
            'page' => '/Global/Compositions',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
                'composition_id' => ['required' => true, 'type' => 'integer', 'description' => 'A `composition_id` from the `/compositions` response.', 'example' => 1],
                'minimum_games' => ['type' => 'integer', 'description' => 'Defaults to 100.'],
            ],
        ],

        'api.external.draft' => [
            'summary' => 'Draft order and pick position for one hero.',
            'page' => '/Global/Draft',
            'uses' => ['globals'],
            // Validated by the shared globals rules, read by nothing here. Leaving them
            // documented would advertise a filter that silently does nothing.
            'except' => ['role', 'groupsize', 'statfilter'],
            'async' => true,
            'parameters' => [
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
                'teamoneparty' => ['description' => 'Party combination for the first team.'],
                'teamtwoparty' => ['description' => 'Party combination for the second team.'],
            ],
        ],

        'api.external.leaderboard' => [
            'summary' => 'Season leaderboards by player, hero or role.',
            'page' => '/Global/Leaderboard',
            'parameters' => [
                'season' => ['type' => 'integer', 'description' => 'Season id. Defaults to the current season.'],
                'game_type' => ['description' => 'One game type, by short name or display name — `sl` and `Storm League` both work. Defaults to Storm League.', 'example' => 'Storm League'],
                'type' => ['enum' => ['player', 'hero', 'role', 'match prediction'], 'description' => 'What the board ranks. Defaults to `player`.'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size. Defaults to `Solo`.'],
                'hero' => ['description' => 'Hero name, when `type` is `hero`.'],
                'role' => ['description' => 'Role name, when `type` is `role`.'],
                'region' => ['type' => 'integer', 'description' => 'Region id.'],
                'tierrank' => ['description' => 'League tier id.'],
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
                '404' => ['description' => 'No such job, or it has expired.'],
            ],
        ],

        /*
        | NGS. Granted access rather than a purchased tier: restricted to accounts
        | holding the NGS flags, and charged no quota.
        */

        'api.external.ngs.match' => [
            'summary' => 'One NGS match.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'season' => ['required' => true, 'type' => 'integer', 'description' => 'NGS season.'],
                'division' => ['required' => true, 'description' => 'Division name.'],
                'team' => ['required' => true, 'description' => 'Team name.'],
                'round' => ['required' => true, 'type' => 'integer', 'description' => 'Round number.'],
            ],
        ],

        'api.external.ngs.hero.stat' => [
            'summary' => 'Hero statistics within one NGS division.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'season' => ['required' => true, 'type' => 'integer'],
                'division' => ['required' => true, 'description' => 'Division name.'],
                'hero' => ['required' => true, 'description' => 'Hero name.'],
                'battletag' => ['description' => 'Restrict to one player.'],
            ],
        ],

        'api.external.ngs.player.profile' => [
            'summary' => 'One NGS player.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'battletag' => ['required' => true, 'description' => 'Full battletag.', 'example' => 'Zemill#1940'],
                'season' => ['type' => 'integer'],
                'division' => ['description' => 'Division name.'],
            ],
        ],

        'api.external.ngs.leaderboard.average' => [
            'summary' => 'NGS leaderboard by highest average of one statistic.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'stat' => ['required' => true, 'description' => 'The statistic to rank by.', 'example' => 'hero_damage'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.external.ngs.leaderboard.total' => [
            'summary' => 'NGS leaderboard by highest total of one statistic.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'stat' => ['required' => true, 'description' => 'The statistic to rank by.', 'example' => 'hero_damage'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.external.ngs.replay.data' => [
            'summary' => 'Full detail for one NGS match by replay id.',
            'page' => '/Esports/NGS',
            'parameters' => [
                'replayID' => ['required' => true, 'type' => 'integer', 'description' => 'Heroes Profile match ID.'],
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
                'game_type' => ['description' => 'Game type, by short name or display name — `sl` and `Storm League` both work. Comma-separated for several. Omit for every type.', 'example' => 'Storm League'],
                'region' => ['type' => 'integer', 'description' => 'Region id. Omit for every region.'],
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
            'responses' => [
                '200' => [
                    'description' => 'A frozen three-field body. `status` is `Success`, `Duplicate`, or a failure string. Deployed clients read nothing else.',
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
                    'description' => 'The literal word `true` or `false`, as plain text. Not JSON: the uploader compares the body as a string, and an envelope stops the post-match page opening, silently.',
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
