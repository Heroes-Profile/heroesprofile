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
      // Per tab: { title, version, abstract, sections: [{ heading, shared?, body: [string | { formula } | { list }] }] }.
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
              'Game phase. Deaths are assigned to five phases by game time: 0-5, 5-10, 10-15, 15-20 and 20+ minutes. For each phase we report the mean number of deaths per game. We also report the mean time of a player\'s first death and the share of games with no deaths ("deathless games").',
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
            { heading: 'Statistical analysis', body: [
              'All rates are means over player-games. Shares are computed as a ratio of totals (for example, deaths on the enemy half divided by all deaths), not as an average of per-game ratios, so games with more deaths carry proportionally more weight. Your values are shown next to the same statistic for every player-game in the global sample under the same filters.',
              { formula: 'enemy-half share = Σ deaths on enemy half / Σ deaths' },
              'No significance testing is applied: the tab is descriptive. With few games, differences between you and the global sample can be large by chance alone; as a rough guide, treat differences in shares as meaningful only once a category contains at least 20 of your deaths.',
            ] },
            { heading: 'Limitations', body: [
              'Kill credit includes assists, so a hero appearing among your killers did not necessarily deal the final blow. The map-half split uses straight-line distance to each base, which is a coarse proxy for map control on maps with unusual layouts. Players who hide their profile are counted but not named in the nemesis lists.',
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
              'Two kinds of points are used: real hero positions (from the game\'s position events, death locations and regeneration-globe pickups) and estimated positions (the target points of move and ability orders, at most one per second of each kind).',
            ] },
            { heading: 'Methods', body: [
              'Heatmap. Real positions are binned into 4 × 4 map-unit cells per map and team. Cell intensity is proportional to the number of points in the cell. Only real positions are used, because estimated positions are click targets rather than places the hero actually stood.',
              '"Where you click". The same binning is applied to estimated positions only, showing where a player sends their hero and abilities.',
              'Lane thirds. For the selected map, the vertical extent of the observed cells is divided into three equal bands (top, middle, bottom), and each band\'s share of all real-position points is reported. This is a rough proxy for lane preference on three-lane maps.',
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
              'Talent tiers. For each game we record which team reached level 10 (and 20) first, the win rate when your team did, and how many seconds ahead of (or behind) the enemy it got there.',
              'Objective timing. The game times of your team\'s first map objective, first mercenary camp and first enemy fort destroyed are averaged, together with the win rate in games where your team got each one first.',
              'Experience sources. The final experience breakdown of your team is averaged by source, separately for won and lost games.',
              'Takedowns. For each enemy death we record whether you were credited as a killer. Participation is the share of enemy deaths you took part in; solo kills are enemy deaths where you were the only credited killer. Takedowns are also split by game phase.',
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
          abstract: 'We measure a player\'s command rate over the course of a game, the timing of their talent picks, and per-ability usage. Ability identities are recovered by reconstructing the game\'s ability catalogue and aligning it with the numeric ability links stored in replays, validated against talent anchors (94% exact recovery of held-out anchors). Presses are distinguished from casts with a rule combining a repeat-press window and each ability\'s base cooldown.',
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
              'Ability mapping. The game numbers abilities in the order its data files first mention them. We rebuilt that order from the game\'s data files (core, hero data and each hero\'s files, in load order, counting every reference in an ability field, including references to abilities no longer defined), then aligned it to the links observed in replays with dynamic programming. The alignment was anchored on links that appear only after a specific talent is picked and never in games without it, and pinned for generic commands (stop, attack, mount, hearthstone) from a scripted test game. Hiding a fifth of the anchors and re-aligning recovered 94% of them exactly; each mapped link carries a confidence label (pinned, anchor, exact, inferred).',
              'Presses and casts. Every key press is a press. A press counts as a cast unless it repeats the previous press of the same ability within 2 seconds, or the ability has a single charge and a known base cooldown and less than 60% of that cooldown has passed since the last counted cast (the margin allows for cooldown reduction). Where an ability sends two command indexes per press, the busiest index represents it.',
              { formula: 'cast if (t − last press > 2 s) and not (single charge ∧ known cooldown c ∧ t − last cast < 0.6·c)' },
              'Usage over the game. For a selected ability, presses and casts per game minute are averaged over games still running at that minute, alongside your deaths and takedowns per minute.',
            ] },
            { heading: 'Statistical analysis', body: [
              'All usage figures are per-game means; "games used in" is the share of games with at least one press. Comparisons with the global sample are made per hero, so ability usage is always compared like for like.',
            ] },
            { heading: 'Limitations', body: [
              'Command times are recorded to the second. Base cooldowns ignore talents and cooldown resets, so casts can be undercounted for abilities that reset early. About 6% of mapped links may be off by one position; low-confidence links are labelled. A new game patch requires the mapping to be rebuilt.',
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
              'This tab uses each game\'s start time, length, map and result, and the leave and rejoin events of every player.',
            ] },
            { heading: 'Methods', body: [
              'Sessions. Games are ordered by start time. A new session starts when more than 30 minutes pass between the end of one game (start time plus length) and the start of the next.',
              'Win rate by session position is computed for the 1st to 5th game of a session and for all later games combined. Win rate after a win (or loss) uses every game that follows a win (or loss) within the same session.',
              'Time of day and day of week use your browser\'s local time zone, in four-hour blocks. Game length is grouped into under 15, 15-20, 20-25 and 25+ minutes.',
              'Disconnects. A leave event counts as a disconnect unless it occurs in the last 30 seconds of the game (the normal end-of-game exit). Games are split into those with a disconnect on your team, on the enemy team, or neither, and the win rate is reported for each.',
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
              'Signal confidence is High when the association beyond deaths is at least 0.15 in magnitude, the early-game effect is at least 3 points, and consistency is at least 0.2. It is Medium when either of the first two holds, and Low otherwise.',
              'Your-sample confidence is High with 30 or more of your games behind the value, Medium with 10-29, and Low below 10.',
              'Progress over recent games ("Are you improving?") compares the mean of your last 3 or 5 games with the mean of your earlier games under the same filters, for each signal rated Medium or High. Because a few games are a small sample, a change is only reported as improving or worse when it exceeds one standard error of a few-game mean, estimated from the spread of your earlier games; otherwise it is reported as about the same. At least five earlier games are required.',
              { formula: 'reported change if |mean(last n) − mean(earlier)| > sd(earlier) / √n' },
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
              { formula: 'relative difference = (losers − winners) / losers   (for behaviours where lower is better)' },
              'You against stacks compares your own averages of the same behaviours in games facing a stack with your other games.',
            ] },
            { heading: 'Statistical analysis', body: [
              'Win rates are simple proportions with their game counts. A suggestion is rated High when the relative difference is at least 20% and both groups contain at least 300 teams, Medium at 10% and 100 teams, and Low otherwise. Only behaviours where the winners did better are listed.',
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
