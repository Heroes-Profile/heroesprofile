<?php

// The Team page lists these, in this order.
//
// To add or change someone, copy a block and fill it in:
//   name  - as shown on the page
//   company - who they're with (Heroes Profile, and Skill Tree Development LLC for its owners), or null
//   role  - what they handle, one short line
//   start - when they joined, 'YYYY-MM' (shown as "June 2018")
//   end   - when they left, 'YYYY-MM', or null while they're still on the team ("Present")
//   bio   - one or two sentences about them; plain text
//   peak_rank - their best Storm League rank, shown in words, or null:
//               ['league' => 'master'], ['league' => 'diamond', 'division' => 2],
//               ['league' => 'grandmaster', 'ladder_position' => 1] or ['league' => 'unranked']
//   profile - their Heroes Profile player page, a path like '/Player/Name/12345/1', or null
//   email, reddit, discord - how to reach them; leave any out (or null) that they don't have.
//                            reddit is the username without u/; discord is the username
//   discord_id - their Discord user ID (Developer Mode, right-click, Copy User ID): the name links to their profile.
//                Without one, clicking the name copies the username to paste into Add Friend

return [
    'members' => [
        [
            'name' => 'Zemill',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Full Stack',
            'start' => '2018-06',
            'end' => null,
            'bio' => "Zemill started playing Heroes of the Storm in its early alpha. They began building Heroes Profile as a fun side project after hearing about HotsAPI. Today they do nearly all of the site's work, from the backend, scaffolding and architecture to the frontend and its social media presence; the only parts they leave to others are the visual design and styling.",
            'peak_rank' => ['league' => 'master'],
            'profile' => '/Player/Zemill/67280/1',
            'email' => 'zemill@heroesprofile.com',
            'reddit' => 'heroesprofile',
            'discord' => 'zemill',
            'discord_id' => '214839302000082945',
        ],
        [
            'name' => 'Avadriel',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Frontend',
            'start' => '2018-06',
            'end' => null,
            'bio' => "A World of Warcraft veteran, Avadriel joined Zemill at the very start of Heroes Profile as its frontend developer, shaping the site's style, and today they consult on the frontend.",
            'peak_rank' => ['league' => 'unranked'],
            'email' => 'avadriel@heroesprofile.com',
            'reddit' => 'heroesprofile',
            'discord' => null,
        ],
    ],

    // People who helped with a one-off piece of work, under Contributors below the team.
    //   name, discord - as above
    //   contribution  - what they did, one short line
    //   start, end    - when, 'YYYY-MM' (the same month twice for one month)
    //   link          - a page about what they did, or null; the contribution line links to it
    //   github        - their GitHub username, or null; shows a chip linking to their GitHub profile
    //   featured      - true for the biggest contribution: a teal border around their card
    'contributors' => [
        [
            'name' => 'Bluray',
            'contribution' => 'Designed the 2026 site redesign',
            'link' => '/Redesign/2026',
            'start' => '2026-10',
            'end' => '2026-12',
            'discord' => 'blurayy',
            'featured' => true,
        ],
        [
            'name' => 'cabsil22',
            'contribution' => 'Fixed the player matchups table',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3Acabsil22',
            'start' => '2025-12',
            'end' => '2025-12',
            'github' => 'cabsil22',
            'discord' => null,
        ],
        [
            'name' => 'K-JBoon',
            'contribution' => 'Docker setup and faster talents and matchup pages',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3AK-JBoon',
            'start' => '2025-08',
            'end' => '2025-10',
            'github' => 'K-JBoon',
            'discord' => null,
        ],
        [
            'name' => 'raylu',
            'contribution' => 'Pint checks in GitHub Actions',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3Araylu',
            'start' => '2025-05',
            'end' => '2025-05',
            'github' => 'raylu',
            'discord' => null,
        ],
        [
            'name' => 'MGatner',
            'contribution' => 'Set up unit tests and static code analysis',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3AMGatner',
            'start' => '2020-05',
            'end' => '2021-02',
            'github' => 'MGatner',
            'discord' => null,
        ],
        [
            'name' => 'Gibbo3771',
            'contribution' => 'Fixed the Docker setup',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3AGibbo3771',
            'start' => '2020-04',
            'end' => '2020-04',
            'github' => 'Gibbo3771',
            'discord' => null,
        ],
        [
            'name' => 'chorsnell',
            'contribution' => 'Docker setup and setup docs',
            'link' => 'https://github.com/Heroes-Profile/heroesprofile/pulls?q=is%3Apr+is%3Amerged+author%3Achorsnell',
            'start' => '2020-01',
            'end' => '2020-01',
            'github' => 'chorsnell',
            'discord' => null,
        ],
    ],

    // People whose GitHub issues reported a real bug or a reasonable idea, under Bug reports & ideas, most first.
    // Reviewed by hand: outages, questions, "not a bug" and issues closed as not planned don't count.
    //   github   - their GitHub username; the chip links to their issues on this repository
    //   count    - how many of their issues count
    //   featured - true for the biggest contribution: a teal border and the award icon
    'bug_reporters' => [
        ['github' => 'Elitesparkle', 'count' => 29, 'featured' => true],
        ['github' => 'StolltheStoll', 'count' => 5],
        ['github' => 'VirtueAvatar', 'count' => 5],
        ['github' => 'HenrikRaz', 'count' => 4],
        ['github' => 'jgonzalezn', 'count' => 3],
        ['github' => 'Dennisenzo', 'count' => 2],
        ['github' => 'doughill1000', 'count' => 2],
        ['github' => 'FeuLaFeu', 'count' => 2],
        ['github' => 'homeofpoe', 'count' => 2],
        ['github' => 'jared-craig', 'count' => 2],
        ['github' => 'MartinLuccanera', 'count' => 2],
        ['github' => 'Patrick-Sutton', 'count' => 2],
        ['github' => 'worstmedivh', 'count' => 2],
        ['github' => 'AlternatiRP', 'count' => 1],
        ['github' => 'arenoide', 'count' => 1],
        ['github' => 'awktane', 'count' => 1],
        ['github' => 'becnelli', 'count' => 1],
        ['github' => 'camerongraybill', 'count' => 1],
        ['github' => 'Dmitry2803', 'count' => 1],
        ['github' => 'domWalters', 'count' => 1],
        ['github' => 'edward6d', 'count' => 1],
        ['github' => 'Frankpro42', 'count' => 1],
        ['github' => 'glowshroom', 'count' => 1],
        ['github' => 'Haharin', 'count' => 1],
        ['github' => 'heroboy', 'count' => 1],
        ['github' => 'JCplusULTRA', 'count' => 1],
        ['github' => 'jds3d', 'count' => 1],
        ['github' => 'KarmilApple', 'count' => 1],
        ['github' => 'LuminairPrime', 'count' => 1],
        ['github' => 'MakiseKurisu', 'count' => 1],
        ['github' => 'MarMar001', 'count' => 1],
        ['github' => 'matt2802', 'count' => 1],
        ['github' => 'nick2202', 'count' => 1],
        ['github' => 'nightblade9', 'count' => 1],
        ['github' => 'Over327', 'count' => 1],
        ['github' => 'PatrikMosko', 'count' => 1],
        ['github' => 'rakyi', 'count' => 1],
        ['github' => 'RoyM33', 'count' => 1],
        ['github' => 'sandersaelmans', 'count' => 1],
        ['github' => 'SynergyFactor', 'count' => 1],
        ['github' => 'WelsyCZ', 'count' => 1],
    ],
];
