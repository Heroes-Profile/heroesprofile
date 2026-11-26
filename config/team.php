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
