<template>
  <!-- The methodology for the open tab: abstract always shown, the rest on request -->
  <article class="max-w-[1100px] mx-auto mt-16 mb-10 px-4">
    <header class="border-t border-gray-light pt-6 mb-4">
      <p class="text-xs uppercase text-gray-light mb-1">Methodology</p>
      <h2 class="text-2xl font-bold mb-1">{{ paper.title }}</h2>
      <p class="text-sm text-gray-light">Heroes Profile Analytics &middot; {{ paper.version }}</p>
    </header>

    <section class="mb-4">
      <h3 class="text-lg font-bold mb-1">Abstract</h3>
      <p class="text-sm leading-relaxed">{{ paper.abstract }}</p>
    </section>

    <custom-button
      @click="open = !open"
      :text="open ? 'Hide the methodology' : 'Read the full methodology'"
      :alt="open ? 'Hide the methodology' : 'Read the full methodology'"
      size="small"
      :ignoreclick="true"
    ></custom-button>

    <div v-if="open" class="mt-6">
      <section v-for="(section, index) in sections" :key="index" class="mb-6">
        <h3 class="text-lg font-bold mb-2">{{ index + 1 }}. {{ section.heading }}</h3>
        <template v-for="(block, b) in section.body" :key="b">
          <p v-if="typeof block === 'string'" class="text-sm leading-relaxed mb-2">{{ block }}</p>
          <p v-else-if="block.formula" class="text-sm font-mono bg-darken rounded px-3 py-2 mb-2 overflow-x-auto">{{ block.formula }}</p>
          <p v-else-if="block.parts" class="text-sm leading-relaxed mb-2">
            <template v-for="(part, i) in block.parts" :key="i">
              <a v-if="part.href" :href="part.href" target="_blank" class="underline">{{ part.label }}</a>
              <template v-else>{{ part }}</template>
            </template>
          </p>
          <ul v-else-if="block.list" class="list-disc ml-6 text-sm leading-relaxed mb-2">
            <li v-for="(item, i) in block.list" :key="i">{{ item }}</li>
          </ul>
        </template>
      </section>
    </div>
  </article>
</template>

