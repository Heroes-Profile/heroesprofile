<template>
  <div class="xalatath-recap text-white">
    <!-- The Void Gazette: a front page -->
    <div class="xalatath-scoreboard px-4 py-8">
      <article class="void-gazette max-w-[1100px] mx-auto px-5 md:px-10 py-6 md:py-8">
        <header class="text-center">
          <div class="flex justify-between text-[11px] md:text-xs uppercase tracking-widest border-b border-[#1a1a1a] pb-1">
            <span>Final Edition</span>
            <span class="max-md:hidden">Heroes Profile</span>
            <span>Price: One Soul</span>
          </div>
          <h1 class="void-gazette-masthead text-5xl md:text-7xl py-3">The Void Gazette</h1>
          <div class="flex justify-between text-[11px] md:text-xs uppercase tracking-widest border-y-[3px] border-double border-[#1a1a1a] py-1">
            <span>{{ formatDate(recap.startsAt) }} to {{ formatDate(recap.endsAt) }}</span>
            <span class="max-md:hidden">"All the news that's fit to consume"</span>
            <span>Stage {{ final.stage }} of 5</span>
          </div>
        </header>

        <!-- Lead story -->
        <div class="grid md:grid-cols-5 gap-6 py-5 border-b border-[#1a1a1a]">
          <figure class="md:col-span-2">
            <img src="/images/event/xalatath/xalatath-gazette.webp" alt="Xal'atath" class="void-gazette-photo w-full h-auto" />
            <figcaption class="text-xs italic mt-1">Xal'atath, moments before consuming our photographer.</figcaption>
          </figure>
          <div class="md:col-span-3">
            <h2 class="void-gazette-headline text-3xl md:text-5xl leading-tight mb-3">{{ leadArticle.headline }}</h2>
            <p class="void-gazette-body text-base md:text-lg first-letter:text-5xl first-letter:font-bold first-letter:float-left first-letter:mr-2 first-letter:leading-none">{{ leadArticle.body }}</p>

            <div class="border-t border-[#1a1a1a] mt-5 pt-4">
              <h3 class="void-gazette-headline text-2xl md:text-3xl leading-tight mb-2">Heroes Profile Back in Mortal Hands</h3>
              <p class="void-gazette-body text-base">{{ reclaimedBody }}</p>
            </div>
          </div>
        </div>

        <!-- Inside this edition: jump links to everything below -->
        <nav class="border-b border-[#1a1a1a] py-4">
          <div class="void-gazette-headline text-center text-lg uppercase tracking-widest">Inside This Edition</div>
          <div class="text-center text-xs italic mb-3">Click a section to jump straight to it.</div>
          <ul class="void-gazette-index text-sm">
            <li v-for="entry in indexEntries" :key="entry.id" class="flex items-baseline gap-2 break-inside-avoid">
              <a :href="`#${entry.id}`" class="hover:underline" @click.prevent="jumpTo(entry.id)">{{ entry.label }}</a>
              <span class="flex-1 border-b border-dotted border-[#1a1a1a] opacity-50"></span>
              <span class="tabular-nums">{{ entry.page }}</span>
            </li>
          </ul>
        </nav>

        <!-- Other stories -->
        <div class="void-gazette-columns pt-5">
          <section v-for="(article, index) in articles" :key="index" class="break-inside-avoid mb-5">
            <h3 class="void-gazette-headline text-xl leading-snug mb-1">{{ article.headline }}</h3>
            <p class="void-gazette-body text-sm">{{ article.body }}</p>
          </section>
        </div>
      </article>
    </div>

    <!-- Event-wide: doesn't change with the mode toggle -->
    <div class="max-w-[1500px] mx-auto px-4 pt-8 flex flex-col gap-10">
      <!-- Stage timeline -->
      <section id="recap-stages" data-index="How the Nexus Fell" v-if="stages.length">
        <h2 class="recap-heading">How the Nexus Fell on Heroes Profile</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
          <div v-for="stage in timelineStages" :key="stage.stage" :class="['rounded-md p-4 flex flex-col items-center text-center', { 'opacity-30': !stage.reached_at }]" :style="stageCardStyle(stage.stage)">
            <img class="w-24 mb-2" :src="`/images/event/xalatath/xalatath-logo-stage-${stage.stage}.svg`" alt="" />
            <div class="font-bold">Stage {{ stage.stage }}</div>
            <div class="text-xs opacity-75">{{ stage.stage === 0 ? 'Event start' : `${format(stage.threshold)} games + bans` }}</div>
            <div class="text-sm mb-2">{{ stage.reached_at ? formatDate(stage.reached_at) : 'Never reached' }}</div>
            <p class="text-sm opacity-90">{{ stageNotes[stage.stage] }}</p>
          </div>
        </div>

        <!-- What each stage changed, one card per stage, all the same size. The Before/After
             toggle above flips every card at once. -->
        <div class="flex flex-wrap items-center gap-3 mt-6 mb-2">
          <h3 class="text-sm uppercase tracking-widest xalatath-title">What Changed</h3>
          <multi-tab-button :options="changeOptions" :model-value="paletteOn ? 'after' : 'before'" @update:model-value="setChangeView"></multi-tab-button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
          <!-- Stage 5 added no new colours (the site stayed in the stage 4 palette); its gradient was only the splash -->
          <div v-for="stage in timelineStages" :key="'change-' + stage.stage" :class="['rounded-md p-3 h-96 flex flex-col overflow-hidden', { 'opacity-30': !stage.reached_at }]" :style="stageCardStyle(stage.stage === 5 ? 4 : stage.stage)">
            <div class="text-xs uppercase opacity-75 text-center mb-2">Stage {{ stage.stage }}</div>
            <!-- Top-aligned so the logos and samples line up across the row -->
            <div class="flex-1 min-h-0 flex flex-col items-center justify-start pt-2 text-center">

              <!-- Every card leads with the logo: the previous stage's frame becoming this one's.
                   Event frames get the nav's nudge (the hex sits low in the image); the original logo doesn't. -->
              <span class="relative inline-block w-20 h-20 shrink-0">
                <img :src="logoBefore(stage.stage)" alt="" :class="['absolute inset-0 w-full h-full object-contain void-palette-mock', { '-translate-y-[8%]': stage.stage > 0 }]" :style="{ opacity: paletteOn ? 0 : 1 }" />
                <img :src="`/images/event/xalatath/xalatath-logo-stage-${stage.stage}.svg`" alt="" class="absolute inset-0 w-full h-full object-contain -translate-y-[8%] void-palette-mock" :style="{ opacity: paletteOn ? 1 : 0 }" />
              </span>

              <!-- 0: the eye hunt; her eye turns up tucked into a page -->
              <div v-if="stage.stage === 0" class="w-full mt-4 text-[10px]">
                <div class="relative h-24 rounded overflow-hidden text-left" style="background-color: #0F121D;">
                  <div class="px-2 py-1" style="background-color: #333333;">Heroes Profile</div>
                  <div class="px-2 pt-2 opacity-50 leading-relaxed">Player stats · Match history<br />Talent builds · Leaderboards</div>
                  <img src="/images/event/xalatath/void-eye.svg" alt="" class="absolute bottom-1 right-2 w-8 h-8 void-palette-mock" :style="{ opacity: paletteOn ? 1 : 0 }" />
                </div>
                <div class="mt-1 opacity-75 void-palette-mock" :style="{ opacity: paletteOn ? 1 : 0 }">The eye hunt begins</div>
              </div>

              <!-- 1: the nav picking up the purple glow -->
              <div v-else-if="stage.stage === 1" class="w-full mt-4 rounded overflow-hidden text-[10px] text-left" style="background-color: #0F121D;">
                <div class="px-2 py-1 void-palette-mock"
                  :style="{ backgroundColor: '#333333', boxShadow: paletteOn ? '0 4px 14px rgba(123, 63, 228, 0.8)' : 'none' }">
                  Heroes Profile · Global Stats · Tools
                </div>
                <div class="h-4"></div>
              </div>

              <!-- 2: her whispers, a new one each time it flips to After -->
              <div v-else-if="stage.stage === 2" class="h-28 w-full mt-4 flex items-center justify-center">
                <transition name="void-whisper" mode="out-in">
                  <p v-if="paletteOn" :key="whisperIndex" class="void-whisper rounded px-3 py-2 text-sm italic">{{ whispers[whisperIndex] }}</p>
                </transition>
              </div>

              <!-- 3: the nav scrambled into glyphs -->
              <div v-else-if="stage.stage === 3" class="h-28 w-full mt-4 flex flex-wrap content-center justify-center gap-x-3 gap-y-1 text-sm">
                <span v-for="word in glyphWords" :key="word" :class="['whitespace-nowrap', { 'void-glyph-text': paletteOn }]">{{ paletteOn ? glyph(word) : word }}</span>
              </div>

              <!-- 4: a tiny slice of the site flipping to the stage 4 palette -->
              <div v-else-if="stage.stage === 4" class="w-full mt-4 text-[10px] text-left">
                <div class="rounded overflow-hidden border border-[#5b21b6] void-palette-mock" :style="{ backgroundColor: palette.page }">
                  <div class="px-2 py-1 flex justify-between" :style="{ backgroundColor: palette.nav }">
                    <span>Heroes Profile</span>
                    <span :style="{ color: palette.accent }">Global Stats</span>
                  </div>
                  <div class="px-2 py-1 flex justify-between" :style="{ backgroundColor: palette.thead }">
                    <span>Hero</span><span>Win rate</span>
                  </div>
                  <div class="px-2 py-1 flex justify-between">
                    <span>Xal'atath</span>
                    <span class="px-2 rounded" :style="{ backgroundColor: palette.button }">Search</span>
                  </div>
                </div>
                <!-- The colours for whichever side of the toggle is showing -->
                <div class="grid grid-cols-[auto_1fr_auto] items-center gap-x-2 gap-y-1 mt-2">
                  <template v-for="swatch in paletteSwatches" :key="swatch.label">
                    <span class="inline-block w-3 h-3 rounded-sm void-palette-mock" :style="{ backgroundColor: paletteOn ? swatch.after : swatch.before }"></span>
                    <span class="opacity-75 truncate">{{ swatch.label }}</span>
                    <span class="opacity-75 tabular-nums">{{ paletteOn ? swatch.after : swatch.before }}</span>
                  </template>
                </div>
              </div>

              <!-- 5: the fallen splash's message -->
              <div v-else-if="stage.stage === 5" class="h-28 w-full mt-4 flex items-center justify-center">
                <div class="font-logo text-lg leading-tight void-palette-mock" :style="{ opacity: paletteOn ? 1 : 0 }">The Nexus Has Fallen</div>
              </div>
            </div>
            <!-- Before/after label pinned to the bottom of every card -->
            <div class="text-xs opacity-75 text-center pt-2">{{ paletteOn ? (stage.stage === 0 ? 'Event logo' : `After stage ${stage.stage}`) : 'Before' }}</div>
          </div>
        </div>
      </section>

      <!-- First sighting -->
      <section id="recap-firsts" data-index="First Sighting" v-if="firstGame">
        <h2 class="recap-heading">First Sighting</h2>
        <div class="xalatath-recap-card p-4 max-w-[600px]">
          <p class="mb-2">This is the first Xal'atath game seen on Heroes Profile.</p>
          <a class="link text-sm" target="_blank" rel="noopener" :href="`/Match/Single/${firstGame.replayID}`">View the first match</a>
        </div>
      </section>

      <!-- Launch comparison -->
      <section id="recap-launch" data-index="Biggest Launch Ever?" v-if="launches.length">
        <h2 class="recap-heading">Biggest Launch Ever?</h2>
        <p class="mb-3 text-sm opacity-75">Games played in each hero's first {{ launchDays }} days, the length of the event, from replays uploaded to Heroes Profile.</p>
        <div v-for="row in launches" :key="row.hero" class="mb-3">
          <div class="flex justify-between text-sm mb-1">
            <span :class="{ 'font-bold xalatath-title': row.hero == heroId }">{{ heroName(row.hero) }} <span class="opacity-60">({{ formatDate(row.release_date) }})</span></span>
            <span class="tabular-nums">{{ format(row.games) }}</span>
          </div>
          <div class="h-3 bg-darken rounded overflow-hidden">
            <div :class="['h-full', row.hero == heroId ? 'xalatath-progress' : 'bg-[#6b7280]']" :style="{ width: percent(row.games, launchMax) }"></div>
          </div>
        </div>
        <p class="mt-2 font-bold">{{ launchVerdict }}</p>
      </section>

      <!-- Eye hunt -->
      <section id="recap-eye" data-index="The Eye Hunt" v-if="recap.eyeHunt && recap.eyeHunt.finders">
        <h2 class="recap-heading">The Eye Hunt</h2>
        <div class="flex flex-wrap gap-6 items-end">
          <div class="xalatath-recap-card p-4 flex items-center gap-4">
            <img src="/images/event/xalatath/void-eye.svg" alt="" class="w-16 h-16" />
            <div>
              <div class="text-3xl font-bold tabular-nums">{{ format(recap.eyeHunt.finders) }}</div>
              <div>found Xal'atath's eye</div>
              <div v-if="eyeBusiestDay" class="text-sm opacity-75">Busiest day: {{ format(eyeBusiestDay.count) }} found on {{ formatDate(eyeBusiestDay.date) }}</div>
            </div>
          </div>
          <div class="flex items-end gap-[2px] h-24">
            <div v-for="day in eyeDays" :key="day.date" class="w-3 bg-[#7b3fe4] rounded-t-sm" :style="{ height: percent(day.count, eyeDayMax) }" :title="`${day.date}: ${day.count}`"></div>
          </div>
        </div>
        <div class="max-w-[900px] mt-4 text-sm leading-relaxed">
          <p class="mb-2">
            While the event ran, Xal'atath hid her eye somewhere on Heroes Profile. It turned up on roughly one page in twelve,
            in a different spot for every visitor, and moved each day. Clicking it and logging in with Battle.net claimed it.
          </p>
          <p>
            <span class="font-bold">The reward:</span> the Mark of the Void, an eye shown beside your battletag across the site that stays for good,
            plus 3 months of ad-free Heroes Profile. Finders can hide the mark in Profile Settings.
          </p>
        </div>
      </section>
    </div>

    <!-- Stats by game mode; the toggle sticks while scrolling through them -->
    <div class="max-w-[1500px] mx-auto px-4 pt-10">
      <div class="xalatath-mode-bar sticky top-0 max-md:top-[75px] z-40 py-3 mb-6 flex flex-wrap items-center gap-3">
        <span class="text-sm opacity-75">Showing stats for</span>
        <multi-tab-button :options="modeOptions" :model-value="mode" @update:model-value="mode = $event"></multi-tab-button>
      </div>
      <div class="flex flex-col gap-10">
      <!-- Final stats -->
      <section id="recap-final-count" data-index="The Final Count">
        <h2 class="recap-heading">The Final Count</h2>
        <p v-if="mode === 'all'" class="mb-4 opacity-80">The Void reached stage {{ final.stage }} of 5 with {{ format(final.corruption) }} games and bans.</p>
        <p v-else class="mb-4 opacity-80">{{ modeLabel }} games only.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
          <div v-for="tile in allTiles" :key="tile.label" class="xalatath-recap-card p-3 text-center" :title="tile.info">
            <div class="text-xs uppercase opacity-75">{{ tile.label }}</div>
            <div class="text-xl font-bold tabular-nums">{{ format(tile.value) }}</div>
          </div>
        </div>
      </section>

      <!-- Her average game -->
      <section id="recap-average-game" data-index="Her Average Game" v-if="averages.length">
        <h2 class="recap-heading">Her Average Game</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3">
          <div v-for="item in averages" :key="item.label" class="xalatath-recap-card p-3 text-center">
            <div class="text-xs uppercase opacity-75">{{ item.label }}</div>
            <div class="text-xl font-bold tabular-nums">{{ item.value }}</div>
          </div>
        </div>
      </section>

      <!-- Void ledger -->
      <section id="recap-ledger" data-index="The Void Ledger">
        <h2 class="recap-heading">The Void Ledger</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
          <div v-for="item in ledger" :key="item.label" class="xalatath-recap-card p-3 text-center">
            <div class="text-xs uppercase opacity-75">{{ item.label }}</div>
            <div class="text-xl font-bold tabular-nums">{{ item.value }}</div>
          </div>
        </div>
      </section>

      <!-- Rise over time -->
      <section id="recap-rise" data-index="Rise of the Void" v-if="daily.length">
        <h2 class="recap-heading">Rise of the Void</h2>
        <p class="mb-2 text-sm opacity-75">Games per day and win rate. Large points mark the days a stage fell; the dashed line marks the balance update that nerfed her.</p>
        <div class="xalatath-recap-card p-4">
          <canvas ref="dailyChart" width="1500" height="550"></canvas>
        </div>
      </section>

      <!-- Did the nerf work? -->
      <section id="recap-nerf" data-index="Did the Nerf Work?" v-if="nerf">
        <h2 class="recap-heading">Did the Nerf Work?</h2>
        <p class="mb-1 text-sm opacity-75">
          Balance update {{ nerf.after.patch }}<span v-if="nerf.after.first_game"> ({{ formatDate(nerf.after.first_game) }})</span> cut Void Volley and Void Eruption damage, shortened Void Step and changed five talents.
          Launch patch compared with the balance update.
        </p>
        <p class="mb-4 text-lg font-bold xalatath-title">{{ nerf.verdict }}</p>
        <scroll-hint-container :fit="true">
        <table class="min-w-0 w-full responsive-table mb-6">
          <thead>
            <tr><th class="py-2 px-3 text-left text-sm">Stat</th><th class="py-2 px-3 text-left text-sm">Launch patch</th><th class="py-2 px-3 text-left text-sm">After the balance update</th><th class="py-2 px-3 text-left text-sm">Change</th></tr>
          </thead>
          <tbody>
            <tr v-for="row in nerf.rows" :key="row.label">
              <td class="py-2 px-3">{{ row.label }}</td>
              <td class="py-2 px-3">{{ row.before }}</td>
              <td class="py-2 px-3">{{ row.after }}</td>
              <td class="py-2 px-3">{{ row.change }}</td>
            </tr>
          </tbody>
        </table>
        </scroll-hint-container>
        <div v-if="nerfedTalents.length">
          <h3 class="font-bold mb-2">The Talents It Changed</h3>
          <scroll-hint-container :fit="true">
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr><th class="py-2 px-3 text-left text-sm">Talent</th><th class="py-2 px-3 text-left text-sm">Picked before</th><th class="py-2 px-3 text-left text-sm">Picked after</th><th class="py-2 px-3 text-left text-sm">Win rate before</th><th class="py-2 px-3 text-left text-sm">Win rate after</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in nerfedTalents" :key="row.talent_id">
                <td class="py-2 px-3">
                  <div class="flex items-center gap-2">
                    <talent-image-wrapper :talent="recap.talents[row.talent_id]" size="small"></talent-image-wrapper>
                    <span>{{ talentName(row.talent_id) }} <span class="opacity-60">(level {{ row.level }})</span></span>
                  </div>
                </td>
                <td class="py-2 px-3">{{ row.pickBefore }}</td>
                <td class="py-2 px-3">{{ row.pickAfter }}</td>
                <td class="py-2 px-3">{{ row.winBefore }}</td>
                <td class="py-2 px-3">{{ row.winAfter }}</td>
              </tr>
            </tbody>
          </table>
          </scroll-hint-container>
        </div>
      </section>

      <!-- Game modes + regions -->
      <section id="recap-modes" data-index="Modes & Regions" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-if="mode === 'all'">
          <h2 class="recap-heading">Corruption by Game Mode</h2>
          <scroll-hint-container :fit="true">
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr><th class="py-2 px-3 text-left text-sm">Game mode</th><th class="py-2 px-3 text-left text-sm">Games</th><th class="py-2 px-3 text-left text-sm">Win rate</th><th class="py-2 px-3 text-left text-sm">Bans</th></tr>
            </thead>
            <tbody>
              <tr v-for="row in gameModes" :key="row.game_type">
                <td class="py-2 px-3">{{ recap.gameTypes[row.game_type] || row.game_type }}</td>
                <td class="py-2 px-3">{{ format(row.games_played) }}</td>
                <td class="py-2 px-3">{{ winRate(row.wins, row.games_played) }}</td>
                <td class="py-2 px-3">{{ Number(row.bans) ? format(row.bans) : '' }}</td>
              </tr>
            </tbody>
          </table>
          </scroll-hint-container>
        </div>
        <div>
          <h2 class="recap-heading">Corruption by Region</h2>
          <div v-for="row in regions" :key="row.region" class="mb-3">
            <div class="flex justify-between text-sm mb-1">
              <span>{{ recap.regions[row.region] || row.region }}</span>
              <span class="tabular-nums">{{ format(row.games + row.bans) }} ({{ percent(row.games + row.bans, regionTotal) }})</span>
            </div>
            <div class="h-3 bg-darken rounded overflow-hidden">
              <div class="h-full xalatath-progress" :style="{ width: percent(row.games + row.bans, regionTotal) }"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- When the Void struck -->
      <section id="recap-when" data-index="When the Void Struck" v-if="heatmap.max > 0">
        <h2 class="recap-heading">When the Void Struck</h2>
        <multi-tab-button v-if="heatmapRegionOptions.length > 1" class="mb-3 !justify-start" :options="heatmapRegionOptions" :model-value="heatmapRegion" @update:model-value="selectedHeatmapRegion = $event"></multi-tab-button>
        <p class="mb-2 text-sm opacity-75">Games by day and hour, in {{ heatmapZoneLabel }}.</p>
        <div class="overflow-x-auto">
          <div class="inline-grid gap-[2px] text-xs" style="grid-template-columns: 3rem repeat(24, minmax(1.25rem, 1fr));">
            <div></div>
            <div v-for="hour in 24" :key="'h' + hour" class="text-center opacity-60">{{ shortHour(hour - 1) }}</div>
            <template v-for="(row, day) in heatmap.rows" :key="'d' + day">
              <div class="opacity-75 pr-1">{{ weekdayNames[day] }}</div>
              <div v-for="(games, hour) in row" :key="day + '-' + hour" class="h-5 rounded-sm bg-[#7b3fe4]" :style="{ opacity: heatOpacity(games / heatmap.max) }" :title="`${weekdayNames[day]} ${hour12(hour)} - ${format(games)} games`"></div>
            </template>
          </div>
        </div>
        <div class="flex items-center gap-2 mt-3 text-xs">
          <span class="opacity-75">Fewer games</span>
          <div v-for="step in [0, 0.25, 0.5, 0.75, 1]" :key="step" class="w-5 h-5 rounded-sm bg-[#7b3fe4]" :style="{ opacity: heatOpacity(step) }"></div>
          <span class="opacity-75">More games</span>
        </div>
        <p v-if="heatmap.peak" class="mt-2 text-sm">The Void was hungriest on {{ heatmap.peak }}.</p>
      </section>

      <!-- Single-game records, grouped; each opens to its top 10 -->
      <section id="recap-records" data-index="Single-Game Records" v-if="recordGroups.length">
        <h2 class="recap-heading">Single-Game Records</h2>
        <p class="mb-4 text-sm opacity-75">Her best single games for every stat. Click a record to see the top 10.</p>
        <div v-for="group in recordGroups" :key="group.title" class="mb-5">
          <h3 class="text-sm uppercase tracking-widest xalatath-title mb-2">{{ group.title }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-2 items-start">
            <details v-for="item in group.records" :key="item.record" class="xalatath-recap-card group">
              <summary class="px-3 py-2 flex justify-between items-center gap-3 cursor-pointer list-none">
                <div class="min-w-0">
                  <div class="text-sm font-bold truncate">{{ item.label }}</div>
                  <div class="text-xs opacity-70">
                    <i class="fas fa-chevron-down mr-1 transition-transform group-open:rotate-180"></i>Top {{ item.top.length }}
                  </div>
                </div>
                <div class="text-lg font-bold tabular-nums xalatath-title whitespace-nowrap">{{ item.display }}</div>
              </summary>
              <ol class="px-3 pb-2 text-xs">
                <li v-for="row in item.top" :key="row.place" class="flex items-center gap-2 py-1 border-t border-[#5b21b6]">
                  <span class="w-5 opacity-60 tabular-nums">{{ row.place }}</span>
                  <span class="flex-1 tabular-nums font-bold">{{ row.display }}</span>
                  <span class="opacity-70">{{ formatDate(row.game_date) }}</span>
                  <a v-if="row.replayID && !NEGATIVE_RECORDS.includes(item.record)" class="link" target="_blank" rel="noopener" :href="`/Match/Single/${row.replayID}`">Match</a>
                </li>
              </ol>
            </details>
          </div>
        </div>
      </section>

      <!-- Path of the Void -->
      <section id="recap-talents" data-index="Path of the Void" v-if="builds.length || talentTiers.length">
        <h2 class="recap-heading">Path of the Void</h2>
        <!-- Same components as the global talent pages -->
        <global-talent-builds-section v-if="buildRows.length" class="mb-6" :talentbuilddata="buildRows">
          <template #heading>Most played builds</template>
        </global-talent-builds-section>
        <global-talent-details-section v-if="talentGridData" class="mx-auto" :talentdetaildata="talentGridData"></global-talent-details-section>
      </section>

      <!-- Matchups -->
      <section id="recap-matchups" data-index="Victims, Enemies & Allies" v-if="victims.length || allies.length">
        <div class="grid md:grid-cols-3 gap-6">
          <div v-for="group in matchupGroups" :key="group.title">
            <h2 class="recap-heading">{{ group.title }}</h2>
            <p class="text-sm opacity-75 mb-2">{{ group.info }}</p>
            <div v-for="row in group.rows" :key="row.hero" class="flex items-center gap-3 mb-2">
              <hero-image-wrapper v-if="recap.heroes[row.hero]" :hero="recap.heroes[row.hero]" size="small"></hero-image-wrapper>
              <span class="flex-1">{{ heroName(row.hero) }}</span>
              <span class="tabular-nums text-right">
                {{ winRate(row.wins, row.games) }}
                <span class="block text-xs opacity-70">{{ edgeLabel(row) }}</span>
              </span>
            </div>
          </div>
        </div>
      </section>

      <!-- Maps -->
      <section id="recap-maps" data-index="Battlegrounds" v-if="maps.length">
        <h2 class="recap-heading">Battlegrounds Conquered</h2>
        <scroll-hint-container :fit="true">
        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr><th class="py-2 px-3 text-left text-sm">Battleground</th><th class="py-2 px-3 text-left text-sm">Games</th><th class="py-2 px-3 text-left text-sm">Win rate</th></tr>
          </thead>
          <tbody>
            <tr v-for="row in maps" :key="row.game_map">
              <td class="py-2 px-3">{{ mapName(row.game_map) }}</td>
              <td class="py-2 px-3">{{ format(row.games) }}</td>
              <td class="py-2 px-3">{{ winRate(row.wins, row.games) }}</td>
            </tr>
          </tbody>
        </table>
        </scroll-hint-container>
      </section>

      <!-- Awards + devotees -->
      <section id="recap-awards" data-index="Awards & Devotees" class="grid md:grid-cols-2 gap-6">
        <div v-if="awards.length">
          <h2 class="recap-heading">Awards Hoard</h2>
          <p class="mb-3 text-sm opacity-75">A player gets one award a game and MVP takes priority, so MVP is out of all her games and every other award is out of the games she wasn't MVP.</p>
          <div class="grid grid-cols-2 gap-3">
            <div v-for="row in awards" :key="row.award" class="xalatath-recap-card p-3 flex items-center gap-3 max-md:flex-col max-md:text-center">
              <img v-if="recap.awards[row.award]" :src="`/images/awards/${recap.awards[row.award].icon}_blue.png`" :alt="awardTitle(row.award)" class="w-10 h-10" />
              <div class="min-w-0 max-w-full">
                <div class="font-bold">{{ awardTitle(row.award) }}</div>
                <div class="text-sm tabular-nums">{{ format(row.count) }}<span v-if="awardShareOf(row)" class="opacity-75"> · {{ percent(row.count, awardShareOf(row)) }} {{ isMvp(row) ? 'of her games' : 'of non-MVP games' }}</span></div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="devotees.total">
          <h2 class="recap-heading">Devotees and Tourists</h2>
          <p class="mb-3">{{ format(devotees.total) }} players answered her call.</p>
          <div v-for="row in devotees.rows" :key="row.bucket" class="mb-3">
            <div class="flex justify-between text-sm mb-1">
              <span>{{ row.label }}</span>
              <span class="tabular-nums">{{ format(row.players) }} ({{ percent(row.players, devotees.total) }})</span>
            </div>
            <div class="h-3 bg-darken rounded overflow-hidden">
              <div class="h-full xalatath-progress" :style="{ width: percent(row.players, devotees.total) }"></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Leaderboard -->
      <section id="recap-leaderboard" data-index="Most Corrupted" v-if="leaderboard.length">
        <h2 class="recap-heading">Most Corrupted</h2>
        <scroll-hint-container :fit="true">
        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr><th class="py-2 px-3 text-left text-sm">Rank</th><th class="py-2 px-3 text-left text-sm">Player</th><th class="py-2 px-3 text-left text-sm">Region</th><th class="py-2 px-3 text-left text-sm">Games</th><th class="py-2 px-3 text-left text-sm">Win rate</th></tr>
          </thead>
          <tbody>
            <tr v-for="row in leaderboard" :key="row.blizz_id + '-' + row.region">
              <td class="py-2 px-3">{{ row.rank }}</td>
              <td class="py-2 px-3">
                <div class="flex items-center">
                  <a class="link mr-2" target="_blank" rel="noopener" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`">{{ row.battletag }}</a>
                  <div v-if="row.hp_owner">
                    <icon-with-hover class="mt-3" size="small" icon="fas fa-crown" title="info" popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                      <slot>
                        <div>
                          <p class="max-sm:text-xs">Site Owner</p>
                        </div>
                      </slot>
                    </icon-with-hover>
                  </div>
                  <div v-else-if="row.patreon">
                    <icon-with-hover class="mt-3" size="small" icon="fas fa-star" title="info" popupsize="small" style="color:rgba(216, 184, 0, 0.719);">
                      <slot>
                        <div>
                          <p class="max-sm:text-xs">Patreon Subscriber</p>
                        </div>
                      </slot>
                    </icon-with-hover>
                  </div>
                  <void-eye-flair class="mt-3 -ml-1 -translate-y-[0.45em]" :blizz-id="row.blizz_id" :region="row.region"></void-eye-flair>
                </div>
              </td>
              <td class="py-2 px-3">{{ recap.regions[row.region] || row.region }}</td>
              <td class="py-2 px-3">{{ format(row.games) }}</td>
              <td class="py-2 px-3">{{ winRate(row.wins, row.games) }}</td>
            </tr>
          </tbody>
        </table>
        </scroll-hint-container>
      </section>

      <!-- Your contribution -->
      <section id="recap-contribution" data-index="Your Contribution">
        <h2 class="recap-heading">Your Contribution</h2>
        <div v-if="contribution">
          <!-- The card as it downloads; downloadCard() draws the same layout -->
          <!-- Never played or faced her: a short banner instead of the full card -->
          <div v-if="cardEmpty" class="void-contribution-card max-w-[700px] rounded-lg p-4 md:p-5 flex items-center gap-4">
            <img class="w-16 md:w-20 shrink-0" :src="`/images/event/xalatath/xalatath-logo-stage-${final.stage}.svg`" alt="" />
            <div class="min-w-0">
              <div class="text-xs uppercase tracking-widest xalatath-title">Xal'atath Void Corruption<span v-if="mode !== 'all'"> · {{ modeLabel }}</span></div>
              <div class="text-2xl md:text-3xl font-bold">{{ contribution.battletag }}</div>
              <div class="text-lg">{{ noGamesLine }}</div>
              <div class="text-xs opacity-60 mt-2">heroesprofile.com/Event/Xalatath</div>
            </div>
          </div>
          <div v-else class="void-contribution-card max-w-[1000px] rounded-lg p-5 md:p-8 grid md:grid-cols-[220px_minmax(0,1fr)] gap-6">
            <div class="flex flex-col items-center text-center">
              <img class="w-40 md:w-52" :src="`/images/event/xalatath/xalatath-logo-stage-${final.stage}.svg`" alt="" />
            </div>
            <div class="min-w-0">
              <div class="text-xs uppercase tracking-widest xalatath-title">Xal'atath Void Corruption<span v-if="mode !== 'all'"> · {{ modeLabel }}</span></div>
              <div class="text-3xl md:text-4xl font-bold mb-1">{{ contribution.battletag }}</div>
              <template v-if="card.games > 0">
                <div class="text-lg mb-4">
                  Fed the Void <span class="font-bold">{{ format(card.games) }}</span> games,
                  won <span class="font-bold">{{ format(card.wins) }}</span> ({{ winRate(card.wins, card.games) }})
                </div>

                <!-- Labels once as column headers, numbers lined up underneath -->
                <div v-if="card.modes && card.modes.length" class="grid grid-cols-[minmax(0,1fr)_auto_auto] gap-x-6 gap-y-1 text-sm mb-4 max-w-[420px]">
                  <span class="text-xs uppercase opacity-75">Mode</span>
                  <span class="text-xs uppercase opacity-75 text-right">Games</span>
                  <span class="text-xs uppercase opacity-75 text-right">Win rate</span>
                  <template v-for="mode in card.modes" :key="mode.name">
                    <span class="truncate">{{ mode.name }}</span>
                    <span class="text-right tabular-nums">{{ format(mode.games) }}</span>
                    <span class="text-right tabular-nums">{{ winRate(mode.wins, mode.games) }}</span>
                  </template>
                </div>

                <div v-if="card.build" class="mb-4">
                  <div class="text-xs uppercase opacity-75 mb-1">Played build</div>
                  <div class="flex flex-wrap items-center gap-2">
                    <talent-image-wrapper v-for="(talent, index) in card.build.talents" :key="index" :talent="talent" size="small"></talent-image-wrapper>
                    <span class="text-sm ml-2 opacity-90">{{ format(card.build.games) }} games, {{ winRate(card.build.wins, card.build.games) }} won</span>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div v-for="group in contributionMates" :key="group.title" class="grid grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-x-4 gap-y-1 text-sm">
                    <span class="text-xs uppercase opacity-75">{{ group.title }}</span>
                    <span class="text-xs uppercase opacity-75 text-right">Games</span>
                    <span class="text-xs uppercase opacity-75 text-right">Win rate</span>
                    <template v-for="mate in group.rows" :key="mate.hero ? mate.hero.id : mate.games">
                      <span class="flex items-center gap-2 min-w-0">
                        <hero-image-wrapper v-if="mate.hero" :hero="mate.hero" size="small"></hero-image-wrapper>
                        <span class="truncate">{{ mate.hero ? mate.hero.name : '' }}</span>
                      </span>
                      <span class="text-right tabular-nums">{{ format(mate.games) }}</span>
                      <span class="text-right tabular-nums">{{ winRate(mate.wins, mate.games) }}</span>
                    </template>
                  </div>
                </div>
              </template>
              <template v-else>
                <div class="text-lg mb-4">{{ noGamesLine }}</div>
                <!-- Estimated from her share of your team's deaths in each game she was against you -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4 max-w-[600px]">
                  <div v-for="tile in facedTiles" :key="tile.label" class="xalatath-recap-card p-3 text-center">
                    <div class="text-xs uppercase opacity-75">{{ tile.label }}</div>
                    <div class="text-xl font-bold tabular-nums">{{ tile.value }}</div>
                  </div>
                </div>
              </template>

              <!-- Estimated from her share of your team's deaths in each game she was against you -->
              <div v-if="card.games > 0 && card.faced && card.faced.games" class="text-sm mt-2">
                <div class="text-xs uppercase opacity-75 mb-1">Facing her</div>
                Faced her in <span class="font-bold">{{ format(card.faced.games) }}</span> games:
                <span class="font-bold">&asymp; {{ format(card.faced.involved) }}</span> deaths involving her,
                <span class="font-bold">&asymp; {{ format(card.faced.killing_blows) }}</span> to her killing blow.
              </div>
              <div class="text-xs opacity-60 mt-4">heroesprofile.com/Event/Xalatath</div>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-3 mt-3">
            <button type="button" class="bg-[#5b21b6] hover:bg-[#7b3fe4] text-white px-6 py-3 rounded" @click="downloadCard">Download your card</button>
            <button v-if="canShareFiles" type="button" class="bg-[#5b21b6] hover:bg-[#7b3fe4] text-white px-6 py-3 rounded" @click="shareCard">
              <i class="fas fa-share-alt mr-1"></i> Share your card
            </button>
          </div>
        </div>
        <div v-else class="xalatath-recap-card p-4">
          <a class="link" href="/Authenticate/Battlenet">Log in with Battle.net</a> to see how much you fed the Void.
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-4 text-sm">
          <span class="opacity-75">Spread the word:</span>
          <a v-for="link in shareLinks" :key="link.name" :href="link.build(recapUrl)" target="_blank" rel="noopener" class="bg-darken hover:bg-lighten rounded px-3 py-2" @click="openShare($event, link)">
            <i :class="[link.icon, 'mr-1']"></i> {{ link.name }}
          </a>
        </div>
      </section>
      </div>
    </div>

    <div class="max-w-[1500px] mx-auto px-4 py-10 flex flex-col gap-10">
      <!-- Whispers -->
      <section id="recap-whispers" data-index="Her Last Whispers">
        <h2 class="recap-heading">Her Last Whispers</h2>
        <div class="columns-1 md:columns-2 xl:columns-3 gap-4">
          <p v-for="(line, index) in whispers" :key="index" class="void-whisper rounded px-4 py-2 mb-3 italic break-inside-avoid">{{ line }}</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
import moment from 'moment-timezone';
import WHISPERS from './voidWhisperLines';
import { toGlyphs } from './voidGlyphs';
import { mainTiles, extraTiles, formatNumber } from './xalatathTiles';

Chart.register(...registerables);

const HERO_ID = 92;
const WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const DAY_NAMES = ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'];
// Single-game records, grouped for display. Keys match the snapshot's record names.
const RECORD_GROUPS = [
  {
    title: 'Combat',
    records: [
      { key: 'takedowns', label: 'Most takedowns' },
      { key: 'kills', label: 'Most kills' },
      { key: 'assists', label: 'Most assists' },
      { key: 'highest_kill_streak', label: 'Longest kill streak' },
      { key: 'multikill', label: 'Most multikills' },
      { key: 'vengeance', label: 'Most vengeances' },
      { key: 'escapes', label: 'Most escapes' },
      { key: 'teamfight_escapes', label: 'Most team fight escapes' },
    ],
  },
  {
    title: 'Damage',
    records: [
      { key: 'hero_damage', label: 'Most hero damage' },
      { key: 'spell_damage', label: 'Most Void damage' },
      { key: 'physical_damage', label: 'Most physical damage' },
      { key: 'teamfight_hero_damage', label: 'Most team fight damage' },
      { key: 'siege_damage', label: 'Most siege damage' },
      { key: 'structure_damage', label: 'Most structure damage' },
      { key: 'minion_damage', label: 'Most minion damage' },
      { key: 'creep_damage', label: 'Most mercenary damage' },
      { key: 'summon_damage', label: 'Most summon damage' },
    ],
  },
  {
    title: 'Support & Survival',
    records: [
      { key: 'healing', label: 'Most healing' },
      { key: 'self_healing', label: 'Most self-healing' },
      { key: 'teamfight_healing', label: 'Most team fight healing' },
      { key: 'clutch_heals', label: 'Most clutch heals' },
      { key: 'protection_allies', label: 'Most shielding' },
      { key: 'damage_taken', label: 'Most damage soaked' },
      { key: 'teamfight_damage_taken', label: 'Most team fight damage soaked' },
      { key: 'regen_globes', label: 'Most regen globes' },
    ],
  },
  {
    title: 'Crowd Control',
    records: [
      { key: 'stunning_enemies', label: 'Longest stunning enemies' },
      { key: 'rooting_enemies', label: 'Longest rooting enemies' },
      { key: 'silencing_enemies', label: 'Longest silencing enemies' },
    ],
  },
  {
    title: 'Map & Macro',
    records: [
      { key: 'experience_contribution', label: 'Most XP contribution' },
      { key: 'meta_experience', label: 'Most objective XP' },
      { key: 'merc_camp_captures', label: 'Most mercenary camps' },
      { key: 'watch_tower_captures', label: 'Most watch towers' },
      { key: 'town_kills', label: 'Most structures destroyed' },
      { key: 'level', label: 'Highest team level' },
      { key: 'time_on_fire', label: 'Longest time on fire' },
    ],
  },
  {
    title: 'Game',
    records: [
      { key: 'fastest_win', label: 'Fastest win' },
      { key: 'longest_game', label: 'Longest game' },
    ],
  },
  {
    title: 'Hall of Shame',
    records: [
      { key: 'deaths', label: 'Most deaths' },
      { key: 'outnumbered_deaths', label: 'Most outnumbered deaths' },
      { key: 'time_spent_dead', label: 'Most time dead' },
    ],
  },
];
const RECORD_LABELS = Object.fromEntries(RECORD_GROUPS.flatMap((group) => group.records.map((record) => [record.key, record.label])));
// No match link for these, so players can't look up who died the most.
const NEGATIVE_RECORDS = ['deaths', 'outnumbered_deaths', 'time_spent_dead'];
// Stored in seconds, shown as m:ss.
const TIME_RECORDS = ['stunning_enemies', 'rooting_enemies', 'silencing_enemies', 'time_on_fire', 'fastest_win', 'longest_game', 'time_spent_dead'];
// What each stage did to the site while the event ran.
const STAGE_NOTES = {
  0: "She arrived. The Heroes Profile logo gave way to hers, the corruption scoreboard appeared across the top of the site, and her eye began hiding on its pages.",
  1: 'The logo cracked, and a purple glow crept under the nav bar and scoreboard.',
  2: 'She started whispering. Taunts surfaced across the site, aimed at your win rate and your uploads.',
  3: 'Her whispers scrambled the navigation into Void glyphs while she spoke.',
  4: "The palette turned. Backgrounds sank to deep violet and the site's teal and blue went purple.",
  5: 'The Nexus fell. Visitors were greeted by "The Nexus Has Fallen" and the logo was fully consumed.',
};
// Talents the Oct 5 balance update (2.57.0.98348) changed, matched by name.
// Balance update 2.57.0.98348 date from the patch notes; used if the snapshot has no nerf section.
const NERF_DATE = '2026-10-05';
const NERFED_TALENTS = ['Anchored Core', 'Dark Barrier', 'Silence of the Lamb', "Dark Heart's Protection", 'Rift Invasion'];
// Each stage card looks like the site did at that stage (same colours as the live event CSS).
const STAGE_GLOW = '0 0 22px rgba(123, 63, 228, 0.45)';
const STAGE_CARD_STYLES = {
  // Normal site colours, plus the purple glow under the nav.
  // The site as it always was, before any corruption.
  0: { backgroundColor: '#0F121D', border: '1px solid #2b3047' },
  1: { backgroundColor: '#0F121D', border: '1px solid #2b3047', boxShadow: STAGE_GLOW },
  2: { backgroundColor: '#0F121D', border: '1px solid #2b3047', boxShadow: STAGE_GLOW },
  3: { backgroundColor: '#0F121D', border: '1px solid #2b3047', boxShadow: STAGE_GLOW },
  // Palette shift: violet background, teal/blue gone purple.
  4: {
    backgroundColor: '#110822',
    backgroundImage: 'radial-gradient(ellipse at top, rgba(91, 33, 182, 0.35), transparent 60%)',
    border: '1px solid #7b3fe4',
    boxShadow: STAGE_GLOW,
  },
  // Fallen: the "Nexus Has Fallen" splash.
  5: {
    backgroundImage: 'radial-gradient(circle at center, rgb(58, 20, 112) 0%, rgb(8, 3, 18) 75%)',
    border: '1px solid #c084fc',
    boxShadow: '0 0 32px rgba(192, 132, 252, 0.6)',
  },
};
// Each region's heatmap is shown in its own time zone.
const REGION_ZONES = { 1: 'America/New_York', 2: 'Europe/Berlin', 3: 'Asia/Seoul', 5: 'Asia/Shanghai' };
const REGION_ZONE_LABELS = { 1: 'US Eastern', 2: 'Central European', 3: 'Korea', 5: 'China' };
// The page-wide mode toggle. Keys match game_type in the snapshot ('all' = no game_type).
const MODE_OPTIONS = [
  { key: 'all', label: 'All' },
  { key: '1', label: 'Quick Match' },
  { key: '5', label: 'Storm League' },
  { key: '6', label: 'ARAM' },
];
// The site's colours before stage 4 and what the stage 4 CSS turned them into.
const PALETTE_SWATCHES = [
  { key: 'nav', label: 'Nav bar', before: '#333333', after: '#1d0f38' },
  { key: 'thead', label: 'Table headers', before: '#008b8b', after: '#5b21b6' },
  { key: 'button', label: 'Buttons', before: '#213d7a', after: '#3a1470' },
  { key: 'accent', label: 'Teal text', before: '#00d5d5', after: '#c084fc' },
  { key: 'page', label: 'Background', before: '#0F121D', after: '#110822' },
];
const DEVOTEE_LABELS = { 1: 'Played once and fled', '2-9': 'Dabbled (2 to 9 games)', '10+': 'Devoted (10+ games)' };

export default {
  name: 'XalatathRecap',
  props: {
    recap: {
      type: Object,
      required: true,
    },
    contribution: Object,
  },
  data() {
    return {
      heroId: HERO_ID,
      weekdayNames: WEEKDAYS,
      NEGATIVE_RECORDS,
      stageNotes: STAGE_NOTES,
      whispers: WHISPERS,
      chart: null,
      whisperIndex: 0,
      glyphWords: ['Global Stats', 'Leaderboards', 'Tools', 'Find a Player'],
      indexEntries: [],
      canShareFiles: false,
      selectedHeatmapRegion: null,
      mode: 'all',
      paletteOn: false,
      paletteSwatches: PALETTE_SWATCHES,
      cardUrl: null,
    };
  },
  computed: {
    final() {
      return this.recap.final || { totals: {}, stage: 0, corruption: 0, byGameType: {} };
    },
    totals() {
      if (this.mode === 'all') {
        return this.final.totals || {};
      }
      return (this.final.byGameType || {})[this.mode] || {};
    },
    sections() {
      return this.recap.sections || {};
    },
    // Snapshot sections narrowed to the selected mode, and to All (for the Gazette and other event-wide bits).
    modeSections() {
      return this.sectionsFor(this.mode);
    },
    allSections() {
      return this.sectionsFor('all');
    },
    changeOptions() {
      return [{ key: 'before', label: 'Before' }, { key: 'after', label: 'After' }];
    },
    palette() {
      const side = this.paletteOn ? 'after' : 'before';
      return Object.fromEntries(PALETTE_SWATCHES.map((swatch) => [swatch.key, swatch[side]]));
    },
    modeOptions() {
      return MODE_OPTIONS;
    },
    modeLabel() {
      const option = MODE_OPTIONS.find((item) => item.key === this.mode);
      return option ? option.label : '';
    },
    allGames() {
      return this.allStat('games_played');
    },
    leaderboard() {
      return (this.recap.leaderboard || []).filter((row) => this.modeKey(row) === this.mode);
    },
    allTiles() {
      return [...mainTiles(this.totals), ...extraTiles(this.totals)];
    },
    eventDays() {
      return Math.max(1, moment(this.recap.endsAt).diff(moment(this.recap.startsAt), 'days', true));
    },
    games() {
      return this.stat('games_played');
    },
    averages() {
      const avg = this.modeSections.averages[0];
      if (!avg || !avg.games) {
        return [];
      }
      return [
        { label: 'Hero damage', value: this.format(avg.hero_damage) },
        { label: 'Self-healing', value: this.format(avg.self_healing) },
        { label: 'Damage taken', value: this.format(avg.damage_taken) },
        { label: 'XP contribution', value: this.format(avg.experience_contribution) },
        { label: 'Mercenary camps', value: Number(avg.merc_camp_captures).toFixed(2) },
        { label: 'Vengeances', value: Number(avg.vengeance).toFixed(2) },
        { label: 'Game length', value: this.gameLength(Math.round(avg.game_length)) },
      ];
    },
    ledger() {
      const games = this.games || 1;
      const deaths = this.stat('deaths');
      return [
        { label: 'Win rate', value: this.winRate(this.stat('wins'), this.games) },
        { label: 'Takedowns per game', value: (this.stat('takedowns') / games).toFixed(1) },
        { label: 'Deaths per game', value: (deaths / games).toFixed(1) },
        { label: 'Takedowns per death', value: deaths ? (this.stat('takedowns') / deaths).toFixed(2) : '-' },
        { label: 'Void damage per game', value: this.format(Math.round(this.stat('spell_damage') / games)) },
        { label: 'One game every', value: this.games ? this.duration((this.eventDays * 86400) / this.games) : '-' },
      ];
    },
    // Stage 0 is the event start; it has no threshold in the snapshot.
    timelineStages() {
      return [{ stage: 0, threshold: 0, reached_at: this.recap.startsAt }, ...this.stages];
    },
    stages() {
      return [...(this.sections.stages || [])].sort((a, b) => a.stage - b.stage);
    },
    daily() {
      return [...this.modeSections.daily].sort((a, b) => (a.day < b.day ? -1 : 1));
    },
    gameModes() {
      return Object.values(this.final.byGameType || {})
        .filter((row) => Number(row.game_type) !== 0)
        .sort((a, b) => b.games_played - a.games_played);
    },
    regions() {
      return [...this.modeSections.regions].sort((a, b) => Number(a.region) - Number(b.region));
    },
    regionTotal() {
      return this.regions.reduce((sum, row) => sum + row.games + row.bans, 0);
    },
    heatmapRegions() {
      const games = {};
      this.modeSections.hourly.forEach((cell) => {
        if (cell.region !== undefined) {
          games[cell.region] = (games[cell.region] || 0) + cell.games;
        }
      });
      return Object.keys(games).map(Number).sort((a, b) => a - b);
    },
    heatmapRegionOptions() {
      return this.heatmapRegions.map((region) => ({ key: region, label: this.recap.regions[region] || String(region) }));
    },
    // Region id order (NA first); defaults to the first.
    heatmapRegion() {
      return this.heatmapRegions.includes(this.selectedHeatmapRegion) ? this.selectedHeatmapRegion : this.heatmapRegions[0];
    },
    heatmapZone() {
      return REGION_ZONES[this.heatmapRegion] || moment.tz.guess();
    },
    heatmapZoneLabel() {
      return REGION_ZONES[this.heatmapRegion] ? `${this.recap.regions[this.heatmapRegion]} local time (${REGION_ZONE_LABELS[this.heatmapRegion]})` : 'your local time';
    },
    heatmap() {
      const rows = Array.from({ length: 7 }, () => Array(24).fill(0));
      // One offset for the whole event, taken at its start.
      const offsetHours = Math.round(moment.tz(this.recap.startsAt, this.heatmapZone).utcOffset() / 60);
      this.modeSections.hourly
        .filter((cell) => cell.region === undefined || cell.region === this.heatmapRegion)
        .forEach((cell) => {
          // DAYOFWEEK is 1 = Sunday; shift to 0 = Monday hour-of-week, then into the region's time.
          const utcHourOfWeek = ((cell.weekday + 5) % 7) * 24 + cell.hour;
          const local = (((utcHourOfWeek + offsetHours) % 168) + 168) % 168;
          rows[Math.floor(local / 24)][local % 24] += cell.games;
        });
      let max = 0;
      let peak = null;
      rows.forEach((row, day) => row.forEach((games, hour) => {
        if (games > max) {
          max = games;
          peak = `${DAY_NAMES[day]} around ${this.hour12(hour)}`;
        }
      }));
      return { rows, max, peak };
    },
    launches() {
      return [...(this.sections.launches || [])].sort((a, b) => b.games - a.games);
    },
    launchDays() {
      const row = this.launches[0];
      return row && row.days ? Number(row.days) : Math.round(this.eventDays);
    },
    launchMax() {
      return this.launches.reduce((max, row) => Math.max(max, row.games), 0);
    },
    launchVerdict() {
      const index = this.launches.findIndex((row) => row.hero == HERO_ID);
      if (index === 0) {
        return 'Yes. No recent hero came close.';
      }
      if (index > 0) {
        return `Not quite. ${this.heroName(this.launches[0].hero)} still holds the crown. For now.`;
      }
      return '';
    },
    recordRows() {
      return this.modeSections.records;
    },
    firstGame() {
      return this.allSections.records.find((row) => row.record === 'first_game' && (row.place || 1) === 1) || null;
    },
    records() {
      return this.buildRecords(this.recordRows);
    },
    nerf() {
      return this.nerfSummary(this.modeSections.nerf);
    },
    allNerf() {
      return this.nerfSummary(this.allSections.nerf);
    },
    nerfedTalents() {
      const rows = this.modeSections.nerf_talents;
      const levelGames = {};
      rows.forEach((row) => {
        const key = `${Number(row.after_nerf)}-${row.level}`;
        levelGames[key] = (levelGames[key] || 0) + row.games;
      });
      const byTalent = {};
      rows.forEach((row) => {
        if (!NERFED_TALENTS.includes(this.talentName(row.talent_id))) {
          return;
        }
        byTalent[row.talent_id] = byTalent[row.talent_id] || { talent_id: row.talent_id, level: row.level };
        byTalent[row.talent_id][Number(row.after_nerf) ? 'after' : 'before'] = row;
      });
      const pick = (side, after) => (side ? this.percent(side.games, levelGames[`${after}-${side.level}`]) : '-');
      return Object.values(byTalent)
        .sort((a, b) => a.level - b.level)
        .map((talent) => ({
          ...talent,
          pickBefore: pick(talent.before, 0),
          pickAfter: pick(talent.after, 1),
          winBefore: talent.before ? this.winRate(talent.before.wins, talent.before.games) : '-',
          winAfter: talent.after ? this.winRate(talent.after.wins, talent.after.games) : '-',
        }));
    },
    recordGroups() {
      const byKey = Object.fromEntries(this.records.map((record) => [record.record, record]));
      return RECORD_GROUPS
        .map((group) => ({ title: group.title, records: group.records.map((record) => byKey[record.key]).filter(Boolean) }))
        .filter((group) => group.records.length);
    },
    builds() {
      return [...this.modeSections.builds].sort((a, b) => a.rank - b.rank);
    },
    talentTiers() {
      const byLevel = {};
      this.modeSections.talents.forEach((row) => {
        byLevel[row.level] = byLevel[row.level] || { level: row.level, games: 0, talents: [] };
        byLevel[row.level].games += row.games;
        byLevel[row.level].talents.push(row);
      });
      return Object.values(byLevel)
        .sort((a, b) => a.level - b.level)
        .map((tier) => ({ ...tier, talents: tier.talents.sort((a, b) => b.games - a.games) }));
    },
    // Shaped like GlobalTalentBuildsSection's rows: talents as objects, win_rate as a percent.
    buildRows() {
      const hero = this.recap.heroes[HERO_ID];
      const levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];
      return this.builds.map((build) => {
        const row = { hero, games_played: build.games, win_rate: build.games ? (build.wins / build.games) * 100 : 0 };
        levels.forEach((level, i) => {
          row[level] = this.recap.talents[build.talents[i]] || null;
        });
        return row;
      });
    },
    // Shaped like GlobalTalentDetailsSection's talentdetaildata: { level: [{ talentInfo, win_rate, popularity, games_played, sort }] }.
    talentGridData() {
      if (!this.talentTiers.length) {
        return null;
      }
      const data = {};
      this.talentTiers.forEach((tier) => {
        data[tier.level] = tier.talents
          .filter((talent) => this.recap.talents[talent.talent_id])
          .map((talent) => {
            const info = this.recap.talents[talent.talent_id];
            return {
              talentInfo: info,
              sort: Number(info.sort) || 0,
              games_played: talent.games,
              win_rate: talent.games ? (talent.wins / talent.games) * 100 : 0,
              popularity: tier.games ? (talent.games / tier.games) * 100 : 0,
            };
          });
      });
      return data;
    },
    matchups() {
      return this.modeSections.matchups;
    },
    victims() {
      return this.victimsOf(this.matchups);
    },
    allVictims() {
      return this.victimsOf(this.allSections.matchups);
    },
    allies() {
      return this.matchups.filter((row) => row.relation === 'ally').sort((a, b) => this.edge(b) - this.edge(a));
    },
    matchupGroups() {
      return [
        { title: 'Victims', info: 'Enemy heroes she beat by more than everyone else does.', rows: this.victims.slice(0, 5) },
        { title: 'Enemies', info: 'Enemy heroes that held her below what they usually concede.', rows: [...this.victims].reverse().slice(0, 5) },
        { title: 'Allies', info: 'Teammates who won more with her than they usually do.', rows: this.allies.slice(0, 5) },
      ];
    },
    maps() {
      return this.sortMaps(this.modeSections.maps);
    },
    allMaps() {
      return this.sortMaps(this.allSections.maps);
    },
    // Her games in the selected mode, from the same score rows the awards are counted from.
    awardGames() {
      const averages = this.modeSections.averages[0];
      return averages ? Number(averages.games) || 0 : 0;
    },
    awardMvpCount() {
      const mvp = this.modeSections.awards.find((row) => this.isMvp(row));
      return mvp ? Number(mvp.count) || 0 : 0;
    },
    awards() {
      return [...this.modeSections.awards].sort((a, b) => b.count - a.count);
    },
    devotees() {
      return this.devoteeSummary(this.modeSections.devotees);
    },
    allDevotees() {
      return this.devoteeSummary(this.allSections.devotees);
    },
    eyeDays() {
      return Object.entries((this.recap.eyeHunt && this.recap.eyeHunt.per_day) || {})
        .map(([date, count]) => ({ date, count }))
        .sort((a, b) => (a.date < b.date ? -1 : 1));
    },
    eyeBusiestDay() {
      return this.eyeDays.reduce((best, day) => (!best || day.count > best.count ? day : best), null);
    },
    eyeDayMax() {
      return this.eyeDays.reduce((max, day) => Math.max(max, day.count), 0);
    },
    leadArticle() {
      const fallen = this.final.stage >= 5;
      return {
        headline: fallen ? 'Xal\'atath Consumes the Nexus' : `Void Reaches Stage ${this.final.stage}, Then Recedes`,
        // Opens with a word so the drop cap never splits a number.
        body: `The Void reached stage ${this.final.stage} of 5 across Heroes Profile after ${this.format(this.allGames)} games `
          + `and ${this.format(this.allStat('bans'))} bans. She won ${this.winRate(this.allStat('wins'), this.allGames)} of those games `
          + `and racked up ${this.format(this.allStat('takedowns'))} takedowns along the way. `
          + (fallen
            ? 'The site\'s logo was consumed, its colours turned violet, and visitors were told the Nexus had fallen. Officials insisted everything was fine.'
            : 'The site cracked, whispered and glitched, but the Nexus held. For now.'),
      };
    },
    shareText() {
      const c = this.card;
      if (c && c.games > 0) {
        return `I fed the Void ${this.format(c.games)} Xal'atath games and won ${this.winRate(c.wins, c.games)}. See what she left behind on Heroes Profile:`;
      }
      return `Xal'atath corrupted Heroes Profile to stage ${this.final.stage} of 5. See what she left behind:`;
    },
    recapUrl() {
      return `${window.location.origin}/Event/Xalatath`;
    },
    // Intent links can't attach an image. For players with games, openShare() uploads
    // their card first and posts their card page, whose og:image is that card.
    shareLinks() {
      const text = this.shareText;
      return [
        { name: 'Post on X', icon: 'fab fa-x-twitter', build: (url) => `https://x.com/intent/post?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}` },
        { name: 'Post on Bluesky', icon: 'fab fa-bluesky', build: (url) => `https://bsky.app/intent/compose?text=${encodeURIComponent(`${text} ${url}`)}` },
      ];
    },
    // The contribution card for the selected mode (older cached cards have no per-mode views).
    card() {
      const c = this.contribution;
      if (!c) {
        return null;
      }
      return c.views ? (c.views[this.mode] || c.views.all) : c;
    },
    // Never played her and never faced her in this mode.
    cardEmpty() {
      const c = this.card;
      return !!c && !(c.games > 0) && !(c.faced && c.faced.games);
    },
    noGamesLine() {
      const where = this.mode !== 'all' ? ` in ${this.modeLabel}` : '';
      return this.cardEmpty
        ? `Never played her${where}, never faced her. The Void noticed.`
        : `Never played her${where}. She played you instead.`;
    },
    facedTiles() {
      const faced = (this.card && this.card.faced) || {};
      return [
        { label: 'Games faced', value: this.format(faced.games) },
        { label: 'Deaths involving her', value: `≈ ${this.format(faced.involved)}` },
        { label: 'Her killing blows', value: `≈ ${this.format(faced.killing_blows)}` },
      ];
    },
    contributionMates() {
      const c = this.card || {};
      return [
        { title: 'Played with', rows: c.allies || [] },
        { title: 'Played against', rows: c.enemies || [] },
      ];
    },
    reclaimedBody() {
      const days = Math.round(this.eventDays);
      return `After ${days} days under the Void, the Heroes Profile team has taken the site back. `
        + 'The logo is whole again, the colours have returned, and the whispers have stopped. '
        + 'Staff remain on watch. Our sources whisper that more heroes may yet find their way to the Nexus, '
        + 'and if they do, Heroes Profile will be here tracking every game.';
    },
    articles() {
      const games = this.allGames || 1;
      const list = [
        {
          headline: `Nexus Loses ${this.compact(this.allStat('spell_damage'))} Health to the Void, Sources Say`,
          body: `Investigators confirm ${this.format(this.allStat('spell_damage'))} Void damage, about ${this.format(Math.round(this.allStat('spell_damage') / games))} a game. `
            + `Her best single outing dealt ${this.recordDisplay('spell_damage')}.`,
        },
        {
          headline: `Xal'atath Dies ${this.format(this.allStat('deaths'))} Times, Remains Unrepentant`,
          body: `That's ${(this.allStat('deaths') / games).toFixed(1)} deaths a game and ${this.format(Math.floor(this.allStat('time_spent_dead') / 86400))} days spent sealed in the dagger. `
            + `She has declined to comment.`,
        },
        {
          headline: `${this.format(this.allStat('bans'))} Drafts Ban Her. She Spreads Anyway`,
          body: `Bans counted toward the corruption meter too, so every attempt to seal her away only fed the Void.`,
        },
      ];
      if (this.allMaps.length) {
        const map = this.allMaps[0];
        list.push({
          headline: `${this.mapName(map.game_map)} Declared Her Favorite Battleground`,
          body: `She won ${this.winRate(map.wins, map.games)} of ${this.format(map.games)} games there, her best of any map.`,
        });
      }
      const tourists = this.allDevotees.rows.find((row) => String(row.bucket) === '1');
      const devoted = this.allDevotees.rows.find((row) => String(row.bucket) === '10+');
      if (tourists && this.allDevotees.total) {
        list.push({
          headline: `${this.percent(tourists.players, this.allDevotees.total)} of Heroes Tried Her Once and Fled`,
          body: `${this.format(this.allDevotees.total)} players answered her call. ${devoted ? `${this.format(devoted.players)} of them stayed for 10 games or more.` : ''}`,
        });
      }
      if (this.recap.eyeHunt && this.recap.eyeHunt.finders) {
        list.push({
          headline: `${this.format(this.recap.eyeHunt.finders)} Mortals Claim to Have Seen "The Eye"`,
          body: `Witnesses describe a single eye hidden somewhere on the site. Finders now bear the Mark of the Void beside their names.`,
        });
      }
      if (this.allVictims.length) {
        const victim = this.allVictims[0];
        list.push({
          headline: `${this.heroName(victim.hero)} Players Request Therapy`,
          body: `She won ${this.winRate(victim.wins, victim.games)} of her ${this.format(victim.games)} games against ${this.heroName(victim.hero)}, `
            + `${this.edge(victim).toFixed(1)} points better than everyone else manages against them.`,
        });
      }
      if (this.allNerf) {
        list.push({
          headline: 'Blizzard Responds With Balance Update',
          body: `${this.allNerf.verdict} Win rate went from ${this.allNerf.rows[0].before} to ${this.allNerf.rows[0].after}.`,
        });
      }
      return list;
    },
  },
  watch: {
    mode() {
      this.cardUrl = null;
      this.chart?.destroy();
      this.chart = null;
      this.$nextTick(() => this.drawDailyChart());
    },
  },
  mounted() {
    this.drawDailyChart();
    try {
      this.canShareFiles = !!(navigator.canShare && navigator.canShare({ files: [new File([''], 'card.png', { type: 'image/png' })] }));
    } catch (e) {
      this.canShareFiles = false;
    }
    // Built from whichever sections actually rendered, numbered like newspaper pages.
    this.indexEntries = Array.from(this.$el.querySelectorAll('[data-index]')).map((el, i) => ({
      id: el.id,
      label: el.dataset.index,
      page: `A${i + 2}`,
    }));
    // Stage 2 and 3 cards replay what those stages looked like.
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  methods: {
    format: formatNumber,
    glyph: toGlyphs,
    // The What Changed toggle; a new whisper each time it flips to After.
    // The logo a stage started from: the original Heroes Profile logo before the event, else the previous frame.
    logoBefore(stage) {
      return stage === 0 ? '/images/logo/heroesprofilelogo.png' : `/images/event/xalatath/xalatath-logo-stage-${stage - 1}.svg`;
    },
    setChangeView(view) {
      const after = view === 'after';
      if (after && !this.paletteOn) {
        this.whisperIndex = (this.whisperIndex + 1) % this.whispers.length;
      }
      this.paletteOn = after;
    },
    // Her win rate with/against a hero minus the baseline, in points. Against a hero the
    // baseline is what everyone wins against them (100 - their win rate); with one it's
    // their own win rate.
    edge(row) {
      const hers = row.games ? (row.wins / row.games) * 100 : 0;
      if (!row.hero_games) {
        return hers - 50;
      }
      const heroRate = (row.hero_wins / row.hero_games) * 100;
      return row.relation === 'enemy' ? hers - (100 - heroRate) : hers - heroRate;
    },
    edgeLabel(row) {
      const edge = this.edge(row);
      return `${edge >= 0 ? '+' : ''}${edge.toFixed(1)} pts vs usual`;
    },
    jumpTo(id) {
      const el = document.getElementById(id);
      if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    },
    // Rows with no game_type (and snapshots from before the split) belong to All.
    modeKey(row) {
      return row.game_type === null || row.game_type === undefined ? 'all' : String(row.game_type);
    },
    sectionsFor(mode) {
      const out = {};
      Object.entries(this.sections).forEach(([name, value]) => {
        const rows = Array.isArray(value) ? value : [value];
        out[name] = rows.filter((row) => row && this.modeKey(row) === mode);
      });
      ['daily', 'regions', 'hourly', 'records', 'nerf', 'nerf_talents', 'builds', 'talents', 'matchups', 'maps', 'awards', 'devotees', 'averages']
        .forEach((name) => { out[name] = out[name] || []; });
      return out;
    },
    allStat(column) {
      return Number((this.final.totals || {})[column] || 0);
    },
    buildRecords(rows) {
      return Object.keys(RECORD_LABELS)
        .map((key) => {
          const top = rows
            .filter((row) => row.record === key)
            .sort((a, b) => (a.place || 1) - (b.place || 1))
            .map((row, i) => ({ ...row, place: row.place || i + 1, display: this.recordValue(key, row.value) }));
          if (!top.length) {
            return null;
          }
          return { record: key, label: RECORD_LABELS[key], display: top[0].display, top };
        })
        .filter(Boolean);
    },
    sortMaps(rows) {
      return [...rows].sort((a, b) => b.wins / b.games - a.wins / a.games);
    },
    devoteeSummary(rows) {
      const order = ['10+', '2-9', '1'];
      const sorted = [...rows]
        .sort((a, b) => order.indexOf(String(a.bucket)) - order.indexOf(String(b.bucket)))
        .map((row) => ({ ...row, label: DEVOTEE_LABELS[row.bucket] || row.bucket }));
      return { rows: sorted, total: sorted.reduce((sum, row) => sum + row.players, 0) };
    },
    // Sorted by edge over the baseline, so a hero everyone beats doesn't top the list.
    victimsOf(rows) {
      return rows.filter((row) => row.relation === 'enemy').sort((a, b) => this.edge(b) - this.edge(a));
    },
    nerfSummary(rows) {
      const before = rows.find((row) => !Number(row.after_nerf));
      const after = rows.find((row) => Number(row.after_nerf));
      if (!before || !after || !before.games || !after.games) {
        return null;
      }
      const rate = (value, total) => (total ? (value / total) * 100 : 0);
      const pct = (value) => `${value.toFixed(1)}%`;
      const points = (from, to) => `${to - from >= 0 ? '+' : ''}${(to - from).toFixed(1)} pts`;
      const relative = (from, to) => (from ? `${to - from >= 0 ? '+' : ''}${(((to - from) / from) * 100).toFixed(1)}%` : '-');
      const number = (from, to, label, digits = 0) => ({
        label,
        before: digits ? Number(from).toFixed(digits) : this.format(from),
        after: digits ? Number(to).toFixed(digits) : this.format(to),
        change: relative(Number(from), Number(to)),
      });

      const winBefore = rate(before.wins, before.games);
      const winAfter = rate(after.wins, after.games);
      const pickBefore = rate(before.games, before.total_games);
      const pickAfter = rate(after.games, after.total_games);
      const banBefore = rate(before.bans, before.total_games);
      const banAfter = rate(after.bans, after.total_games);

      const drop = winBefore - winAfter;
      let verdict = 'She shrugged it off. Her win rate barely moved.';
      if (drop >= 2) {
        verdict = `It worked. Her win rate fell ${drop.toFixed(1)} points.`;
      } else if (drop >= 0.5) {
        verdict = `It stung, a little. Her win rate fell ${drop.toFixed(1)} points.`;
      } else if (drop <= -0.5) {
        verdict = `It backfired. Her win rate went up ${Math.abs(drop).toFixed(1)} points.`;
      }

      return {
        before,
        after,
        verdict,
        rows: [
          { label: 'Win rate', before: pct(winBefore), after: pct(winAfter), change: points(winBefore, winAfter) },
          { label: 'Games played', before: this.format(before.games), after: this.format(after.games), change: '' },
          { label: 'Picked (per 100 games)', before: pickBefore.toFixed(1), after: pickAfter.toFixed(1), change: relative(pickBefore, pickAfter) },
          { label: 'Banned (per 100 games)', before: banBefore.toFixed(1), after: banAfter.toFixed(1), change: relative(banBefore, banAfter) },
          number(before.spell_damage, after.spell_damage, 'Void damage per game'),
          number(before.hero_damage, after.hero_damage, 'Hero damage per game'),
          number(before.takedowns, after.takedowns, 'Takedowns per game', 2),
          number(before.deaths, after.deaths, 'Deaths per game', 2),
        ],
      };
    },
    // match_award 1 is MVP, same as the player page's MVP rate.
    isMvp(row) {
      return String(row.award) === '1';
    },
    // MVP is out of all her games; other awards are out of the games she wasn't MVP.
    awardShareOf(row) {
      if (!this.awardGames) {
        return 0;
      }
      return this.isMvp(row) ? this.awardGames : Math.max(this.awardGames - this.awardMvpCount, 0);
    },
    recordValue(key, value) {
      return TIME_RECORDS.includes(key) ? this.gameLength(value) : this.format(value);
    },
    recordDisplay(key) {
      const record = this.buildRecords(this.allSections.records).find((row) => row.record === key);
      return record ? record.display : '-';
    },
    stageCardStyle(stage) {
      return STAGE_CARD_STYLES[stage] || {};
    },
    stat(column) {
      return Number(this.totals[column] || 0);
    },
    percent(value, total) {
      return total ? `${((value / total) * 100).toFixed(1)}%` : '0%';
    },
    winRate(wins, games) {
      return games ? `${((wins / games) * 100).toFixed(1)}%` : '-';
    },
    // Faint purple = few games, solid purple = the busiest hour.
    heatOpacity(share) {
      return 0.08 + 0.92 * share;
    },
    hour12(hour) {
      return `${hour % 12 || 12} ${hour < 12 ? 'AM' : 'PM'}`;
    },
    shortHour(hour) {
      return `${hour % 12 || 12}${hour < 12 ? 'a' : 'p'}`;
    },
    compact(value) {
      return new Intl.NumberFormat('en-US', { notation: 'compact', maximumFractionDigits: 1 }).format(value || 0);
    },
    formatDate(value) {
      return value ? moment.utc(value).format('MMM D, YYYY') : '';
    },
    gameLength(seconds) {
      const minutes = Math.floor(seconds / 60);
      return `${minutes}:${String(seconds % 60).padStart(2, '0')}`;
    },
    duration(seconds) {
      const minutes = Math.round(seconds / 60);
      if (minutes < 1) {
        return `${Math.round(seconds)} seconds`;
      }
      if (minutes < 120) {
        return `${minutes} minute${minutes === 1 ? '' : 's'}`;
      }
      return `${(minutes / 60).toFixed(1)} hours`;
    },
    heroName(id) {
      const hero = this.recap.heroes[id];
      return hero ? hero.name : `Hero ${id}`;
    },
    mapName(id) {
      const map = this.recap.maps[id];
      return map ? map.name : `Map ${id}`;
    },
    talentName(id) {
      const talent = this.recap.talents[id];
      return talent ? (talent.title || talent.talent_name) : 'Unknown talent';
    },
    awardTitle(id) {
      const award = this.recap.awards[id];
      return award ? award.title : id;
    },
    drawDailyChart() {
      if (!this.daily.length || !this.$refs.dailyChart) {
        return;
      }
      const stageDays = new Set(this.stages.filter((stage) => stage.reached_at).map((stage) => moment.utc(stage.reached_at).format('YYYY-MM-DD')));
      const labels = this.daily.map((row) => moment.utc(row.day).format('MMM D'));
      const radius = this.daily.map((row) => (stageDays.has(moment.utc(row.day).format('YYYY-MM-DD')) ? 8 : 2));

      const nerfDay = moment.utc(this.nerf && this.nerf.after.first_game ? this.nerf.after.first_game : NERF_DATE).format('MMM D');
      // Dashed vertical line on the balance update day.
      const nerfMarker = {
        id: 'nerfMarker',
        afterDatasetsDraw(chart) {
          const index = nerfDay ? chart.data.labels.indexOf(nerfDay) : -1;
          if (index < 0) {
            return;
          }
          const x = chart.scales.x.getPixelForValue(index);
          const { top, bottom } = chart.chartArea;
          const ctx = chart.ctx;
          ctx.save();
          ctx.strokeStyle = '#f87171';
          ctx.lineWidth = 2;
          ctx.setLineDash([6, 4]);
          ctx.beginPath();
          ctx.moveTo(x, top);
          ctx.lineTo(x, bottom);
          ctx.stroke();
          ctx.setLineDash([]);
          ctx.fillStyle = '#f87171';
          ctx.font = 'bold 14px sans-serif';
          ctx.fillText('Nerfed (balance update)', x + 6, top + 16);
          ctx.restore();
        },
      };

      this.chart = markRaw(new Chart(this.$refs.dailyChart.getContext('2d'), {
        type: 'line',
        plugins: [nerfMarker],
        data: {
          labels,
          datasets: [
            {
              label: 'Games',
              data: this.daily.map((row) => row.games),
              borderColor: '#c084fc',
              backgroundColor: 'rgba(192, 132, 252, 0.2)',
              pointBackgroundColor: '#c084fc',
              pointRadius: radius,
              yAxisID: 'y',
            },
            {
              label: 'Win rate %',
              data: this.daily.map((row) => (row.games ? Number(((row.wins / row.games) * 100).toFixed(1)) : null)),
              borderColor: '#9ca3af',
              borderDash: [6, 4],
              pointRadius: 0,
              yAxisID: 'y1',
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true, position: 'left' },
            y1: { position: 'right', grid: { drawOnChartArea: false } },
          },
        },
      }));
    },
    loadImage(src) {
      return new Promise((resolve) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null);
        img.src = src;
      });
    },
    // Uploads this player's card once per page view; returns their card page, or the recap if they can't have one.
    async ensureCardUploaded() {
      if (this.cardUrl) {
        return this.cardUrl;
      }
      const canvas = await this.buildCardCanvas();
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
      if (!blob) {
        return this.recapUrl;
      }
      const form = new FormData();
      form.append('card', blob, 'card.png');
      const response = await this.$axios.post('/Event/Xalatath/Card', form);
      this.cardUrl = (response.data && response.data.url) || this.recapUrl;
      return this.cardUrl;
    },
    async openShare(event, link) {
      if (!this.card || !this.card.games) {
        return;
      }
      event.preventDefault();
      // Opened before the upload so the popup isn't blocked.
      const win = window.open('', '_blank');
      let url = this.recapUrl;
      try {
        url = await this.ensureCardUploaded();
      } catch (e) {
        // upload failed; share the recap instead
      }
      if (win) {
        win.opener = null;
        win.location.href = link.build(url);
      } else {
        window.open(link.build(url), '_blank', 'noopener');
      }
    },
    async downloadCard() {
      this.saveCard(await this.buildCardCanvas());
    },
    // Hands the card image to the device's share sheet (X, Discord, Messages...).
    async shareCard() {
      const canvas = await this.buildCardCanvas();
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/png'));
      if (!blob) {
        return;
      }
      const file = new File([blob], `void-corruption-${this.contribution.battletag}.png`, { type: 'image/png' });
      try {
        await navigator.share({
          files: [file],
          title: "Xal'atath Void Corruption",
          text: `${this.shareText} ${window.location.origin}/Event/Xalatath`,
        });
      } catch (e) {
        // cancelled, or the target refused the file
      }
    },
    // Draws the same layout as the on-page card.
    async buildCardCanvas() {
      const c = this.card;
      const battletag = this.contribution.battletag;
      const canvas = document.createElement('canvas');
      canvas.width = 1200;
      canvas.height = 675;
      const ctx = canvas.getContext('2d');

      const gradient = ctx.createRadialGradient(600, 300, 50, 600, 340, 750);
      gradient.addColorStop(0, '#3a1470');
      gradient.addColorStop(1, '#08030f');
      ctx.fillStyle = gradient;
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      const build = c.build ? c.build.talents : [];
      const mates = [...(c.allies || []), ...(c.enemies || [])];
      const [logo, talentImages, heroImages] = await Promise.all([
        this.loadImage(`/images/event/xalatath/xalatath-logo-stage-${this.final.stage}.png`),
        Promise.all(build.map((talent) => (talent && talent.icon ? this.loadImage(`/images/talents/${talent.icon}`) : null))),
        Promise.all(mates.map((mate) => (mate.hero ? this.loadImage(`/images/heroes/${mate.hero.short_name}.png`) : null))),
      ]);

      const x = 340;
      const title = `XAL'ATATH VOID CORRUPTION${this.mode !== 'all' ? ` · ${this.modeLabel.toUpperCase()}` : ''}`;

      // Never played or faced her: logo and text centred vertically, like the on-page banner.
      if (this.cardEmpty) {
        if (logo) {
          ctx.drawImage(logo, 50, 212, 240, 250);
        }
        ctx.fillStyle = '#c9a8ff';
        ctx.font = 'bold 22px sans-serif';
        ctx.fillText(title, x, 275);
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 54px sans-serif';
        ctx.fillText(battletag, x, 340);
        ctx.font = '30px sans-serif';
        ctx.fillText(this.noGamesLine, x, 390);
        ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
        ctx.font = '20px sans-serif';
        ctx.fillText('heroesprofile.com/Event/Xalatath', x, 435);
        return canvas;
      }

      // Left: logo and stage.
      if (logo) {
        ctx.drawImage(logo, 50, 120, 240, 250);
      }

      ctx.fillStyle = '#c9a8ff';
      ctx.font = 'bold 22px sans-serif';
      ctx.fillText(title, x, 70);
      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 54px sans-serif';
      ctx.fillText(battletag, x, 130);

      if (c.games > 0 && c.faced && c.faced.games) {
        ctx.font = '20px sans-serif';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        ctx.fillText(`Faced her in ${this.format(c.faced.games)} games: ≈${this.format(c.faced.involved)} deaths involving her, ≈${this.format(c.faced.killing_blows)} to her killing blow`, x, 252);
        ctx.fillStyle = '#ffffff';
      }

      if (c.games > 0) {
        ctx.font = '30px sans-serif';
        ctx.fillText(`Fed the Void ${this.format(c.games)} games, won ${this.format(c.wins)} (${this.winRate(c.wins, c.games)})`, x, 180);

        ctx.font = '20px sans-serif';
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        const modes = (c.modes || []).slice(0, 3).map((mode) => `${mode.name} ${this.winRate(mode.wins, mode.games)}`).join('   ·   ');
        if (modes) {
          ctx.fillText(`Win rate by mode:   ${modes}`, x, 216);
        }

        if (c.build) {
          ctx.fillStyle = '#c9a8ff';
          ctx.font = 'bold 16px sans-serif';
          ctx.fillText('PLAYED BUILD', x, 292);
          talentImages.forEach((img, i) => {
            if (img) {
              ctx.drawImage(img, x + i * 70, 302, 60, 60);
            }
          });
          ctx.fillStyle = '#ffffff';
          ctx.font = '20px sans-serif';
          ctx.fillText(`${this.format(c.build.games)} games`, x + 7 * 70 + 10, 326);
          ctx.fillText(`${this.winRate(c.build.wins, c.build.games)} won`, x + 7 * 70 + 10, 352);
        }

        [['PLAYED WITH', c.allies || [], x, 0], ['PLAYED AGAINST', c.enemies || [], x + 420, (c.allies || []).length]].forEach(([title, rows, left, offset]) => {
          ctx.fillStyle = '#c9a8ff';
          ctx.font = 'bold 16px sans-serif';
          // Column headers once, numbers right-aligned under them.
          ctx.fillText(title, left, 400);
          ctx.textAlign = 'right';
          ctx.fillText('GAMES', left + 290, 400);
          ctx.fillText('WIN RATE', left + 390, 400);
          ctx.textAlign = 'left';
          rows.forEach((mate, i) => {
            const top = 415 + i * 62;
            const img = heroImages[offset + i];
            if (img) {
              ctx.drawImage(img, left, top, 50, 50);
            }
            ctx.fillStyle = '#ffffff';
            ctx.font = '22px sans-serif';
            ctx.fillText(mate.hero ? mate.hero.name : '', left + 62, top + 33);
            ctx.textAlign = 'right';
            ctx.fillText(this.format(mate.games), left + 290, top + 33);
            ctx.fillText(this.winRate(mate.wins, mate.games), left + 390, top + 33);
            ctx.textAlign = 'left';
          });
        });
      } else {
        ctx.font = '30px sans-serif';
        ctx.fillText(this.noGamesLine, x, 180);

        // Faced tiles, same as the on-page card.
        this.facedTiles.forEach((tile, i) => {
          const left = x + i * 280;
          ctx.fillStyle = 'rgba(58, 20, 112, 0.35)';
          ctx.strokeStyle = '#5b21b6';
          ctx.lineWidth = 2;
          ctx.beginPath();
          ctx.roundRect(left, 225, 260, 120, 8);
          ctx.fill();
          ctx.stroke();
          ctx.textAlign = 'center';
          ctx.fillStyle = 'rgba(255, 255, 255, 0.75)';
          ctx.font = 'bold 16px sans-serif';
          ctx.fillText(tile.label.toUpperCase(), left + 130, 265);
          ctx.fillStyle = '#ffffff';
          ctx.font = 'bold 42px sans-serif';
          ctx.fillText(tile.value, left + 130, 318);
          ctx.textAlign = 'left';
        });
      }

      ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
      ctx.font = '20px sans-serif';
      ctx.fillText('heroesprofile.com/Event/Xalatath', x, 645);

      return canvas;
    },
    saveCard(canvas) {
      const link = document.createElement('a');
      link.download = `void-corruption-${this.contribution.battletag}.png`;
      link.href = canvas.toDataURL('image/png');
      link.click();
    },
  },
};
</script>
