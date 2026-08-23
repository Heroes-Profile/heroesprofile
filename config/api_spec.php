<?php

/*
|--------------------------------------------------------------------------
| Public API specification
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
    | Order matters: generated clients default to the first server, so the one that
    | works today has to lead. `api.heroesprofile.com` still resolves to the old site
    | until the subdomain moves on 1 Jan 2027 — advertising it alone would have
    | pointed every generated client at a host that cannot serve v1.
    |
    | The path-based URL is not a stopgap. `RouteServiceProvider` mounts these routes
    | under `api.path` with no host constraint, so it keeps answering after the
    | subdomain moves. Porting to it is a one-time change.
    |
    | Keep both in step with `config/api.php` (`path` and `domain`).
    */

    'servers' => [
        ['url' => 'https://www.heroesprofile.com/api/public/v1', 'description' => 'Production'],
        ['url' => 'https://api.heroesprofile.com/v1', 'description' => 'Production, from 1 Jan 2027 when the API subdomain moves here'],
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
            'description' => 'Game type short names, comma-separated.',
            'example' => 'sl',
        ],
        'region' => ['type' => 'integer', 'description' => 'Region id. 1 NA, 2 EU, 3 KR, 5 CN.'],
        'hero' => ['description' => 'Hero name.', 'example' => 'Anduin'],
        'role' => ['description' => 'Role name.', 'example' => 'Healer'],
        'game_map' => ['description' => 'Map name.', 'example' => 'Alterac Pass'],
        'hero_level' => ['description' => 'Minimum hero level.'],
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
            'api.public.heroes',
            'api.public.heroes.talents',
            'api.public.maps',
            'api.public.mmr.tier',
            'api.public.patches',
        ],

        'Global Hero Stats' => [
            'api.public.compositions',
            'api.public.compositions.heroes',
            'api.public.draft',
            'api.public.heroes.maps',
            'api.public.heroes.matchups',
            'api.public.heroes.matchups.talents',
            'api.public.heroes.stats',
            'api.public.heroes.talents.builds',
            'api.public.heroes.talents.builds.all',
            'api.public.heroes.talents.details',
            'api.public.party',
        ],

        'Player Stats' => [
            'api.public.players',
            'api.public.players.friendfoe',
            'api.public.players.heroes',
            'api.public.players.heroes.single',
            'api.public.players.maps',
            'api.public.players.maps.single',
            'api.public.players.matches',
            'api.public.players.matchups',
            'api.public.players.mmr',
            'api.public.players.mmr.heroes',
            'api.public.players.mmr.roles',
            'api.public.players.roles',
            'api.public.players.roles.single',
            'api.public.players.talents.build',
        ],

        'Leaderboards' => [
            'api.public.leaderboard',
        ],

        'Matches' => [
            'api.public.matches.bans',
            'api.public.matches.show',
            'api.public.replays.download',
            'api.public.replays.index',
        ],

        'NGS Stats' => [
            'api.public.ngs.hero.stat',
            'api.public.ngs.leaderboard.average',
            'api.public.ngs.leaderboard.total',
            'api.public.ngs.match',
            'api.public.ngs.player.profile',
            'api.public.ngs.replay.data',
        ],

        'Tools' => [
            'api.public.heroes.talents.builder',
            'api.public.heroes.talents.builder.replays',
            'api.public.tools.activity.players.unique',
            'api.public.tools.randomize',
        ],

        'Uploading Replays' => [
            'api.public.prematch',
            'api.public.replays.fingerprint',
            'api.public.replays.parsed',
            'api.public.upload',
        ],

        'Job Results' => [
            'api.public.jobs',
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
            'example' => 'ExamplePlayer#0000',
        ],
        'region' => [
            'required' => true,
            'type' => 'integer',
            'enum' => [1, 2, 3, 5],
            'description' => 'Region id. 1 NA, 2 EU, 3 KR, 5 CN.',
            'example' => 1,
        ],
    ],

    'endpoints' => [

        /*
        | Reference data. Large, slow-moving, and effectively free.
        */

        'api.public.maps' => [
            'summary' => 'Every map, with its id and rotation status.',
            'parameters' => [],
        ],

        'api.public.heroes' => [
            'summary' => 'Every hero, with role, type and release date.',
            'parameters' => [],
        ],

        'api.public.heroes.talents' => [
            'summary' => 'Every talent for every hero.',
            'parameters' => [
                'hero' => ['description' => 'Restrict to one hero by name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.patches' => [
            'summary' => 'Game versions, with the season each belongs to.',
            'parameters' => [],
        ],

        'api.public.mmr.tier' => [
            'summary' => 'The league tier a rating falls in.',
            'parameters' => [
                'game_type' => ['required' => true, 'description' => 'Game type short name.', 'example' => 'sl'],
                'mmr' => ['required' => true, 'type' => 'integer', 'description' => 'The rating to place.', 'example' => 2400],
            ],
        ],

        /*
        | Players. `hero`, `map` and `role` are always names here, never ids —
        | the wrappers translate for the controllers that want ids.
        */

        'api.public.players' => [
            'summary' => 'Profile, ratings and career totals for one player.',
            'uses' => ['player'],
        ],

        'api.public.players.matches' => [
            'summary' => 'Match history, with the full stat line for each game.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer', 'description' => 'Restrict to one season.'],
                'pagination_page' => ['type' => 'integer', 'description' => 'Page of results. Defaults to 1.'],
            ],
        ],

        'api.public.players.heroes' => [
            'summary' => 'Per-hero performance for one player.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
                'minimumgames' => ['type' => 'integer', 'description' => 'Drop heroes below this many games.'],
            ],
        ],

        'api.public.players.heroes.single' => [
            'summary' => 'One hero, for one player.',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.maps' => [
            'summary' => 'Per-map performance for one player.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.maps.single' => [
            'summary' => 'One map, for one player.',
            'uses' => ['player'],
            'parameters' => [
                'map' => ['required' => true, 'description' => 'Map name.', 'example' => 'Alterac Pass'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.roles' => [
            'summary' => 'Per-role performance for one player.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.roles.single' => [
            'summary' => 'One role, for one player.',
            'uses' => ['player'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.mmr' => [
            'summary' => 'Rating history for the account.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'One game type short name. Defaults to `sl`.', 'example' => 'sl'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.mmr.heroes' => [
            'summary' => 'Rating history for one hero.',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'One game type short name. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.mmr.roles' => [
            'summary' => 'Rating history for one role.',
            'uses' => ['player'],
            'parameters' => [
                'role' => ['required' => true, 'description' => 'Role name.', 'example' => 'Healer'],
                'game_type' => ['description' => 'One game type short name. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.talents.build' => [
            'summary' => 'A player, most played builds on one hero.',
            'uses' => ['player'],
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Map name.'],
            ],
        ],

        'api.public.players.matchups' => [
            'summary' => 'Opponents this player meets most, and how they fare.',
            'uses' => ['player'],
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Omit for every type.'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.players.friendfoe' => [
            'summary' => 'Team-mates and opponents this player sees repeatedly.',
            'uses' => ['player'],
            'parameters' => [
                'type' => ['enum' => ['friend', 'enemy'], 'description' => 'Which side to report. Defaults to `friend`.'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Defaults to `sl`.'],
                'hero' => ['description' => 'Restrict to one hero by name.'],
                'season' => ['type' => 'integer'],
                'game_map' => ['description' => 'Map name.'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size filter.'],
            ],
        ],

        /*
        | Matches.
        */

        'api.public.matches.show' => [
            'summary' => 'Full detail for one match, including every stat line.',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        'api.public.matches.bans' => [
            'summary' => 'Hero bans for one match.',
            'parameters' => [
                'replayID' => ['type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        'api.public.replays.index' => [
            'summary' => 'A page of replays, for building a local copy of the data. Paged by replay id: pass the `next_after` from one response as the `after` of the next, and stop when it comes back null.',
            'parameters' => [
                'after' => ['type' => 'integer', 'description' => 'Return replays with an id greater than this. Omit to start from the beginning.', 'example' => 0],
                'timeframe_type' => ['enum' => ['minor', 'major'], 'description' => 'How `timeframe` is read.'],
                'timeframe' => ['description' => 'One patch or build.', 'example' => '2.55.17.97771'],
                'game_type' => ['description' => 'Game type short names, comma-separated. Omit for every type.'],
                'game_map' => ['description' => 'Map names, comma-separated. Omit for every map.'],
            ],
        ],

        'api.public.replays.download' => [
            'summary' => 'The original .StormReplay file for a match.',
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

        'api.public.heroes.stats' => [
            'summary' => 'Win rate, popularity and ban rate for every hero.',
            'uses' => ['globals'],
            'async' => true,
        ],

        'api.public.heroes.matchups' => [
            'summary' => 'How one hero performs with and against every other.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.heroes.maps' => [
            'summary' => 'One hero, win rate per map.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.heroes.matchups.talents' => [
            'summary' => 'Talent performance for one hero against or alongside another.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'ally_enemy' => ['required' => true, 'description' => 'The other hero, by name.', 'example' => 'Johanna'],
                'type' => ['enum' => ['Enemy', 'Ally'], 'description' => 'Whether the other hero is an opponent or a team-mate. Defaults to `Enemy`.'],
                'talent_view' => ['enum' => ['hero', 'ally_enemy'], 'description' => 'Whose talents to report. Defaults to `hero`.'],
            ],
        ],

        'api.public.heroes.talents.details' => [
            'summary' => 'Win rate and popularity for every talent.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.heroes.talents.builds' => [
            'summary' => 'The most played complete builds for one hero.',
            'uses' => ['globals'],
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

        'api.public.heroes.talents.builds.all' => [
            'summary' => 'Every hero, most popular builds, for the current patch. Takes no parameters: the timeframe and build type come from the site defaults, not the request.',
            'parameters' => [],
        ],

        'api.public.heroes.talents.builder' => [
            'summary' => 'Win rates for a partially chosen build, to evaluate the next talent.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'selectedtalents' => ['description' => 'Talents already chosen, keyed by tier: `selectedtalents[1]`, `selectedtalents[4]`, through 20. Values are talent ids.'],
            ],
        ],

        'api.public.heroes.talents.builder.replays' => [
            'summary' => 'The replays behind a talent-builder result.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
                'selectedtalents' => ['required' => true, 'description' => 'Talents to match, keyed by tier: `selectedtalents[1]`, `selectedtalents[4]`, through 20. Values are talent ids. Required: without it this endpoint returns the same talent list as `heroes/talents/builder`.'],
            ],
        ],

        'api.public.compositions' => [
            'summary' => 'Which team compositions win, and how often.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'minimum_games' => ['type' => 'integer', 'description' => 'Drop compositions below this many games. Defaults to 100.'],
            ],
        ],

        'api.public.compositions.heroes' => [
            'summary' => 'The heroes making up one composition.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'composition_id' => ['required' => true, 'type' => 'integer', 'description' => 'A `composition_id` from the `/compositions` response.'],
                'minimum_games' => ['type' => 'integer', 'description' => 'Defaults to 100.'],
            ],
        ],

        'api.public.draft' => [
            'summary' => 'Draft order and pick position for one hero.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.party' => [
            'summary' => 'How party size affects win rate.',
            'uses' => ['globals'],
            'async' => true,
            'parameters' => [
                'teamoneparty' => ['description' => 'Party combination for the first team.'],
                'teamtwoparty' => ['description' => 'Party combination for the second team.'],
            ],
        ],

        'api.public.leaderboard' => [
            'summary' => 'Season leaderboards by player, hero or role.',
            'parameters' => [
                'season' => ['type' => 'integer', 'description' => 'Season id. Defaults to the current season.'],
                'game_type' => ['description' => 'One game type short name. Defaults to `sl`.', 'example' => 'sl'],
                'type' => ['enum' => ['player', 'hero', 'role', 'match prediction'], 'description' => 'What the board ranks. Defaults to `player`.'],
                'groupsize' => ['enum' => ['All', 'Solo', 'Duo', '3 Players', '4 Players', '5 Players'], 'description' => 'Party size. Defaults to `Solo`.'],
                'hero' => ['description' => 'Hero name, when `type` is `hero`.'],
                'role' => ['description' => 'Role name, when `type` is `role`.'],
                'region' => ['type' => 'integer', 'description' => 'Region id.'],
                'tierrank' => ['description' => 'League tier id.'],
            ],
        ],

        'api.public.jobs' => [
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

        'api.public.ngs.match' => [
            'summary' => 'One NGS match.',
            'parameters' => [
                'season' => ['required' => true, 'type' => 'integer', 'description' => 'NGS season.'],
                'division' => ['required' => true, 'description' => 'Division name.'],
                'team' => ['required' => true, 'description' => 'Team name.'],
                'round' => ['required' => true, 'type' => 'integer', 'description' => 'Round number.'],
            ],
        ],

        'api.public.ngs.hero.stat' => [
            'summary' => 'Hero statistics within one NGS division.',
            'parameters' => [
                'season' => ['required' => true, 'type' => 'integer'],
                'division' => ['required' => true, 'description' => 'Division name.'],
                'hero' => ['required' => true, 'description' => 'Hero name.'],
                'battletag' => ['description' => 'Restrict to one player.'],
            ],
        ],

        'api.public.ngs.player.profile' => [
            'summary' => 'One NGS player.',
            'parameters' => [
                'battletag' => ['required' => true, 'description' => 'Full battletag.'],
                'season' => ['type' => 'integer'],
                'division' => ['description' => 'Division name.'],
            ],
        ],

        'api.public.ngs.leaderboard.average' => [
            'summary' => 'NGS leaderboard by highest average of one statistic.',
            'parameters' => [
                'stat' => ['required' => true, 'description' => 'The statistic to rank by.', 'example' => 'hero_damage'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.ngs.leaderboard.total' => [
            'summary' => 'NGS leaderboard by highest total of one statistic.',
            'parameters' => [
                'stat' => ['required' => true, 'description' => 'The statistic to rank by.', 'example' => 'hero_damage'],
                'season' => ['type' => 'integer'],
            ],
        ],

        'api.public.ngs.replay.data' => [
            'summary' => 'Full detail for one NGS match by replay id.',
            'parameters' => [
                'replayID' => ['required' => true, 'type' => 'integer', 'description' => 'Heroes Profile match ID.'],
            ],
        ],

        /*
        | Tools.
        */

        'api.public.tools.randomize' => [
            'summary' => 'A random talent build for one hero.',
            'parameters' => [
                'hero' => ['required' => true, 'description' => 'Hero name.', 'example' => 'Anduin'],
            ],
        ],

        'api.public.tools.activity.players.unique' => [
            'summary' => 'Unique players seen per month.',
            'parameters' => [
                'game_type' => ['description' => 'Game type short names, comma-separated. Omit for every type.'],
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

        'api.public.upload' => [
            'summary' => 'Upload a replay.',
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

        'api.public.replays.fingerprint' => [
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

        'api.public.replays.parsed' => [
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

        'api.public.prematch' => [
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