<script>
export default {
  name: 'AnalyticsPaper',
  props: {
    tab: String,
  },
  data() {
    return {
      open: false,
      // How every game reaches Analytics; the start of each methodology's Data section
      sharedData: [
        'Data come from Heroes of the Storm replay files uploaded to Heroes Profile. Only games played on or after 2 October 2026, 16:00 UTC (the start of the 2026 season 3) are eligible. Raw replay files from that point on are kept in long-term storage, so any game can be analysed again later as methods improve.',
        'When a game is analysed, its replay is parsed in full by a modified version of the open-source Heroes.ReplayParser library (version 1.2.20, with the game\'s unit events enabled) into an "extract" (version 3). The extract records every hero death with its time, position, victim and killers; structure and camp captures; team levels; periodic experience breakdowns; talent picks; every ability command and move order with its target; and hero positions. Positions are either real (from the game\'s own position events, deaths and regeneration-globe pickups) or estimated (the target point of a move or ability order). Most positions are estimates, which we flag throughout.',
        'Each extract is loaded into an analytical database alongside the site\'s own records of the game (game type, map, players, heroes, teams, results and party sizes). A game counts towards your numbers once it has been processed; the progress bar at the top of the page shows how many of your season games that is.',
        'Comparisons labelled "global" use a fixed random sample of all eligible games: those whose replay ID ends in a value below the sample threshold (replay ID mod 1000 < 100, currently a 10% sample). These games are processed nightly regardless of who uses Analytics, so the comparison group is not tilted towards players who use the tool. Unless stated otherwise, global numbers are computed under the same game type, map and hero filters as yours.',
      ],
      // Per tab: { title, version, abstract, sections: [{ heading, shared?, body: [string | { formula } | { list } | { parts: [string | { label, href }] }] }] }.
      // Update a tab's entry whenever its queries or rules change.
      papers: {
        deaths: {
          title: 'Where, when and how you die: a spatial and contextual analysis of hero deaths',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We characterise each player\'s deaths by location, game phase and context (how many enemies were involved, the team level difference at the time, and which half of the map the death occurred on) and compare these distributions with a global sample of games under identical filters. We also identify the enemy heroes and players most often involved in a player\'s deaths, and the opponents a player most often kills.',
          sections: [
            { heading: 'Introduction', body: [
              'Deaths are the most direct cost a player imposes on their team: each one removes a hero from the map, concedes experience and frequently enables an enemy objective. Raw death counts say little about why deaths happen, however. This tab decomposes deaths along the dimensions that coaching most often addresses: where on the map, at what point in the game, in what kind of engagement, and against whom.',
            ] },
            { heading: 'Data', shared: true, body: [
              'This tab uses every hero death in your processed games and in the global sample: its game time in seconds, its map position, the victim, and the full list of heroes credited with the kill (all players who contributed, not only the final blow).',
            ] },
            { heading: 'Methods', body: [
              'Death map. Each death is plotted at its recorded position on the in-game minimap. Death positions are real positions taken from the game\'s death events, not estimates.',
              'Game phase. Deaths are assigned to five phases by game time: 0-5, 5-10, 10-15, 15-20 and 20+ minutes. For each phase we report the mean number of deaths per game, counting games with no deaths in that phase as zero. We also report the mean time of a player\'s first death (over games with at least one death only) and the share of games with no deaths ("deathless games").',
              'Engagement type. A death is classified by the number of enemy heroes credited with the kill:',
              { list: [
                'no hero killer: killed by minions, structures, mercenaries or bosses;',
                'ganked: one or two enemy heroes involved;',
                'teamfight: three or more enemy heroes involved.',
              ] },
              'Level context. For each death we look up both teams\' levels at that moment and classify the death as occurring while behind, even or ahead in levels.',
              'Map half. Each team\'s base is estimated as the average position of its heroes\' first recorded positions in the game (their spawn). A death is "on the enemy half" when it is closer to the enemy base than to the player\'s own. Overextension is the share of deaths on the enemy half, broken down by game phase.',
              'Nemesis and victims. We count, across all games under the filters, how often each enemy hero (and each enemy player) appears among the killers of your deaths, and how often each enemy hero (and player) dies with you among their killers.',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Death map: one dot per death of yours with a recorded position, on the selected map. Colour is the engagement type; hovering shows the game time and whether the death was on the enemy half. Any map can be picked; the one with the most deaths is selected first.',
                'Ganked: share of your deaths where one or two enemy players were credited with the kill.',
                'Teamfight: share of your deaths where three or more enemy players were credited with the kill.',
                'No hero killer: deaths with no enemy player credited (minions, structures, mercenaries, bosses). Shown on the map only; these deaths still count in the denominator of every share.',
                'Level Behind / Even / Ahead: share of your deaths where your team\'s level was lower than, equal to, or higher than the enemy team\'s at the moment of death.',
                'Enemy Half: share of your deaths that were closer to the enemy base than to your own.',
                'First Death: mean game time of your first death, over games in which you died.',
                'Deathless Games: share of your games with zero deaths.',
                'Deaths per game by game phase: mean number of your deaths per game in each 5-minute window.',
                'Share of deaths on the enemy half, by game phase: within each window, enemy-half deaths divided by all deaths.',
                'Heroes / players that kill you most: for each enemy hero or player, the number of your deaths they were credited in (assists included). Top 15 heroes, top 10 players.',
                'Heroes / players you kill most: for each enemy hero or player, the number of their deaths you were credited in (assists included). Top 15 heroes, top 10 players.',
                'Global: the same statistic over every player-game in the global sample under your filters. The nemesis and victim lists have no global comparison.',
              ] },
              { formula: 'ganked share = deaths with 1-2 credited killers / all deaths' },
              { formula: 'deaths per game in phase p = Σ deaths in p / games (including deathless games)' },
            ] },
            { heading: 'Statistical analysis', body: [
              'All rates are means over player-games. Shares are computed as a ratio of totals (for example, deaths on the enemy half divided by all deaths), not as an average of per-game ratios, so games with more deaths carry proportionally more weight. Your values are shown next to the same statistic for every player-game in the global sample under the same filters.',
              { formula: 'enemy-half share = Σ deaths on enemy half / Σ deaths' },
              'No significance testing is applied: the tab is descriptive. With few games, differences between you and the global sample can be large by chance alone; as a rough guide, treat differences in shares as meaningful only once a category contains at least 20 of your deaths.',
            ] },
            { heading: 'Limitations', body: [
              'Kill credit includes assists, so a hero appearing among your killers did not necessarily deal the final blow. The map-half split uses straight-line distance to each base, which is a coarse proxy for map control on maps with unusual layouts. Players who hide their profile are counted but not named in the nemesis lists.',
              'A team\'s base position comes from its heroes\' first recorded positions, which can occasionally be an order target rather than a real position. When it is missing, the death is counted as not on the enemy half. Deaths in the first seconds before any team level is recorded are counted as neither behind, even nor ahead.',
            ] },
          ],
        },

        map: {
          title: 'Positional tendencies: a heatmap analysis of hero positioning and command targets',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We estimate where a player spends time on each map using the real hero positions recorded in replays, and separately map where the player directs move and ability orders. We summarise vertical lane preference by splitting each map into thirds.',
          sections: [
            { heading: 'Introduction', body: [
              'Where a player stands, and where they click, reflect lane assignment, rotations and risk-taking. This tab visualises both, per map.',
            ] },
            { heading: 'Data', shared: true, body: [
              'Two kinds of points are used: real hero positions (from the game\'s position events, death locations and regeneration-globe pickups) and estimated positions (the target points of move and ability orders, at most one per second of each kind). Points from early replays that did not record whether a point was real or estimated are excluded from both maps.',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Real position: a point where the hero actually stood, taken from the game\'s periodic position updates, death locations or regeneration-globe pickups. Each second keeps only its first real point.',
                'Estimated position: the target point of a move or ability order. It shows where the player clicked, not where the hero was.',
                'Cell: a 4 × 4 map-unit square; its value is the number of points that fall inside it across the player\'s filtered games.',
                'Team 1 / Team 2: the side the player started on. Bases differ by side and the map is not mirrored, so each side is shown separately.',
                'Map list: every map, in alphabetical order; the map with the most real-position data is selected first.',
              ] },
            ] },
            { heading: 'Methods', body: [
              'Heatmap. Real positions are binned into 4 × 4 map-unit cells per map and team. Cell intensity is proportional to the number of points in the cell. Only real positions are used, because estimated positions are click targets rather than places the hero actually stood.',
              '"Where you click". The same binning is applied to estimated positions only, showing where a player sends their hero and abilities.',
              'Lane thirds. For the selected map, the vertical extent of the map\'s structures and camps (town, building and camp positions averaged from objective events) is divided into three equal bands (top, middle, bottom). Each band\'s share of all real-position points for the selected team is reported; points above the highest or below the lowest structure count toward the top or bottom band. This is a rough proxy for lane preference on three-lane maps.',
            ] },
            { heading: 'Statistical analysis', body: [
              'The heatmap is a count density; no smoothing is applied beyond the 4-unit binning. Shares are ratios of point counts.',
            ] },
            { heading: 'Limitations', body: [
              'Real positions are sparse (roughly one in ten recorded points), so the heatmap undersamples quiet periods such as walking between lanes. Thirds are a geometric approximation and do not follow each map\'s actual lane paths.',
            ] },
          ],
        },

        timelines: {
          title: 'Tempo and conversion: level leads, talent-tier timing, objective control and takedown participation',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We describe how a player\'s games unfold over time: the team level difference minute by minute, win rates from behind, even and ahead at fixed checkpoints, which team reaches talent tiers 10 and 20 first, the timing and conversion of the first objective, camp and fort, end-of-game experience sources, and the player\'s participation in their team\'s takedowns. Each is compared with the global sample under the same filters.',
          sections: [
            { heading: 'Introduction', body: [
              'Heroes of the Storm games are decided by tempo: shared team experience, talent-tier spikes at levels 10 and 20, and converting advantages into objectives and structures. This tab measures each of these from the replay\'s own timelines.',
            ] },
            { heading: 'Data', shared: true, body: [
              'This tab uses team level-up times, periodic experience breakdowns (minion, creep, structure, hero and passive experience), objective and camp captures, structure destructions, and hero deaths with their killers.',
            ] },
            { heading: 'Methods', body: [
              'Level lead. For each minute m of each game, each team\'s level is the highest level reached at or before 60·m seconds. The lead is your team\'s level minus the enemy\'s, averaged over all games still running at that minute, and also separately for won and lost games.',
              'Comebacks. At 10, 15 and 20 minutes, each game is classified as behind, even or ahead in levels, and the win rate is computed for each state.',
              'Talent tiers. For each game we record which team reached level 10 (and 20) first, strictly before the enemy, and the win rate when your team did.',
              'Objective timing. The game times of your team\'s first map objective, first mercenary camp and first enemy fort destroyed are averaged, together with the win rate in games where your team got each one first.',
              'Experience sources. The final experience breakdown of your team is averaged by source, separately for won and lost games.',
              'Takedowns. For each enemy death we record whether you were credited as a killer. Participation is the share of enemy deaths you took part in, pooled over all games; takedowns and solo kills per game are per-game means. Takedowns are also split into five-minute phases (0-5, 5-10, 10-15, 15-20 and 20+ minutes).',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Level lead: your team\'s level minus the enemy team\'s level at a given minute. Positive means your team is ahead.',
                'Behind / even / ahead: your team\'s level at the checkpoint is lower than, equal to, or higher than the enemy\'s. Games shorter than the checkpoint are excluded.',
                'First to 10 / 20: your team reached level 10 (or 20) strictly before the enemy, or the enemy never reached it. Reaching it at the same second does not count.',
                'First objective, camp, fort: the game time of your team\'s first map-objective event, first mercenary camp capture, and first destroyed enemy fort. Averages include only games where the event happened.',
                'First objective / camp / fort win %: win rate in games where your team got that event before the enemy did, or the enemy never got it.',
                'Experience sources: minions, mercenary camps, structures, heroes, and passive (the experience every team gains automatically over time), taken from the last experience report in each game.',
                'Takedown: an enemy hero death where you are among the credited killers (kill or assist). A solo kill is one where you are the only credited player.',
                'Global: a sample of all players\' games under the same game type, map and hero filters.',
              ] },
            ] },
            { heading: 'Statistical analysis', body: [
              { formula: 'participation = Σ enemy deaths with you among the killers / Σ enemy deaths' },
              'Means at each minute use only games still running at that minute, so late minutes rest on fewer, longer games and are noisier. Win rates conditional on a state (for example "behind at 15 minutes") are reported with their game counts so small groups can be discounted.',
            ] },
            { heading: 'Limitations', body: [
              'Level is a team-wide quantity; differences reflect the whole team, not one player. "First objective" depends on the map\'s mechanic and is only comparable across games on the same map.',
            ] },
          ],
        },

        activity: {
          title: 'Mechanical activity: command rates, talent timing and ability usage reconstructed from replay commands',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We measure a player\'s command rate over the course of a game, the timing of their talent picks, and per-ability usage. Ability identities are recovered by rebuilding the game\'s ability catalogue from its data files, and checked against how each hero actually uses its abilities in replays. Presses are distinguished from casts with a rule combining a repeat-press window and each ability\'s base cooldown.',
          sections: [
            { heading: 'Introduction', body: [
              'Replays store every ability command a player issues, but only as numeric identifiers ("links") into the game\'s internal ability catalogue, which is not shipped with the replay. Turning these into named abilities makes it possible to study how and when players use their kit.',
            ] },
            { heading: 'Data', shared: true, body: [
              'This tab uses every ability command (link, command index, game time and target), talent pick times, and a mapping table from ability link to ability name, slot and icon for each game build.',
            ] },
            { heading: 'Methods', body: [
              'Command rate. Commands per minute counts every ability, attack and stop command in each game minute (plain move orders are excluded), averaged over games.',
              'Talent timing. The mean game time at which each talent tier (levels 1, 4, 7, 10, 13, 16, 20) is picked.',
              { parts: ['Ability mapping. The game numbers abilities in the order its data files first mention them. We rebuild that order for each game build from the game\'s data files, following the construction rules worked out by the ', { label: 'hotsixors project by K-JBoon', href: 'https://github.com/K-JBoon/hotsixors' }, ' (Apache License 2.0), which we ported into our own tooling: mods load in a fixed order, a slot is claimed at an ability\'s first mention in a link field, shared cooldown and charge pools are not links, and a few late-resolved fields reserve slots at the end of their file. For build 98348 our rebuild matches hotsixors\' published table exactly (1,316 links). We confirmed it against replays: every hero\'s basic abilities appear in nearly all of its games from the first minute, heroics only after level 10, and generic commands (stop, attack, mount, hearthstone) match a scripted test game.'] },
              'Presses and casts. Every key press is a press. A press counts as a cast unless it repeats the previous press of the same ability within 2 seconds, or the ability has a single charge and a known base cooldown and less than 60% of that cooldown has passed since the last counted cast (the margin allows for cooldown reduction). Where an ability sends two command indexes per press, the busiest index represents it.',
              { formula: 'cast if (t − last press > 2 s) and not (single charge ∧ known cooldown c ∧ t − last cast < 0.6·c)' },
              'Usage over the game. For a selected ability, presses and casts per game minute are averaged over games still running at that minute, alongside your deaths and takedowns per minute.',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Ability commands per minute: ability, attack and stop orders you issued in each game minute (move orders are excluded), averaged over the games still running at that minute. Each game\'s last, partial minute counts as a full minute, so the final points run slightly low.',
                'Talent pick time: the mean game time at which you chose the talent for each tier. This is the moment of the click, which can be later than when the team reached the level.',
                'Presses per game: every press or order of the ability, summed and divided by all your games on that hero, including games where it was never pressed.',
                'Casts per game: presses grouped into estimated uses by the cast rule above, divided the same way. Games recorded before casts were stored are filled in at the rate of the games that have them.',
                'Global presses, global casts, global games used in: the same figures for the same hero from the global sample of other players\' games under the same filters.',
                'Games used in: the share of your games on that hero with at least one press of the ability.',
                'First use: the mean game time of the first press, over the games where the ability was pressed.',
                'Per game, in that minute (ability graph): for each game minute, the total presses or casts divided by your games on that hero still running at that minute; games with no press count as zero. Your lines stop once fewer than 5 of your games were still running. Deaths and takedowns per game use the same denominator and are drawn on the right axis.',
              ] },
              'Why presses can far exceed casts: Attack is a clear example. Players give many attack orders per game, but casts merge presses within 2 seconds of the previous press, so casts roughly count separate bursts of attacking. The auto-attacks themselves are not stored in replays; only the orders are.',
            ] },
            { heading: 'Statistical analysis', body: [
              'All usage figures are per-game means; "games used in" is the share of games with at least one press. Comparisons with the global sample are made per hero, so ability usage is always compared like for like.',
            ] },
            { heading: 'Limitations', body: [
              'Command times are recorded to the second. Base cooldowns ignore talents and cooldown resets, so casts can be undercounted for abilities that reset early. A new game patch requires the mapping to be rebuilt from that build\'s data files; games on a build with no mapping yet use the newest earlier build\'s.',
              'Late game minutes are averaged over only the few games that last that long, so per-minute figures there are noisy.',
            ] },
          ],
        },

        sessions: {
          title: 'Play sessions, timing and disconnects: behavioural patterns across consecutive games',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We group a player\'s games into play sessions and examine win rate by position within a session and after wins and losses (a test for "tilt"), by time of day and day of week, by game length and by map, and in games affected by disconnects on either team.',
          sections: [
            { heading: 'Introduction', body: [
              'Performance can depend on when and how long a player plays as much as on in-game decisions. This tab looks at those patterns across a player\'s game history.',
            ] },
            { heading: 'Data', shared: true, body: [
              'This tab uses each game\'s end time, length, map and result, and the leave and rejoin events of every player.',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Session: a run of consecutive games in which no more than 30 minutes pass between the end of one game and the start of the next (a replay records when the game ended; the start is that minus the game length). Sessions are built from all your processed games; the filters then choose which games are counted, each at its real place in its session.',
                'Sessions and games per session: sessions containing at least one game that matches the filters, and their average length in games (all games, matching or not).',
                'Win rate after a win (or loss): win rate in games whose previous game in the same session was a win (or loss). The first game of a session has no previous game and is excluded.',
                'Disconnect: a leave event by any player more than 30 seconds before the game ended, whether or not the player rejoined.',
                'You disconnected: a game in which you disconnected. Teammate DC game: a game in which another player on your team disconnected. Enemy DC game: the same for the enemy team. A game can count in more than one.',
                'No-DC game: a game with no disconnect on either team.',
              ] },
            ] },
            { heading: 'Methods', body: [
              'Sessions. All your processed games are ordered by start time, whatever the filters. A new session starts when more than 30 minutes pass between the end of one game and the start of the next. A replay records when the game ended, so each start is that time minus the game length. Games matching the filters are then counted at their position in their session.',
              'Win rate by session position is computed for the 1st to 5th game of a session and for all later games combined. Win rate after a win (or loss) uses every game that follows a win (or loss) within the same session.',
              'Time of day and day of week use your browser\'s local time zone, in four-hour blocks. Game length is grouped into under 15, 15-20, 20-25 and 25+ minutes.',
              'Disconnects. A leave event counts as a disconnect unless it occurs in the last 30 seconds of the game (the normal end-of-game exit); a later rejoin does not remove it. Games are classed by whether you, another teammate, or an enemy disconnected (a game can be in more than one class), and the win rate is reported for each class and for games with no disconnect at all.',
            ] },
            { heading: 'Statistical analysis', body: [
              'All figures are simple win rates with their game counts. Session effects are confounded with time of day and with who you queue with, so they describe your history rather than establish cause.',
            ] },
            { heading: 'Limitations', body: [
              'Sessions are inferred from processed games only; unprocessed or non-season games in between are invisible and can merge or split sessions.',
            ] },
          ],
        },

        coaching: {
          title: 'Behavioural coaching signals: definitions, validation against match outcomes, and confidence rating',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'We define a set of behavioural signals derived from fights, deaths, objectives, travel and ability use, compute each player\'s rate of each behaviour, and compare it with the global sample under the same filters. Each signal is validated nightly on the global sample by three tests (an early-game effect on win rate controlling for the early death gap, the consistency of the behaviour across a player\'s games, and its association with win rate beyond deaths per game) which combine into a High, Medium or Low rating. A separate rating reflects how many of the player\'s own games support their value.',
          sections: [
            { heading: 'Introduction', body: [
              'Coaching advice is most useful when it targets behaviours that are both habitual and consequential. Rather than assert which behaviours matter, we measure them and test each against match outcomes, and show how strong the evidence is alongside every number.',
            ] },
            { heading: 'Reading the tab', body: [
              'Each signal shows your value, the global median, your standing and two ratings.',
              { list: [
                'You: your rate across all your processed games under the current filters. Count signals are totals divided by games; shares (fight presence, objective presence, unmounted travel) are totals divided by totals; time to first heroic and camp duty are averages of per-game values.',
                'Global median: the middle value of the comparison group described under Statistical analysis.',
                'Standing: the share of that group doing better or worse than you, taking into account whether lower or higher is better for the signal. Players with exactly your value count as neither; when they are 10% or more of the group, the tied share is shown too.',
                'Signal rating (High, Medium, Low): how strong the global evidence is that the behaviour goes with winning or losing. It is the same for every player and is recomputed nightly.',
                'Your sample (High, Medium, Low): how many of your own games stand behind your value. It is specific to you and to the filters.',
                'Improving, Worse, About the same: whether your last 3 or 5 games differ from your earlier games by more than your usual game-to-game swing.',
                'Your last game: your most recent processed game under the filters, each signal against your usual (the average of your earlier games, up to 200) and the global median. Better or Worse means further from your usual than one game normally varies (the standard deviation of your earlier games, with a floor of 1% of the usual); otherwise Typical. Every moment flagged in that game is listed with a link into the replay viewer. The game shown is identified by its end time and replay number, because coaching is rebuilt overnight and a game just played appears the day after it is processed.',
              ] },
              'The summary at the top lists up to two signals to work on: those rated Medium or High where more than half of players do better than you, most-behind first (fights a talent tier down is excluded because it mostly reflects the team being behind on levels). It also names your best signal where you are ahead of most players, and notes when fewer than 10 games are behind the read.',
            ] },
            { heading: 'Data', shared: true, body: [
              'Signals are computed nightly for every processed game. They use hero deaths and killers, team levels, objective, camp and structure events, hero positions, move orders, ability commands, the ability mapping (for heroic and mount abilities), and each map\'s bush cells, read from the vision-blocker layer of the map files.',
            ] },
            { heading: 'Methods', body: [
              'Fights. Hero deaths are chained into fights: a death more than 15 seconds after the previous one starts a new fight. Fights with two or more deaths are analysed; the team with fewer deaths wins the fight. A player was present at a fight if they died in it, were credited with a kill in it, or had any recorded position within 25 map units of the fight\'s centre (the mean death position) between 10 seconds before the first death and the last death.',
              'Signal definitions:',
              { list: [
                'Overstaying lost fights: deaths that occur when the player\'s team already has at least two more deaths than the enemy in that fight.',
                'Missing fights your team lost: lost fights the player was not present for; "costly" when, within 60 seconds of the fight ending, the team conceded a map objective or camp, or lost a structure.',
                'Objective presence: the share of the team\'s camp captures and enemy structure destructions at which the player had a position within 30 units from 15 seconds before to 5 seconds after, or was credited with the structure kill.',
                'Fights a talent tier down: fights the player took part in while the enemy had reached level 10 (or 20) and the player\'s team had not.',
                'Caught first: being the first death of a fight. Picked off alone: a death with no other death within 15 seconds.',
                'Fight presence: the share of fights the player was present for.',
                'Walking without mounting: the share of travel time spent unmounted, where travel is a second in which the player\'s move-order targets drift at least 15 units over the next 5 seconds outside fighting (within 5 seconds of a basic or heroic ability). Mounted state runs from a mount press until a dismount, attack, cast or death. Games in which no player was ever mounted (for example ARAM) are excluded.',
                'Camp duty: on heroes in the top third by camp presence across the global sample (heroes with at least 200 camp captures), the player\'s camp presence minus that hero\'s average.',
                'Dying to the same hero again: the third and later deaths in a game to the same enemy hero, counting only deaths with one or two killers.',
                'Not adapting to a losing matchup: dying twice to the same enemy hero before 8:00 (one or two killers), then a third time to that hero within 5 minutes of the second death.',
                'Caught at a bush: a first or only death of a fight within 3 units of a bush cell.',
                'Time to first heroic: seconds from the level-10 talent pick to the first cast of a heroic ability.',
              ] },
            ] },
            { heading: 'Statistical analysis', body: [
              'All validation uses the global sample only. Three tests are computed for each signal every night.',
              'Early-game effect. For count signals with a natural early-game version, player-games are split by whether the behaviour occurred in the first 10 minutes. Within strata of the team\'s early death difference (own minus enemy deaths before 10:00, clamped to ±4), the win-rate difference between the two groups is computed and combined as a weighted average, weighting each stratum by the number of player-games showing the behaviour (strata with fewer than 50 player-games in either group are dropped). Stratifying on the early death difference separates the behaviour from how the game was already going.',
              { formula: 'early effect = Σ_s n_yes,s · (WR_yes,s − WR_no,s) / Σ_s n_yes,s' },
              'Habit consistency. For players with at least six games, each player\'s games are split by order into odd and even halves; the correlation between the two halves\' averages across players measures how much the signal is a stable habit rather than noise.',
              'Association beyond deaths. For players with at least ten games, we compute the correlation between the player\'s average signal value and their win rate, controlling for deaths per game (the most consistent predictor of win rate) by partial correlation:',
              { formula: 'r_xw·d = (r_xw − r_xd · r_wd) / √((1 − r_xd²)(1 − r_wd²))' },
              'Signal confidence is High when the association beyond deaths is at least 0.15 in magnitude, the early-game effect is at least 3 points, and consistency is at least 0.2. It is Medium when either of the first two holds, and Low otherwise. Signals without an early-game version (missing costly lost fights, fight presence, fights a talent tier down, time to first heroic, dying to the same hero again, walking without mounting and camp duty) have no early-game effect, so they can be rated Medium at most.',
              'Your-sample confidence is High with 30 or more of your games behind the value, Medium with 10-29, and Low below 10. Most signals count all your games; walking without mounting counts only games with mounts and at least 30 seconds of travel, caught at a bush only games on maps with bushes, camp duty only games on strong camp heroes with at least one team camp capture, and time to first heroic only games where a heroic was picked.',
              'Progress over recent games ("Are you improving?") compares the mean of your last 3 or 5 games with the mean of your earlier games under the same filters (up to your last 200 games), for each signal rated Medium or High. Because a few games are a small sample, a change is only reported as improving or worse when it exceeds one standard error of a few-game mean, estimated from the spread of your earlier games, and at least 1% of the earlier mean; otherwise it is reported as about the same. At least five earlier games are required. The trend chart shows up to your last 20 games with a rolling average of up to 5 games, starting from the third game.',
              { formula: 'reported change if |mean(last n) − mean(earlier)| > max(sd(earlier) / √n, 1% · |mean(earlier)|)' },
              'Your standing is your position in the global distribution under the same filters. We use players with at least 5 games under those filters when there are at least 20 such players, otherwise individual global games (when there are at least 50), and otherwise the unfiltered distribution of players with at least 10 games. Standing is reported as the share of players doing better or worse than you.',
            ] },
            { heading: 'Limitations', body: [
              'These are observational associations; a strong rating means a behaviour reliably accompanies winning or losing, not that changing it alone will change results. Most positions are estimated from orders, which loosens presence and travel measures. We know where bushes are but not what each player could see, so bush deaths are not proven face-checks. The replay contains no damage events, so behaviours such as taking avoidable damage cannot be measured. Ratings change as the sample grows; signals marked Low may move up or down.',
            ] },
          ],
        },

        stacks: {
          title: 'Premade parties: win rates with and against stacks, and the team behaviours that beat them',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'Using party information recorded in replays, we measure a player\'s win rate by their own party size and by the size of the largest enemy party, compare both with the global sample, and identify team behaviours that distinguish teams that beat a larger enemy stack from teams that lose to one.',
          sections: [
            { heading: 'Introduction', body: [
              'Players frequently report that games against premade groups ("stacks") feel unwinnable. This tab measures how large that effect is for the player and for everyone, and looks for behaviours that teams who overcome it share.',
            ] },
            { heading: 'Data', shared: true, body: [
              'Each player\'s party size comes from the replay\'s lobby data (players in the same party share a party value), as recorded by Heroes Profile: solo, or a party of 2 to 5. Games in which any player\'s party size is not yet known are left out.',
            ] },
            { heading: 'Methods', body: [
              'A team\'s stack is the size of its largest party (1 when every player queued alone). For each of your games we record your own party size, the enemy team\'s largest party, and the stack advantage: the enemy\'s largest party minus your team\'s, clamped to ±3.',
              'Facing a stack means the enemy\'s largest party was 3 or more and larger than your team\'s.',
              'How teams beat stacks. Among global-sample teams that faced a stack, we average each team\'s coaching behaviours per player (solo picks, overstays, being caught first, missed lost fights, fight and objective presence, camps, fights a tier down, bush deaths, unmounted travel and deaths) and compare teams that won with teams that lost.',
              { formula: 'relative difference = (losers − winners) / losers   (lower is better);   (winners − losers) / losers   (higher is better)' },
              'You against stacks compares your own averages of the same behaviours in games facing a stack with your other games.',
            ] },
            { heading: 'Definitions', body: [
              { list: [
                'Stack: the size of a team\'s largest party. A team with a duo and three solo players is a 2-stack; "No party (all solo)" means every player queued alone.',
                'Your party size: the party you queued in yourself, which can be smaller than your team\'s stack. The global row compares players who queued in the same size of party.',
                'Difference (win-rate tables): your win rate minus the global win rate, in percentage points.',
                'Stack advantage: the enemy\'s largest party minus your team\'s, clamped to ±3. Positive means the enemy had the bigger party; Even means both were the same size.',
                'Teams that won / Teams that lost: for each team, the per-player average of each behaviour in that game, then averaged over all teams in the group. Values marked % are shares; the rest are counts per player per game. These use every hero even when a hero filter is set, to keep the sample large; game type and map filters apply.',
                'Difference (suggestions): the winners\' advantage as a share of the losers\' value, signed so that positive always means the winners did better.',
                'You vs stacks / You otherwise: your own per-game averages of the same behaviours in games facing a stack, and in every other game (including games where your team had the equal or bigger stack).',
              ] },
            ] },
            { heading: 'Statistical analysis', body: [
              'Win rates are simple proportions with their game counts. A suggestion is rated High when the relative difference is at least 20% and the smaller of the winning and losing groups contains at least 300 teams, Medium at 10% and 100 teams, and Low otherwise. Only behaviours where the winners did better are listed, largest difference first.',
            ] },
            { heading: 'Limitations', body: [
              'The comparison of winners and losers is not causal: teams that are winning for other reasons also tend to group and die less. Party data identify who queued together, not whether they used voice communication or played regularly as a group. Large stacks are uncommon, so the 4- and 5-stack rows rest on fewer games.',
            ] },
          ],
        },

        games: {
          title: 'Processed games: inclusion criteria',
          version: 'Extract v3 · Coaching rules v1 · October 2026',
          abstract: 'This list contains every one of your season games that has been processed for Analytics and matches the current filters. Every other tab is computed from exactly this set of games.',
          sections: [
            { heading: 'Data', shared: true, body: [
              'A game appears here once its replay has been processed at the current extract version. Its MMR values, talents and other details come from the site\'s standard match records, the same as Match History.',
            ] },
          ],
        },
      },
    };
  },
  computed: {
    paper() {
      return this.papers[this.tab];
    },
    // Every paper's Data section starts with how the games are collected and sampled
    sections() {
      return this.paper.sections.map(section => (section.shared ? { heading: section.heading, body: [...this.sharedData, ...section.body] } : section));
    },
  },
};
</script>
