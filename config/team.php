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
//   email, reddit, discord - how to reach them; leave any out (or null) that they don't have.
//                            reddit is the username without u/; discord is the username

return [
    'members' => [
        [
            'name' => 'Zemill',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Backend',
            'start' => '2018-06',
            'end' => null,
            'bio' => 'Bio for Zemill goes here.',
            'peak_rank' => ['league' => 'master'],
            'email' => 'zemill@heroesprofile.com',
            'reddit' => 'heroesprofile',
            'discord' => 'zemill',
        ],
        [
            'name' => 'Avadriel',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Frontend',
            'start' => '2018-06',
            'end' => null,
            'bio' => 'Bio for Avadriel goes here.',
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
    'contributors' => [
        [
            'name' => 'Bluray',
            'contribution' => 'Designed the 2026 site redesign',
            'link' => '/Redesign/2026',
            'start' => '2026-10',
            'end' => '2026-12',
            'discord' => 'blurayy',
        ],
    ],
];
