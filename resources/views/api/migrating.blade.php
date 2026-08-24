@extends('layouts.api')

@section('title', 'Migrating')
@section('meta_description', 'How to move an existing integration from the old Heroes Profile API to v1.')

@section('content')
  <page-heading
    heading="Migrating"
    :infoText1="'What changed between the old API and v1, and what you have to do about it.'"
  ></page-heading>

  <div class="mx-auto max-w-[1000px] px-4 mt-6 pb-16">

    <div class="bg-lighten border-l-4 border-yellow p-4 mb-8">
      <p class="text-sm">
        The old API keeps working until it is switched off, so nothing breaks while you
        port. Activating live data on your account is the one step you cannot undo — it
        expires your old key immediately, so do it when your integration is ready and not
        before.
      </p>
      <p class="text-sm mt-2">
        If v1 does not cover something you rely on today,
        <a href="#missing" class="link">tell us before you activate</a> — not after.
      </p>
    </div>

    <h2 class="text-2xl mb-3">The short version</h2>
    <ul class="list-disc list-inside text-sm space-y-1 mb-8">
      <li>
        Base URL is <code class="text-lteal">https://www.heroesprofile.com/api/external/v1</code>.
        Port to that — it works now and keeps working
      </li>
      <li>Send your key as <code class="text-lteal">Authorization: Bearer &lt;key&gt;</code> instead of <code>?api_token=</code></li>
      <li>Create a new key here — old keys do not carry over, and this one is shown once</li>
      <li>Most paths changed shape. The <a href="/Api/Docs" class="link">docs</a> list every one</li>
      <li>Global statistics can answer <code class="text-lteal">202</code> with a job to poll instead of data</li>
    </ul>

    <h2 class="text-2xl mb-3">Base URL</h2>
    <p class="text-sm mb-3">
      <code class="text-lteal">https://www.heroesprofile.com/api/external/v1</code> — every
      path below hangs off that. <code>/v1/heroes</code> in the table means
      <code>https://www.heroesprofile.com/api/external/v1/heroes</code>.
    </p>
    <p class="text-sm mb-3">
      <strong>That is the only base URL, now and later.</strong> Port to it once and you
      are done — there is no second address to move to afterwards.
    </p>
    <p class="text-sm mb-8">
      <code>{{ $domain }}</code> is the <em>old</em> API and stays that way until it is
      switched off on <strong>1 January 2027</strong>, after which it simply redirects
      here. It is not a way to reach v1 at any point, so do not build against it.
    </p>

    <h2 class="text-2xl mb-3">Authentication</h2>
    <p class="text-sm mb-3">
      Create a new key on your <a href="/Api/Account" class="link">account page</a> — old
      keys do not carry over. It is shown once, so copy it then; if you lose it, revoke it
      and make another.
    </p>
    <p class="text-sm mb-3">
      Send it as <code class="text-lteal">Authorization: Bearer &lt;key&gt;</code>.
      <code>?api_token=</code> still works but is deprecated and will be removed — query
      strings end up in server logs, browser history and referrer headers.
    </p>
    <p class="text-sm mb-8">
      Four endpoints that used to answer anonymously now need a key —
      <code>/Patches</code>, <code>/Heroes</code>, <code>/Heroes/Talents</code> and
      <code>/Maps</code>. They cost effectively nothing against your allowance, but an
      unauthenticated call now returns <code class="text-lteal">401</code>. The uploader
      endpoints are unaffected and stay anonymous permanently.
    </p>

    <h2 class="text-2xl mb-3">Where each endpoint went</h2>
    <p class="text-sm mb-4">
      Read the notes, not just the paths. The old API answered almost everything with a
      flat array of database rows; v1 answers with named objects, and several are
      sectioned or paginated. <strong>Most integrations will need their parsing changed,
      not only their URLs.</strong> The <a href="/Api/Docs" class="link">docs</a> carry the
      exact shape of every response, taken from real output.
    </p>
    <table class="min-w-0 w-full responsive-table mb-4">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">Old</th>
          <th class="py-2 px-3 text-left text-sm">New</th>
          <th class="py-2 px-3 text-left text-sm">What changed</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="py-2 px-3">/Patches</td><td class="py-2 px-3">/v1/patches</td><td class="py-2 px-3">Was an object keyed by major version. Now <code>{patches:[…]}</code>, a flat list, with <code>season</code> added. Key required</td></tr>
        <tr><td class="py-2 px-3">/Heroes</td><td class="py-2 px-3">/v1/heroes</td><td class="py-2 px-3">Now <code>{heroes:[…]}</code> rather than a bare array. Key required</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents</td><td class="py-2 px-3">/v1/heroes/talents</td><td class="py-2 px-3">Now <code>{talents:{…}}</code>, still keyed by hero within. Key required</td></tr>
        <tr><td class="py-2 px-3">/Maps</td><td class="py-2 px-3">/v1/maps</td><td class="py-2 px-3">Now <code>{maps:[…]}</code> rather than a bare array. Key required</td></tr>
        <tr><td class="py-2 px-3">/MMR/Tier</td><td class="py-2 px-3">/v1/mmr/tier</td><td class="py-2 px-3">Now <code>{game_type, mmr, tier}</code> — the query echoed back with its answer</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Stats</td><td class="py-2 px-3">/v1/heroes/stats</td><td class="py-2 px-3">Now an object: per-hero rows alongside <code>average_win_rate</code>, <code>average_popularity</code>, <code>average_ban_rate</code> and friends. May answer 202</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Matchups</td><td class="py-2 px-3">/v1/heroes/matchups</td><td class="py-2 px-3">Now split into <code>ally</code>, <code>enemy</code> and <code>combined</code>. May answer 202</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents/Details</td><td class="py-2 px-3">/v1/heroes/talents/details</td><td class="py-2 px-3">Now keyed by talent tier — <code>1</code>, <code>4</code>, <code>7</code> … <code>20</code>. May answer 202</td></tr>
        <tr><td class="py-2 px-3">/Heroes/Talents/Builds</td><td class="py-2 px-3">/v1/heroes/talents/builds</td><td class="py-2 px-3">A list of builds. <code>talentbuildtype</code> now selects the ranking. May answer 202</td></tr>
        <tr><td class="py-2 px-3">/Player</td><td class="py-2 px-3">/v1/players</td><td class="py-2 px-3">Now one object of career aggregates rather than a row array</td></tr>
        <tr><td class="py-2 px-3">/Player/Replays</td><td class="py-2 px-3">/v1/players/matches</td><td class="py-2 px-3"><strong>Paginated.</strong> Rows are under <code>data</code>, with <code>current_page</code> and <code>total</code> beside it. Now carries the full stat line</td></tr>
        <tr><td class="py-2 px-3">/Player/Hero/All</td><td class="py-2 px-3">/v1/players/heroes</td><td class="py-2 px-3">Still a list. Hero is a nested object now, not an id</td></tr>
        <tr><td class="py-2 px-3">/Player/Hero/Single</td><td class="py-2 px-3">/v1/players/heroes/single</td><td class="py-2 px-3">As above, narrowed to one hero</td></tr>
        <tr><td class="py-2 px-3">/Player/Talents/Build</td><td class="py-2 px-3">/v1/players/talents/build</td><td class="py-2 px-3">Now <code>{talentData, buildData}</code></td></tr>
        <tr><td class="py-2 px-3">/Player/MMR</td><td class="py-2 px-3">/v1/players/mmr</td><td class="py-2 px-3">Now <code>{tableData, leagueData}</code> — history plus the tier bands it sits in</td></tr>
        <tr><td class="py-2 px-3">/Player/MMR/Hero</td><td class="py-2 px-3">/v1/players/mmr/heroes</td><td class="py-2 px-3">Same two-section shape</td></tr>
        <tr><td class="py-2 px-3">/Player/MMR/Role</td><td class="py-2 px-3">/v1/players/mmr/roles</td><td class="py-2 px-3">Same two-section shape</td></tr>
        <tr><td class="py-2 px-3">/Replay/Data</td><td class="py-2 px-3">/v1/matches/{replayID}</td><td class="py-2 px-3">Now one match object — <code>players</code>, <code>replay_bans</code>, <code>draft_order</code>, <code>experience_breakdown</code> — instead of a flat player-row array. Id is a path segment</td></tr>
        <tr><td class="py-2 px-3">/Replay/Ban</td><td class="py-2 px-3">/v1/matches/{replayID}/bans</td><td class="py-2 px-3">Now <code>{replayID, bans}</code></td></tr>
        <tr><td class="py-2 px-3">/Replay/Download</td><td class="py-2 px-3">/v1/replays/download</td><td class="py-2 px-3">Unchanged: the file itself. Key required. Note this is the API endpoint — the site's own download button is a separate feature and is unaffected</td></tr>
        <tr><td class="py-2 px-3">/Replay/Parsed</td><td class="py-2 px-3">/v1/replays/parsed</td><td class="py-2 px-3">Unchanged. Still plain text, still keyless</td></tr>
        <tr><td class="py-2 px-3">/Replay/Min_id</td><td class="py-2 px-3">/v1/replays</td><td class="py-2 px-3">Now <code>{replays, next_after, max_replay_id}</code>. Cursor is exclusive — see below</td></tr>
        <tr><td class="py-2 px-3">/Replay/Max</td><td class="py-2 px-3">/v1/replays</td><td class="py-2 px-3">Folded in as <code>max_replay_id</code>. No separate call</td></tr>
        <tr><td class="py-2 px-3">/NGS/*</td><td class="py-2 px-3">/v1/ngs/*</td><td class="py-2 px-3">Same six reads, lowercased. Each is an object rather than a row array</td></tr>
        <tr><td class="py-2 px-3">/openApi/PreMatch</td><td class="py-2 px-3">/v1/prematch</td><td class="py-2 px-3">Unchanged. Still a bare integer, still keyless</td></tr>
        <tr><td class="py-2 px-3">/replays/fingerprints/{fp}</td><td class="py-2 px-3">/v1/replays/fingerprints/{fp}</td><td class="py-2 px-3">Unchanged. Still keyless</td></tr>
        <tr><td class="py-2 px-3">/upload/heroesprofile/{source}</td><td class="py-2 px-3">/v1/upload/heroesprofile/{source}</td><td class="py-2 px-3">Unchanged contract</td></tr>
      </tbody>
    </table>
    <p class="text-sm text-gray-medium mb-8">
      v1 also adds endpoints the old API never had — compositions, draft, party, per-map
      and per-role player breakdowns, friend/foe, matchups, leaderboards and the talent
      builder. The <a href="/Api/Docs" class="link">docs</a> list them all.
    </p>

    <h2 class="text-2xl mb-3">Removed</h2>
    <table class="min-w-0 w-full responsive-table mb-8">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">Endpoint</th>
          <th class="py-2 px-3 text-left text-sm">Why, and what to do</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="py-2 px-3">/Player/PreMatch</td>
          <td class="py-2 px-3">Per-player stat averages. Nothing on the site produces this shape any more. <code>/v1/players/heroes</code> covers the same ground.</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/Heroesprofile/ReplayID</td>
          <td class="py-2 px-3">A hotsapi id lookup. hotsapi sync is retired.</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/openApi/Parse/Replay</td>
          <td class="py-2 px-3">Fetched an arbitrary URL server-side. Not reproduced, deliberately. Upload the file instead.</td>
        </tr>
        <tr>
          <td class="py-2 px-3">/CCL/*, /HI/*</td>
          <td class="py-2 px-3">Those competitions are no longer covered.</td>
        </tr>
      </tbody>
    </table>

    <h2 class="text-2xl mb-3">Response changes that will bite</h2>

    <h3 class="text-lg mt-6 mb-2">Errors have a status code and an envelope</h3>
    <p class="text-sm mb-3">
      The old API answered errors as plain strings with <code>HTTP 200</code>. v1 uses real
      status codes and a consistent body, so a client can branch on the status rather than
      pattern-matching prose:
    </p>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-3">{"error":{"code":"quota_exceeded","message":"Weekly limit of 1,000 calls reached for this endpoint."}}</pre>
    <p class="text-sm mb-6">
      <code class="text-lteal">401</code> no key, <code class="text-lteal">403</code> not in
      your plan, <code class="text-lteal">422</code> a bad parameter,
      <code class="text-lteal">429</code> out of quota or rate limited.
    </p>

    <h3 class="text-lg mb-2">Global statistics may answer 202 instead of data</h3>
    <p class="text-sm mb-3">
      This is the one change with no equivalent in the old API, and the one most likely
      to look like a failure if you are not expecting it.
    </p>
    <p class="text-sm mb-3">
      Global statistics aggregate across every match in a patch. When the answer is not
      already cached that query can run for <strong>several minutes</strong>. The old API
      simply held the connection open, which meant it often exceeded the client's own
      timeout and returned nothing after tying up a worker for the whole wait. Neither
      side got anything useful.
    </p>
    <p class="text-sm mb-4">
      So v1 does not wait. A cached answer comes straight back as
      <code class="text-lteal">200</code>, exactly as before — which is what most calls
      get. A cache miss starts the work in the background and hands you a job to collect
      it from.
    </p>

    <h4 class="text-sm uppercase tracking-wider text-lteal mb-2">1. Your call returns 202</h4>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-2">GET /v1/heroes/stats?timeframe_type=minor&amp;timeframe=2.55.17.97771&amp;game_type=sl

HTTP/1.1 202 Accepted
Location: /v1/jobs/9f2c1a7e-...
Retry-After: 10

{"async": true, "status": "queued", "job_id": "9f2c1a7e-..."}</pre>
    <p class="text-sm mb-4">
      <code>Location</code> is where to collect it and <code>Retry-After</code> is how long
      to wait between attempts, in seconds. <strong>Your quota is charged here</strong>,
      once, when the job is created.
    </p>

    <h4 class="text-sm uppercase tracking-wider text-lteal mb-2">2. Poll the job until it is ready</h4>
    <pre class="bg-darken p-3 text-xs overflow-x-auto mb-2">GET /v1/jobs/9f2c1a7e-...

HTTP/1.1 202 Accepted        still working
{"async": true, "status": "processing", "job_id": "9f2c1a7e-..."}

HTTP/1.1 200 OK              done — this is the data
{ … the same body a cache hit would have returned … }</pre>
    <p class="text-sm mb-4">
      <strong>Polling costs no quota</strong>, so poll as long as you need. Keep going
      while the status is <code class="text-lteal">202</code>. When it turns
      <code class="text-lteal">200</code> the body is the result, in exactly the shape the
      endpoint documents — there is no envelope to unwrap.
    </p>

    <h4 class="text-sm uppercase tracking-wider text-lteal mb-2">3. Handle the two failure cases</h4>
    <table class="min-w-0 w-full responsive-table mb-4">
      <thead>
        <tr>
          <th class="py-2 px-3 text-left text-sm">Status</th>
          <th class="py-2 px-3 text-left text-sm">Body</th>
          <th class="py-2 px-3 text-left text-sm">What it means</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="py-2 px-3">404</td>
          <td class="py-2 px-3"><code>status: not_found</code></td>
          <td class="py-2 px-3">Unknown or expired job id. Start the original call again.</td>
        </tr>
        <tr>
          <td class="py-2 px-3">500</td>
          <td class="py-2 px-3"><code>status: failed</code></td>
          <td class="py-2 px-3">The query itself failed, with a reason in <code>error</code>. Retrying the original call is reasonable; retrying the job id is not.</td>
        </tr>
      </tbody>
    </table>

    <h4 class="text-sm uppercase tracking-wider text-lteal mb-2">What this means for your code</h4>
    <ul class="list-disc list-inside text-sm space-y-1 mb-6">
      <li>Treat <code>202</code> as success-in-progress, not an error. Anything checking <code>status === 200</code> will drop these on the floor.</li>
      <li>Follow <code>Location</code> rather than building the job URL yourself.</li>
      <li>Respect <code>Retry-After</code>. Polling every 10 seconds is free; polling every 100ms is a rate limit waiting to happen — the per-key limiter still applies to job polls.</li>
      <li>Give up after a sensible ceiling. A cold query is minutes, not hours.</li>
      <li>Once one caller has warmed a patch, everyone gets <code>200</code> for it. In steady use the 202 path is uncommon.</li>
    </ul>

    <h3 class="text-lg mb-2">Match length is a number</h3>
    <p class="text-sm mb-6">
      <code>game_length</code> was <code>"12 minutes 7 seconds"</code> on the match
      endpoints and a number of seconds everywhere else. It is now
      <strong>seconds everywhere</strong>. Parse it as an integer.
    </p>

    <h3 class="text-lg mb-2">Bulk replay iteration is cursor-paged</h3>
    <p class="text-sm mb-3">
      <code>/Replay/Min_id</code> took an inclusive <code>min_id</code>. <code>/v1/replays</code>
      takes an <strong>exclusive</strong> <code>after</code>: pass back the last id you saw
      and you get what follows, rather than that row again. Stop when
      <code>next_after</code> comes back <code>null</code>.
    </p>
    <p class="text-sm mb-6">
      It no longer returns a bucket URL. Use <code>/v1/replays/download?replayID=</code>,
      and read <code>downloadable</code> first — replay files are kept on a rolling window,
      so older matches have no file to fetch.
    </p>

    <h3 class="text-lg mb-2">Heroes, maps and roles are always names</h3>
    <p class="text-sm mb-6">
      Some old endpoints took a hero id and others a name. v1 takes a
      <strong>name</strong> everywhere and translates internally. The
      <a href="/Api/Docs#variables" class="link">Variables</a> section lists every accepted
      value for every parameter.
    </p>

    <h3 class="text-lg mb-2">Older patches are no longer queryable</h3>
    <p class="text-sm mb-8">
      Global statistics accept patches back to <code class="text-lteal">{{ $minimumPatch }}</code>
      — the same limit the site applies to its own filters. Older data exists but predates
      changes that make it not worth comparing against, and asking for it returns
      <code>422 timeframe_unavailable</code>.
    </p>

    <h2 class="text-2xl mb-3">Testing before you switch</h2>
    <p class="text-sm mb-3">
      A new account receives <strong>example data</strong> until you activate live data:
      real response shapes with placeholder values, costing no quota. Build against that,
      then activate when you are ready. You can switch back to example data at any time
      from your <a href="/Api/Account" class="link">account page</a>.
    </p>
    <p class="text-sm mb-8">
      Every endpoint can also be run from the <a href="/Api/Docs" class="link">docs</a>
      with your own key, which is the quickest way to see a real response shape.
    </p>

    <h2 id="missing" class="text-2xl mb-3">If something you need is missing</h2>
    <p class="text-sm mb-3">
      v1 is a rewrite, not a port, and a few things the old API returned were dropped
      deliberately. If you depend on something that is gone, or a field you used is not
      in the new response, <strong>get in touch before you activate live data</strong>.
    </p>
    <p class="text-sm mb-3">
      The timing matters. Activating expires your old key immediately, so finding the gap
      afterwards leaves you with no working key on either API until it is resolved. Check
      the <a href="/Api/Docs" class="link">docs</a> against what you actually use first,
      and raise anything missing while your old key still works.
    </p>
    <p class="text-sm mb-3">
      Email <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>
      or open an issue on
      <a class="link" href="https://github.com/Heroes-Profile/heroesprofile/issues/new" target="_blank" rel="noopener">GitHub</a>.
      Either is fine — GitHub is easier to track, email is fine if the details are private.
    </p>
    <p class="text-sm">
      Tell us which endpoint and field, and what you use it for. The second part is the
      useful one: it is often available under a different name or shape, and knowing the
      purpose is what lets us point you at it — or decide it is worth adding back.
    </p>
  </div>
@endsection
