@extends('layouts.api')

@section('title', __('Migrating to API v1'))
@section('meta_description', __('How to move an existing integration from the old Heroes Profile API to v1 of the Heroes of the Storm data API.'))

@section('content')
  <page-heading
    heading="Migrating"
    :infoText1="'What changed between the old API and v1, and what you have to do about it.'"
  ></page-heading>

  <div class="mx-auto max-w-[62.5rem] px-4 mt-6 pb-16">

    <div class="bg-lighten border-l-4 border-yellow p-4 mb-8">
      <p class="text-sm">
        {{ __('The old API keeps working until it is switched off, so nothing breaks while you port. Activating live data on your account is the one step you cannot undo — it expires your old key immediately, so do it when your integration is ready and not before.') }}
      </p>
      <p class="text-sm mt-2">
        {{ __('If v1 does not cover something you rely on today,') }}
        <a href="#missing" class="link">{{ __('tell us before you activate') }}</a> {{ __('— not after.') }}
      </p>
    </div>

    <h2 class="text-2xl mb-3">{{ __('The short version') }}</h2>
    <ul class="list-disc list-inside text-sm space-y-1 mb-8">
      <li>
        {{ __('Base URL is') }} <code class="text-lteal break-all">https://www.heroesprofile.com/api/external/v1</code>{{ __('. Port to that — it works now and keeps working') }}
      </li>
      <li>{{ __('Send your key as') }} <code class="text-lteal">Authorization: Bearer &lt;key&gt;</code> {{ __('instead of') }} <code>?api_token=</code></li>
      <li>{{ __('Create a new key here — old keys do not carry over, and this one is shown once') }}</li>
      <li>{{ __('Most paths changed shape. The') }} <a href="/Api/Docs" class="link">{{ __('docs') }}</a> {{ __('list every one') }}</li>
      <li>{{ __('Global statistics can answer') }} <code class="text-lteal">202</code> {{ __('with a job to poll instead of data') }}</li>
    </ul>

    <h2 class="text-2xl mb-3">{{ __('Base URL') }}</h2>
    <p class="text-sm mb-3">
      <code class="text-lteal break-all">https://www.heroesprofile.com/api/external/v1</code> {{ __('— every path below hangs off that.') }} <code>/v1/heroes</code> {{ __('in the table means') }}
      <code class="break-all">https://www.heroesprofile.com/api/external/v1/heroes</code>.
    </p>
    <p class="text-sm mb-3">
      <strong>{{ __('That is the only base URL, now and later.') }}</strong> {{ __('Port to it once and you are done — there is no second address to move to afterwards.') }}
    </p>
    <p class="text-sm mb-8">
      <code>{{ $domain }}</code> {{ __('is the') }} <em>{{ __('old') }}</em> {{ __('API and stays that way until it is switched off on') }} <strong>{{ __('1 January 2027') }}</strong>{{ __(', after which it simply redirects here. It is not a way to reach v1 at any point, so do not build against it.') }}
    </p>

    <h2 class="text-2xl mb-3">{{ __('Authentication') }}</h2>
    <p class="text-sm mb-3">
      {{ __('Create a new key on your') }} <a href="/Api/Account" class="link">{{ __('account page') }}</a> {{ __('— old keys do not carry over. It is shown once, so copy it then; if you lose it, revoke it and make another.') }}
    </p>
    <p class="text-sm mb-3">
      {{ __('Send it as') }} <code class="text-lteal">Authorization: Bearer &lt;key&gt;</code>.
      <code>?api_token=</code> {{ __('still works but is deprecated and will be removed — query strings end up in server logs, browser history and referrer headers.') }}
    </p>
    <p class="text-sm mb-8">
      {{ __('The') }} <code>/openApi</code> {{ __('endpoints that answered anonymously now need a key —') }}
      <code>/Patches</code>, <code>/Heroes</code>, <code>/Heroes/Talents</code>,
      <code>/Maps</code>, <code>/Replay/Download</code>, <code>/Replay/Max</code> {{ __('and') }}
      <code>/Replay/Min_id</code>{{ __('. The reference four cost effectively nothing against your allowance, but an unauthenticated call now returns') }} <code class="text-lteal">401</code>{{ __('. The uploader endpoints,') }} <code>/Replay/Parsed</code> {{ __('included, are unaffected and stay anonymous permanently.') }}
    </p>

    <h2 class="text-2xl mb-3">{{ __('Where each endpoint went') }}</h2>
    <p class="text-sm mb-4">
      {{ __('Read the notes, not just the paths. The old API answered almost everything with a flat array of database rows; v1 answers with named objects, and several are sectioned or paginated.') }} <strong>{{ __('Most integrations will need their parsing changed, not only their URLs.') }}</strong> {{ __('The') }} <a href="/Api/Docs" class="link">{{ __('docs') }}</a> {{ __('carry the exact shape of every response, taken from real output.') }}
    </p>
    <table class="min-w-0 w-full responsive-table mb-4">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">{{ __('Old') }}</th>
          <th class="py-2 px-3 text-left text-sm">{{ __('New') }}</th>
          <th class="py-2 px-3 text-left text-sm">{{ __('What changed') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="py-2 px-3">/Patches</td><td class="py-2 px-3">/v1/patches</td><td class="py-2 px-3">{{ __('Was an object keyed by major version. Now') }} <code>{patches:[…]}</code>{{ __(', a flat list, with') }} <code>season</code> {{ __('added. Key required') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes</td><td class="py-2 px-3">/v1/heroes</td><td class="py-2 px-3">{{ __('Now') }} <code>{heroes:[…]}</code> {{ __('rather than a bare array. Key required') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents</td><td class="py-2 px-3">/v1/heroes/talents</td><td class="py-2 px-3">{{ __('Now') }} <code>{talents:{…}}</code>{{ __(', still keyed by hero within. Key required') }}</td></tr>
        <tr><td class="py-2 px-3">/Maps</td><td class="py-2 px-3">/v1/maps</td><td class="py-2 px-3">{{ __('Now') }} <code>{maps:[…]}</code> {{ __('rather than a bare array. Key required') }}</td></tr>
        <tr><td class="py-2 px-3">/MMR/Tier</td><td class="py-2 px-3">/v1/mmr/tier</td><td class="py-2 px-3">{{ __('Now') }} <code>{game_type, mmr, tier}</code> {{ __('— the query echoed back with its answer') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Stats</td><td class="py-2 px-3">/v1/heroes/stats</td><td class="py-2 px-3">{{ __('Now an object: per-hero rows alongside') }} <code>average_win_rate</code>, <code>average_popularity</code>, <code>average_ban_rate</code> {{ __('and friends. May answer 202') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Matchups</td><td class="py-2 px-3">/v1/heroes/matchups</td><td class="py-2 px-3">{{ __('Now split into') }} <code>ally</code>, <code>enemy</code> {{ __('and') }} <code>combined</code>{{ __('. May answer 202') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents/Details</td><td class="py-2 px-3">/v1/heroes/talents/details</td><td class="py-2 px-3">{{ __('Now keyed by talent tier —') }} <code>1</code>, <code>4</code>, <code>7</code> … <code>20</code>{{ __('. May answer 202') }}</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents/Builds</td><td class="py-2 px-3">/v1/heroes/talents/builds</td><td class="py-2 px-3">{{ __('A list of builds.') }} <code>talentbuildtype</code> {{ __('now selects the ranking. May answer 202') }}</td></tr>
        <tr><td class="py-2 px-3">/Player</td><td class="py-2 px-3">/v1/players/lookup</td><td class="py-2 px-3"><strong>{{ __('Same information as before') }}</strong> — <code>blizz_id</code>, <code>battletag</code>, <code>region</code>, <code>account_level</code> {{ __('and the') }} <code>profile</code> {{ __('link.') }} <code>region</code> {{ __('is now a number. For career totals, use') }} <code>/v1/players</code>{{ __(', which has a much smaller allowance') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/Replays</td><td class="py-2 px-3">/v1/players/matches</td><td class="py-2 px-3"><strong>{{ __('Paginated.') }}</strong> {{ __('Rows are under') }} <code>data</code>{{ __(', with') }} <code>current_page</code> {{ __('and') }} <code>total</code> {{ __('beside it. Now carries the full stat line') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/Hero/All</td><td class="py-2 px-3">/v1/players/heroes</td><td class="py-2 px-3">{{ __('Still a list. Hero is a nested object now, not an id') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/Hero/Single</td><td class="py-2 px-3">/v1/players/heroes/single</td><td class="py-2 px-3">{{ __('As above, narrowed to one hero') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/Talents/Build</td><td class="py-2 px-3">/v1/players/talents/build</td><td class="py-2 px-3">{{ __('Now') }} <code>{talentData, buildData}</code></td></tr>
        <tr><td class="py-2 px-3">/Player/MMR</td><td class="py-2 px-3">/v1/players/mmr</td><td class="py-2 px-3"><strong>{{ __('Same information as before') }}</strong> {{ __('— current rating per game type, with') }} <code>games_played</code>, <code>games_played_last_90_days</code> {{ __('and') }} <code>league_tier</code>{{ __('. Wrapped as') }} <code>{battletag, region, ratings}</code> {{ __('rather than keyed by battletag, and') }} <code>league_tier</code> {{ __('now carries the sub-tier (') }}<code>Diamond 2</code>{{ __(', not') }} <code>diamond</code>). <code>extra_mmr_info</code> {{ __('still adds the raw rating fields') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/MMR/Hero</td><td class="py-2 px-3">/v1/players/mmr/heroes</td><td class="py-2 px-3">{{ __('As above, for one hero.') }} <code>hero</code> {{ __('is a name') }}</td></tr>
        <tr><td class="py-2 px-3">/Player/MMR/Role</td><td class="py-2 px-3">/v1/players/mmr/roles</td><td class="py-2 px-3">{{ __('As above, for one role') }}</td></tr>
        <tr><td class="py-2 px-3">/Replay/Data</td><td class="py-2 px-3">/v1/replay/{replayID}</td><td class="py-2 px-3">{{ __('Now one match object —') }} <code>players</code>, <code>replay_bans</code>, <code>draft_order</code>, <code>experience_breakdown</code> {{ __('— instead of a flat player-row array. Id is a path segment') }}</td></tr>
        <tr><td class="py-2 px-3">/Replay/Ban</td><td class="py-2 px-3">/v1/replay/{replayID}/bans</td><td class="py-2 px-3">{{ __('Now') }} <code>{replayID, bans}</code></td></tr>
        <tr><td class="py-2 px-3">/Replay/Download</td><td class="py-2 px-3">/v1/download/replay</td><td class="py-2 px-3">{{ __('Unchanged: the file itself. Key required. Note this is the API endpoint — the site\'s own download button is a separate feature and is unaffected') }}</td></tr>
        <tr><td class="py-2 px-3">/Replay/Parsed</td><td class="py-2 px-3">/v1/replays/parsed</td><td class="py-2 px-3">{{ __('Unchanged. Still plain text, still keyless') }}</td></tr>
        <tr><td class="py-2 px-3">/Replay/Min_id</td><td class="py-2 px-3">/v1/replays</td><td class="py-2 px-3">{{ __('Now') }} <code>{replays, next_after, max_replay_id}</code>{{ __('. Cursor is exclusive — see below') }}</td></tr>
        <tr><td class="py-2 px-3">/Replay/Max</td><td class="py-2 px-3">/v1/replays</td><td class="py-2 px-3">{{ __('Folded in as') }} <code>max_replay_id</code>{{ __('. No separate call') }}</td></tr>
        <tr><td class="py-2 px-3">/NGS/Replay/Data</td><td class="py-2 px-3">/v1/ngs/replay/{replayID}</td><td class="py-2 px-3">{{ __('The same changes as') }} <code>/Replay/Data</code>{{ __(': one match object, and the id is a path segment') }}</td></tr>
        <tr><td class="py-2 px-3">/NGS/Player/Profile</td><td class="py-2 px-3">/v1/ngs/player</td><td class="py-2 px-3">{{ __('Still takes a') }} <code>battletag</code> {{ __('on its own, or a') }} <code>blizz_id</code>{{ __('. Now returns what the site\'s NGS player page shows — heroes, maps, teams and talents — rather than one row of totals. A name without the') }} <code>#1234</code> {{ __('works only if one NGS player has it') }}</td></tr>
        <tr><td class="py-2 px-3">/NGS/Hero/Stat</td><td class="py-2 px-3">{{ __('/v1/ngs/heroes/stats, /v1/ngs/player/hero') }}</td><td class="py-2 px-3">{{ __('Split in two. League-wide numbers for every hero come from') }} <code>ngs/heroes/stats</code>{{ __('; one player on one hero, the old') }} <code>battletag</code> {{ __('form, from') }} <code>ngs/player/hero</code></td></tr>
        <tr><td class="py-2 px-3">/NGS/Match</td><td class="py-2 px-3">/v1/ngs/team/matches</td><td class="py-2 px-3">{{ __('A team\'s matches, newest first and') }} <strong>{{ __('paginated') }}</strong>{{ __('. Filter by') }} <code>season</code> {{ __('and') }} <code>division</code>{{ __('; there is no') }} <code>round</code> {{ __('filter, but every row carries its') }} <code>round</code></td></tr>
        <tr><td class="py-2 px-3">/NGS/Games/Upload</td><td class="py-2 px-3">/v1/ngs/games/upload</td><td class="py-2 px-3"><strong>{{ __('POST only') }}</strong> {{ __('— the GET form is gone. Needs an account granted NGS upload access, and the key goes in the header like every other call rather than in the body. The four map-ban fields are now required') }}</td></tr>
        <tr><td class="py-2 px-3">/replays/fingerprints/{fp}</td><td class="py-2 px-3">/v1/replays/fingerprints/{fp}</td><td class="py-2 px-3">{{ __('Unchanged. Still keyless') }}</td></tr>
        <tr><td class="py-2 px-3">/upload/heroesprofile/{source}</td><td class="py-2 px-3">/v1/upload/heroesprofile/{source}</td><td class="py-2 px-3">{{ __('Unchanged contract') }}</td></tr>
      </tbody>
    </table>
    <p class="text-sm text-gray-medium mb-8">
      {{ __('v1 also adds endpoints the old API never had — compositions, draft, party, per-map and per-role player breakdowns, friend/foe, matchups, leaderboards and the talent builder, and for NGS standings, teams, divisions, player search and match histories. The') }} <a href="/Api/Docs" class="link">{{ __('docs') }}</a> {{ __('list them all.') }}
    </p>

    <h2 class="text-2xl mb-3">{{ __('New in v1') }}</h2>
    <p class="text-sm mb-3">
      {{ __('Things the old API had no equivalent for. Nothing here is required to port — they are additions, not replacements.') }}
    </p>
    <ul class="list-disc list-inside text-sm space-y-1 mb-4">
      <li>
        <code class="text-lteal">/v1/players/mmr/history</code>{{ __(', and') }}
        <code>/history/heroes</code> {{ __('and') }} <code>/history/roles</code> {{ __('beside it — rating over time, one entry per match, alongside the tier bands it moved through.') }}
        <code>/v1/players/mmr</code> {{ __('itself is unchanged in meaning from the old') }}
        <code>/Player/MMR</code>
      </li>
      <li>
        <code class="text-lteal">/v1/players</code> {{ __('— a player\'s career totals, the same figures as their profile page. The old') }} <code>/Player</code> {{ __('only identified the player; that is now') }} <code>/v1/players/lookup</code>
      </li>
      <li>
        <code class="text-lteal">{{ __('Names instead of codes.') }}</code> <code>game_type</code>
        {{ __('takes') }} <code>Storm League</code> {{ __('as well as') }} <code>sl</code>{{ __(', and') }}
        <code>region</code> {{ __('takes') }} <code>NA</code> {{ __('as well as') }} <code>1</code>{{ __('. Both forms work everywhere, so nothing you already send needs changing') }}
      </li>
      <li>
        <code class="text-lteal">/v1/replay/{replayID}</code> {{ __('returns') }} <strong>{{ __('full battletags') }}</strong>{{ __(', discriminator included. The site\'s own match page shows only the name; an API caller gets the whole thing, because that is what every player endpoint takes as input. Players with a private profile are still withheld entirely') }}
      </li>
    </ul>

    <h2 class="text-2xl mb-3">{{ __('Smaller differences worth knowing') }}</h2>
    <ul class="list-disc list-inside text-sm space-y-1 mb-8">
      <li>
        <code>/v1/patches</code> {{ __('lists only patches global statistics will accept. The old endpoint returned every patch ever recorded, including ones') }}
        <code>timeframe</code> {{ __('then rejected') }}
      </li>
      <li>
        <code>/v1/heroes</code> {{ __('no longer carries') }} <code>rework_date</code>,
        <code>last_change_patch_version</code> {{ __('or') }} <code>last_updated</code> {{ __('— they were not reliable enough to publish') }}
      </li>
      <li>
        <code>league_tier</code> {{ __('on the MMR endpoints now includes the sub-tier —') }}
        <code>Diamond 2</code> {{ __('where the old API said') }} <code>diamond</code>
      </li>
    </ul>

    <h2 class="text-2xl mb-3">{{ __('Removed') }}</h2>
    <table class="min-w-0 w-full responsive-table mb-8">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">{{ __('Endpoint') }}</th>
          <th class="py-2 px-3 text-left text-sm">{{ __('Why, and what to do') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="py-2 px-3">/Player/PreMatch</td>
          <td class="py-2 px-3">{{ __('Per-player stat averages. Nothing on the site produces this shape any more.') }} <code>/v1/players/heroes</code> {{ __('covers the same ground.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/Heroesprofile/ReplayID</td>
          <td class="py-2 px-3">{{ __('A hotsapi id lookup. hotsapi sync is retired.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/openApi/Parse/Replay</td>
          <td class="py-2 px-3">{{ __('Fetched an arbitrary URL server-side. Not reproduced, deliberately. Upload the file instead.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">{{ __('/NGS/Leaderboard/Highest/Average/Stat, /NGS/Leaderboard/Highest/Total/Stat') }}</td>
          <td class="py-2 px-3">{{ __('Nothing on the site\'s NGS pages ranks players by a single stat, so there is no equivalent.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/NGS/Games/Delete</td>
          <td class="py-2 px-3">{{ __('No longer available with an NGS key. If a game needs removing,') }} <a href="#missing" class="link">{{ __('get in touch') }}</a>.</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/ML/Games/Upload</td>
          <td class="py-2 px-3">{{ __('No replacement.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">{{ __('/CCL/*, /HI/*') }}</td>
          <td class="py-2 px-3">{{ __('Those competitions are no longer covered.') }}</td>
        </tr>
      </tbody>
    </table>

    <h2 class="text-2xl mb-3">{{ __('Response changes that will bite') }}</h2>

    <h3 class="text-lg mt-6 mb-2">{{ __('Errors have a status code and an envelope') }}</h3>
    <p class="text-sm mb-3">
      {{ __('The old API answered errors as plain strings with') }} <code>HTTP 200</code>{{ __('. v1 uses real status codes and a consistent body, so a client can branch on the status rather than pattern-matching prose:') }}
    </p>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-3">{"error":{"code":"quota_exceeded","message":"Weekly limit of 1,000 calls reached for this endpoint."}}</pre>
    <p class="text-sm mb-6">
      <code class="text-lteal">401</code> {{ __('no key,') }} <code class="text-lteal">403</code> {{ __('not in your plan,') }} <code class="text-lteal">404</code> {{ __('nothing found,') }}
      <code class="text-lteal">422</code> {{ __('a bad parameter (with an') }} <code>errors</code> {{ __('list saying what),') }}
      <code class="text-lteal">429</code> {{ __('out of quota, rate limited, or at your active job limit (') }}<code>too_many_active_jobs</code>{{ __('). A call that answers with an error is not charged against your allowance.') }}
    </p>

    <h3 class="text-lg mb-2">{{ __('Global statistics may answer 202 instead of data') }}</h3>
    <p class="text-sm mb-3">
      {{ __('This is the one change with no equivalent in the old API, and the one most likely to look like a failure if you are not expecting it.') }}
    </p>
    <p class="text-sm mb-3">
      {{ __('Global statistics aggregate across every match in a patch. When the answer is not already cached that query can run for') }} <strong>{{ __('several minutes') }}</strong>{{ __('. The old API simply held the connection open, which meant it often exceeded the client\'s own timeout and returned nothing after tying up a worker for the whole wait. Neither side got anything useful.') }}
    </p>
    <p class="text-sm mb-4">
      {{ __('So v1 does not wait. A cached answer comes straight back as') }}
      <code class="text-lteal">200</code>{{ __(', exactly as before — which is what most calls get. A cache miss starts the work in the background and hands you a job to collect it from.') }}
    </p>

    <h4 class="text-sm uppercase text-lteal mb-2">{{ __('1. Your call returns 202') }}</h4>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-2">GET /v1/heroes/stats?timeframe_type=minor&amp;timeframe=2.55.17.97771&amp;game_type=sl

HTTP/1.1 202 Accepted
Location: /v1/jobs/9f2c1a7e-...
Retry-After: 10

{"async": true, "status": "queued", "job_id": "9f2c1a7e-..."}</pre>
    <p class="text-sm mb-4">
      <code>Location</code> {{ __('is where to collect it and') }} <code>Retry-After</code> {{ __('is how long to wait between attempts, in seconds.') }} <strong>{{ __('Your quota is charged here') }}</strong>{{ __(', once, when the job is created. With') }} <code>mode=csv</code> {{ __('this answer is still JSON; the CSV comes from the job.') }}
    </p>

    <h4 class="text-sm uppercase text-lteal mb-2">{{ __('2. Poll the job until it is ready') }}</h4>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-2">GET /v1/jobs/9f2c1a7e-...

HTTP/1.1 202 Accepted        still working
{"async": true, "status": "processing", "job_id": "9f2c1a7e-..."}

HTTP/1.1 200 OK              done — this is the data
{ … the same body a cache hit would have returned … }</pre>
    <p class="text-sm mb-4">
      <strong>{{ __('Polling costs no quota') }}</strong>{{ __(', so poll as long as you need. Keep going while the status is') }} <code class="text-lteal">202</code>{{ __('. When it turns') }}
      <code class="text-lteal">200</code> {{ __('the body is the result, in exactly the shape the endpoint documents — there is no envelope to unwrap.') }}
    </p>

    <h4 class="text-sm uppercase text-lteal mb-2">{{ __('3. Handle the failure cases') }}</h4>
    <table class="min-w-0 w-full responsive-table mb-4">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">{{ __('Status') }}</th>
          <th class="py-2 px-3 text-left text-sm">{{ __('Body') }}</th>
          <th class="py-2 px-3 text-left text-sm">{{ __('What it means') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="py-2 px-3">404</td>
          <td class="py-2 px-3"><code>status: not_found</code></td>
          <td class="py-2 px-3">{{ __('Unknown or expired job id. Start the original call again.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">500</td>
          <td class="py-2 px-3"><code>status: failed</code></td>
          <td class="py-2 px-3">{{ __('The query itself failed, with a reason in') }} <code>error</code>{{ __('. Retrying the original call is reasonable; retrying the job id is not.') }}</td>
        </tr>
        <tr>
          <td class="py-2 px-3">422</td>
          <td class="py-2 px-3"><code>query_too_large</code></td>
          <td class="py-2 px-3">{{ __('The query ran for more than 45 minutes and was stopped. Narrow the call — a shorter timeframe, or fewer game types, leagues, maps or heroes. The same call is refused straight away for 24 hours.') }}</td>
        </tr>
      </tbody>
    </table>

    <h4 class="text-sm uppercase text-lteal mb-2">{{ __('What this means for your code') }}</h4>
    <ul class="list-disc list-inside text-sm space-y-1 mb-6">
      <li>{{ __('Treat') }} <code>202</code> {{ __('as success-in-progress, not an error. Anything checking') }} <code>status === 200</code> {{ __('will drop these on the floor.') }}</li>
      <li>{{ __('Follow') }} <code>Location</code> {{ __('rather than building the job URL yourself.') }}</li>
      <li>{{ __('Respect') }} <code>Retry-After</code>{{ __('. Polling every 10 seconds is free; polling every 100ms is a rate limit waiting to happen — the per-key limiter still applies to job polls.') }}</li>
      <li>{{ __('Give up after a sensible ceiling. A cold query is minutes, not hours.') }}</li>
      <li>{{ __('Don\'t start every query at once. Each account can have 3 active jobs on Basic, 5 on Intermediate and 10 on Developer; past that a new cold call answers') }} <code>429 too_many_active_jobs</code>{{ __('. Start more as earlier ones finish.') }}</li>
      <li>{{ __('Once one caller has warmed a patch, everyone gets') }} <code>200</code> {{ __('for it. In steady use the 202 path is uncommon.') }}</li>
    </ul>

    <h3 class="text-lg mb-2">{{ __('Match length is a number') }}</h3>
    <p class="text-sm mb-6">
      <code>game_length</code> {{ __('was') }} <code>"12 minutes 7 seconds"</code> {{ __('on the match endpoints and a number of seconds everywhere else. It is now') }}
      <strong>{{ __('seconds everywhere') }}</strong>{{ __('. Parse it as an integer.') }}
    </p>

    <h3 class="text-lg mb-2">{{ __('Bulk replay iteration is cursor-paged') }}</h3>
    <p class="text-sm mb-3">
      <code>/Replay/Min_id</code> {{ __('took an inclusive') }} <code>min_id</code>. <code>/v1/replays</code>
      {{ __('takes an') }} <strong>{{ __('exclusive') }}</strong> <code>after</code>{{ __(': pass back the last id you saw and you get what follows, rather than that row again. Stop when') }}
      <code>next_after</code> {{ __('comes back') }} <code>null</code>.
    </p>
    <p class="text-sm mb-6">
      {{ __('It no longer returns a bucket URL. Use') }} <code>/v1/download/replay?replayID=</code>{{ __(', and read') }} <code>downloadable</code> {{ __('first — replay files are kept on a rolling window, so older matches have no file to fetch.') }}
    </p>

    <h3 class="text-lg mb-2">{{ __('Heroes, maps and roles are always names') }}</h3>
    <p class="text-sm mb-6">
      {{ __('Some old endpoints took a hero id and others a name. v1 takes a') }}
      <strong>{{ __('name') }}</strong> {{ __('everywhere and translates internally. The') }}
      <a href="/Api/Docs#variables" class="link">{{ __('Variables') }}</a> {{ __('section lists every accepted value for every parameter.') }}
    </p>

    <h3 class="text-lg mb-2">{{ __('Older patches are no longer queryable') }}</h3>
    <p class="text-sm mb-8">
      {{ __('Global statistics accept patches back to') }} <code class="text-lteal">{{ $minimumPatch }}</code>
      {{ __('at the furthest. Each endpoint starts where its own data does — the same limit the site applies to that page\'s filters — and') }} <code>/v1/patches</code> {{ __('lists the oldest patch for each under') }} <code>oldest_patch</code>{{ __('. Asking for an older one returns') }}
      <code>422 timeframe_unavailable</code>.
    </p>

    <h2 class="text-2xl mb-3">{{ __('Testing before you switch') }}</h2>
    <p class="text-sm mb-3">
      {{ __('A new account receives') }} <strong>{{ __('example data') }}</strong> {{ __('until you activate live data: real response shapes with placeholder values, costing no quota. Build against that, then activate when you are ready. You can switch back to example data at any time from your') }} <a href="/Api/Account" class="link">{{ __('account page') }}</a>.
    </p>
    <p class="text-sm mb-8">
      {{ __('Every read endpoint can also be run from the') }} <a href="/Api/Docs" class="link">{{ __('docs') }}</a>
      {{ __('with your own key, which is the quickest way to see a real response shape. The ones that change data — uploads — cannot.') }}
    </p>

    <h2 id="missing" class="text-2xl mb-3">{{ __('If something you need is missing') }}</h2>
    <p class="text-sm mb-3">
      {{ __('v1 is a rewrite, not a port, and a few things the old API returned were dropped deliberately. If you depend on something that is gone, or a field you used is not in the new response,') }} <strong>{{ __('get in touch before you activate live data') }}</strong>.
    </p>
    <p class="text-sm mb-3">
      {{ __('The timing matters. Activating expires your old key immediately, so finding the gap afterwards leaves you with no working key on either API until it is resolved. Check the') }} <a href="/Api/Docs" class="link">{{ __('docs') }}</a> {{ __('against what you actually use first, and raise anything missing while your old key still works.') }}
    </p>
    <p class="text-sm mb-3">
      {{ __('Email') }} <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>
      {{ __('or open an issue on') }}
      <a class="link" href="https://github.com/Heroes-Profile/heroesprofile/issues/new" target="_blank" rel="noopener">{{ __('GitHub') }}</a>{{ __('. Either is fine — GitHub is easier to track, email is fine if the details are private.') }}
    </p>
    <p class="text-sm">
      {{ __('Tell us which endpoint and field, and what you use it for. The second part is the useful one: it is often available under a different name or shape, and knowing the purpose is what lets us point you at it — or decide it is worth adding back.') }}
    </p>
  </div>
@endsection
