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
//   email, reddit, discord - how to reach them; leave any out (or null) that they don't have.
//                            reddit is the username without u/; discord is the username

return [
    'members' => [
        [
            'name' => 'Zemill',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Backend, cloud and site infrastructure · Social media presence',
            'start' => '2018-06',
            'end' => null,
            'bio' => 'Bio for Zemill goes here.',
            'email' => 'zemill@heroesprofile.com',
            'reddit' => 'heroesprofile',
            'discord' => 'zemill',
        ],
        [
            'name' => 'Avadriel',
            'company' => 'Heroes Profile | Skill Tree Development LLC',
            'role' => 'Frontend and site consistency',
            'start' => '2018-06',
            'end' => null,
            'bio' => 'Bio for Avadriel goes here.',
            'email' => 'avadriel@heroesprofile.com',
            'reddit' => 'heroesprofile',
            'discord' => null,
        ],
        [
            'name' => 'Bluray',
            'company' => 'Heroes Profile',
            'role' => 'Design',
            'start' => '2026-10',
            'end' => null,
            'bio' => 'Bio for Bluray goes here.',
            'email' => null,
            'reddit' => null,
            'discord' => 'blurayy',
        ],
    ],
];
