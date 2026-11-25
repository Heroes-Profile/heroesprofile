<template>
  <div>
    <page-heading :heading="'The 2026 Redesign'" :subheading="'What changed on Heroes Profile'"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-10">
      <!-- The same pages screenshotted on the old site and the new one; drag the handle to wipe between them -->
      <section class="flex flex-col gap-4">
        <div>
          <h2 class="text-2xl font-bold">{{ $t('Side by side') }}</h2>
          <p class="text-sm text-subtext mt-1">{{ $t('Browse the site with its own menus: each page shows on the old site and the new one. Drag the handle to compare.') }}</p>
        </div>
        <!-- A copy of the site's nav, on the nav's own classes; its links pick a screenshot instead of leaving the page.
             Desktop opens menus on hover like the real nav, phones on a tap -->
        <nav class="main-navigation-wrapper w-full max-w-[105rem] mx-auto rounded bg-surface px-4 md:px-6 py-1 flex max-md:flex-col md:items-center gap-x-6 text-sm" :aria-label="$t('Side by side')">
          <a href="#" :class="['flex shrink-0 items-center whitespace-nowrap font-logo text-xl max-md:self-center max-md:py-2', { 'text-teal': screenshot === 'home' }]" @click.prevent="pickScreenshot('home')">
            Heroes<img class="w-8 shrink-0 mx-2" src="/images/logo/heroesprofilelogo.png" alt="" />Profile
          </a>
          <div class="flex max-md:flex-col md:space-x-5 md:mx-auto">
            <template v-for="menu in navMenus" :key="menu.label">
              <div v-if="menu.groups" class="relative group inline-block nav-item">
                <a href="#" :class="['cursor-pointer', { 'text-teal': menuHas(menu, screenshot) }]" @click.prevent="openMenu = openMenu === menu.label ? null : menu.label">
                  {{ menu.label === 'Zemill' ? menu.label : $t(menu.label) }}<basil-icon name="arrow" class="rotate-90 group-hover:-rotate-90 transition-transform text-xs ml-2" aria-hidden="true"></basil-icon>
                </a>
                <div :class="['nav-dropdown', { '!block': openMenu === menu.label, 'nav-dropdown-flip': menu.wide }]">
                  <!-- Other: its groups side by side in columns, each a plain list -->
                  <div v-if="menu.wide" class="nav-dropdown-inner-wrapper md:grid md:grid-cols-4 md:gap-x-2 md:w-max">
                    <div v-for="group in menu.groups" :key="group.label" class="flex flex-col">
                      <span class="nav-dropdown-label !mt-0 !pt-2 !border-t-0">{{ $t(group.label) }}</span>
                      <a v-for="item in group.items" :key="item.key" href="#" :class="['whitespace-nowrap', { 'bg-teal': screenshot === item.key }]" @click.prevent="pickScreenshot(item.key)">{{ item.name || $t(item.label) }}</a>
                    </div>
                  </div>
                  <div v-else class="nav-dropdown-inner-wrapper">
                    <template v-for="group in menu.groups" :key="group.label || 'items'">
                      <span v-if="group.label" class="nav-dropdown-label">{{ $t(group.label) }}</span>
                      <template v-for="item in group.items" :key="item.key || item.label">
                        <!-- A flyout like the nav's hero, map and role lists: clicking its heading opens the All page, as on the site; hovering opens the list -->
                        <div v-if="item.flyout" class="nav-flyout-wrapper">
                          <a href="#" :class="['flex justify-between items-center', { 'bg-teal': item.flyout.some((entry) => entry.key === screenshot) }]" @click.prevent="pickScreenshot(item.flyout[0].key)">{{ $t(item.label) }} <span class="nav-flyout-arrow ml-2">›</span></a>
                          <div class="nav-flyout">
                            <div class="nav-dropdown-inner-wrapper rounded-b rounded-r flex flex-col">
                              <a v-for="entry in item.flyout" :key="entry.key" href="#" :class="['whitespace-nowrap', { 'bg-teal': screenshot === entry.key }]" @click.prevent="pickScreenshot(entry.key)">{{ entry.name || $t(entry.label) }}</a>
                            </div>
                          </div>
                        </div>
                        <a v-else href="#" :class="{ 'bg-teal': screenshot === item.key }" @click.prevent="pickScreenshot(item.key)">{{ $t(item.label) }}</a>
                      </template>
                    </template>
                  </div>
                </div>
              </div>
              <a v-else href="#" class="nav-item" @click.prevent="pickScreenshot(menu.key)"><span :class="{ 'text-teal': screenshot === menu.key }">{{ $t(menu.label) }}</span></a>
            </template>
          </div>
        </nav>
        <div class="relative w-full max-w-[105rem] mx-auto rounded overflow-hidden select-none aspect-video bg-surface" :style="{ '--wipe': wipe + '%' }">
          <img :src="`/images/redesign/2026/${screenshot}-after.jpg`" :alt="$t('After')" class="absolute inset-0 w-full h-full object-cover object-top" draggable="false" />
          <!-- A page with no old version (new, or unreachable on the old site) shows the new shot alone -->
          <template v-if="!afterOnly.includes(screenshot)">
          <img :src="`/images/redesign/2026/${screenshot}-before.jpg`" :alt="$t('Before')" class="absolute inset-0 w-full h-full object-cover object-top [clip-path:inset(0_calc(100%-var(--wipe))_0_0)]" draggable="false" />
          <!-- One label in the middle, naming whichever side shows more -->
          <span class="absolute top-3 left-1/2 -translate-x-1/2 rounded-control px-5 py-2 text-xl font-bold pointer-events-none" :class="wipe >= 50 ? 'bg-surface' : 'bg-teal'">{{ wipe >= 50 ? $t('Before') : $t('After') }}</span>
          <span class="absolute inset-y-0 w-0.5 bg-white pointer-events-none left-[var(--wipe)]">
            <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-teal flex items-center justify-center shadow-lg"><i class="fas fa-arrows-alt-h"></i></span>
          </span>
          <input type="range" min="0" max="100" step="0.1" v-model.number="wipe" :aria-label="$t('Drag the handle to compare.')" class="absolute inset-0 w-full h-full opacity-0 cursor-ew-resize" />
          </template>
          <span v-else class="absolute top-3 left-1/2 -translate-x-1/2 rounded-control px-5 py-2 text-xl font-bold pointer-events-none bg-teal">{{ $t('New in the redesign') }}</span>
        </div>
      </section>

      <panel-box :divider="false">
        <div class="flex flex-col gap-3 max-w-[60rem]">
          <p>{{ $t('In late 2026 every page on Heroes Profile was rebuilt to a new design: darker, calmer and the same from page to page, so the stats are easier to read on any screen.') }}</p>
          <p>{{ $t('The new look was designed by Bluray and built by the Heroes Profile team. Below is what changed, with small examples of each part before and after.') }}</p>
        </div>
      </panel-box>

      <!-- By the numbers -->
      <section class="flex flex-col gap-4">
        <h2 class="text-2xl font-bold">{{ $t('By the numbers') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <stat-box v-for="stat in numbers" :key="stat.title" :title="stat.title" :value="stat.value"></stat-box>
        </div>
      </section>

      <!-- The look and the site shell; one toggle flips every example -->
      <section class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="text-2xl font-bold">{{ $t('The look') }}</h2>
            <p class="text-sm text-subtext mt-1">{{ $t('The parts every page shares.') }}</p>
          </div>
          <tab-button tab1text="Before" tab2text="After" :overridedefaultside="after ? 'right' : 'left'" @tab-click="(side) => after = side === 'right'"></tab-button>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
          <div v-for="card in lookCards" :key="card.key" class="rounded bg-surface px-5 py-4 flex flex-col gap-3">
            <h3 class="text-lg font-bold">{{ $t(card.title) }}</h3>
            <!-- A small slice of the site; Before is drawn in the old colours, After in the site's own -->
            <div class="h-40 rounded-control overflow-hidden text-[0.625rem] leading-tight select-none" aria-hidden="true">

              <div v-if="card.key === 'palette'" class="h-full grid grid-rows-2 gap-1">
                <div class="grid grid-flow-col auto-cols-fr gap-1">
                  <span v-for="colour in after ? palette.after : palette.before" :key="colour" class="rounded-control" :style="{ backgroundColor: colour }"></span>
                </div>
                <div class="grid grid-flow-col auto-cols-fr gap-1">
                  <span v-for="colour in after ? palette.after : palette.before" :key="'t' + colour" class="flex items-center justify-center text-subtext tabular-nums">{{ colour }}</span>
                </div>
              </div>

              <div v-else-if="card.key === 'nav'" :class="['h-full p-2 flex flex-col gap-2', after ? 'bg-page' : 'bg-black']">
                <div v-if="!after" class="flex items-center gap-2 px-2 py-2 bg-[#333] uppercase tracking-[0.14em]">
                  <span class="font-logo">HP</span><span>Players</span><span>Global</span><span>Tools</span>
                  <span class="ml-auto px-2 py-1 rounded bg-blue">API</span>
                </div>
                <div v-else class="flex items-center gap-2 px-2 py-2 rounded-control bg-surface">
                  <img src="/images/logo/heroesprofilelogo.png" alt="" class="h-4 w-auto" /><span>{{ $t('Global Stats') }}</span><span>{{ $t('Tools') }}</span>
                  <span class="ml-auto flex-1 max-w-[6rem] rounded-control bg-field px-2 py-1 text-subtext">{{ $t('Search') }}</span>
                  <span class="px-2 py-1 rounded-control bg-teal">API</span>
                </div>
                <div :class="['flex-1 rounded-control', after ? 'bg-surface' : '']"></div>
              </div>

              <div v-else-if="card.key === 'headings'" :class="['h-full flex flex-col gap-2', after ? 'bg-page p-2' : 'bg-black']">
                <div v-if="!after" class="bg-teal px-4 py-3 text-base uppercase tracking-[0.14em]">Hero Statistics</div>
                <template v-else>
                  <div class="text-base font-bold">{{ $t('Hero Stats') }}</div>
                  <div class="flex gap-1">
                    <span v-for="filter in ['Storm League', 'Rank', 'Map']" :key="filter" class="rounded-control bg-surface-3 px-2 py-1">{{ $t(filter) }}</span>
                    <span class="rounded-control bg-teal px-2 py-1">{{ $t('Filter') }}</span>
                  </div>
                </template>
              </div>

              <div v-else-if="card.key === 'tables'" :class="['h-full', after ? 'bg-surface p-2' : 'bg-black']">
                <div :class="after ? 'rounded-control overflow-hidden' : ''">
                  <div :class="['grid grid-cols-[1fr_auto_auto] gap-3 px-2 py-1.5', after ? 'bg-surface-3' : 'bg-teal uppercase tracking-[0.14em]']">
                    <span>{{ $t('Hero') }}</span><span :class="after ? 'text-teal' : ''">{{ $t('Win Rate') }}</span><span>{{ $t('Games') }}</span>
                  </div>
                  <div v-for="(row, index) in sampleRows" :key="row.hero"
                    :class="['grid grid-cols-[1fr_auto_auto] gap-3 px-2 py-1.5', after ? (index % 2 ? 'bg-surface-2' : 'bg-surface-1') : (index % 2 ? 'bg-[#eee] text-black' : 'bg-[#ddd] text-black')]">
                    <span>{{ row.hero }}</span><span class="tabular-nums">{{ row.winRate }}</span><span class="tabular-nums">{{ row.games }}</span>
                  </div>
                </div>
              </div>

              <div v-else-if="card.key === 'filters'" :class="['h-full p-2 flex flex-col gap-2', after ? 'bg-surface' : 'bg-black']">
                <div class="flex flex-wrap gap-1">
                  <span v-for="filter in ['Game Type', 'Rank', 'Map']" :key="filter" :class="['px-2 py-1', after ? 'rounded-control bg-surface-3' : 'rounded bg-teal uppercase tracking-[0.14em]']">{{ $t(filter) }}</span>
                </div>
                <div v-if="after" class="rounded-control bg-surface-1 p-2 flex flex-col gap-1">
                  <span class="flex items-center justify-between">{{ $t('Storm League') }} · {{ $t('Quick Match') }}<i class="fas fa-times text-subtext"></i></span>
                  <span class="text-teal">{{ $t('Show advanced filters') }} ›</span>
                </div>
                <div v-else class="rounded bg-white text-black p-2 flex flex-col gap-1">
                  <span>Storm League</span><span>Quick Match</span>
                </div>
              </div>

              <div v-else-if="card.key === 'loading'" :class="['h-full p-2', after ? 'bg-surface' : 'bg-black flex items-center justify-center']">
                <img v-if="!after" src="/images/logo/heroesprofilelogo.png" alt="" class="logo-spin h-14 w-auto" />
                <div v-else class="flex flex-col gap-2">
                  <span class="h-4 w-1/2 rounded bg-surface-3 animate-pulse"></span>
                  <span v-for="bar in 4" :key="bar" class="h-5 rounded bg-surface-3 animate-pulse"></span>
                </div>
              </div>

              <div v-else-if="card.key === 'popups'" :class="['h-full p-2 relative', after ? 'bg-page' : 'bg-black']">
                <div v-if="!after" class="w-32 rounded bg-white text-black p-2">
                  <div class="font-bold uppercase">Abathur</div>
                  <div>Win Rate: 51.2</div><div>Games: 10432</div>
                </div>
                <div v-else class="w-40 rounded bg-surface-3 p-2 flex flex-col gap-1 shadow-lg">
                  <div class="flex items-center gap-2"><img src="/images/heroes/abathur.png" alt="" class="w-6 h-6 rounded" /><span class="font-bold text-xs">Abathur</span></div>
                  <span class="rounded-control bg-surface-1 py-0.5 text-center text-gold">{{ $t('Patreon Subscriber') }}</span>
                  <div class="h-px bg-surface-4"></div>
                  <div class="flex justify-between"><span class="text-subtext">{{ $t('Win Rate') }}</span><span>51.2%</span></div>
                  <div class="flex justify-between"><span class="text-subtext">{{ $t('Games') }}</span><span>10,432</span></div>
                </div>
              </div>

              <div v-else-if="card.key === 'mobile'" :class="['h-full flex items-center justify-center', after ? 'bg-page' : 'bg-black']">
                <div class="w-20 h-36 rounded border-2 border-surface-4 overflow-hidden flex flex-col gap-1 p-1">
                  <template v-if="!after">
                    <div class="bg-teal h-3"></div>
                    <div class="flex-1 bg-[#ddd] overflow-hidden"><div class="w-[200%] h-full bg-[repeating-linear-gradient(90deg,#ddd_0,#ddd_0.75rem,#cbcbcb_0.75rem,#cbcbcb_0.8rem)]"></div></div>
                  </template>
                  <template v-else>
                    <div class="h-3 rounded-sm bg-surface"></div>
                    <div v-for="card in 3" :key="card" class="flex-1 rounded-sm bg-surface flex items-center gap-1 px-1">
                      <span class="w-3 h-3 rounded-sm bg-surface-3"></span><span class="flex-1 h-1 rounded bg-surface-3"></span>
                    </div>
                  </template>
                </div>
              </div>
            </div>
            <p class="text-sm text-subtext">{{ $t(card.text) }}</p>
          </div>
        </div>
      </section>

      <!-- Page by page; Before is the old site's one layout (a teal banner over a light table) -->
      <section class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h2 class="text-2xl font-bold">{{ $t('Page by page') }}</h2>
            <p class="text-sm text-subtext mt-1">{{ $t('Most pages used to share one layout: a banner over a table. Now each has a layout of its own.') }}</p>
          </div>
          <tab-button tab1text="Before" tab2text="After" :overridedefaultside="after ? 'right' : 'left'" @tab-click="(side) => after = side === 'right'"></tab-button>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
          <div v-for="page in pageCards" :key="page.key" class="rounded bg-surface px-5 py-4 flex flex-col gap-3">
            <h3 class="text-lg font-bold">{{ $t(page.title) }}</h3>
            <div class="h-32 rounded-control overflow-hidden select-none" aria-hidden="true">
              <div v-if="!after" class="h-full bg-black flex flex-col">
                <div class="bg-teal h-6"></div>
                <div class="flex-1 m-2 bg-[#ddd] flex flex-col">
                  <div class="h-3 bg-teal"></div>
                  <div v-for="row in 5" :key="row" :class="['flex-1', row % 2 ? 'bg-[#ddd]' : 'bg-[#eee]']"></div>
                </div>
              </div>
              <!-- Each page's own shape, in blocks -->
              <div v-else class="h-full bg-page p-2 grid gap-1.5" :style="{ gridTemplateAreas: page.layout.areas, gridTemplateColumns: page.layout.columns }">
                <span v-for="block in page.layout.blocks" :key="block.area" :class="['rounded-sm', block.class || 'bg-surface']" :style="{ gridArea: block.area }"></span>
              </div>
            </div>
            <p class="text-sm text-subtext flex-1">{{ $t(page.text) }}</p>
            <a v-if="page.href" :href="page.href" target="_blank" rel="noopener" class="group/bt inline-flex items-center w-fit text-teal text-sm"><span class="group-hover/bt:underline">{{ $t('Open the page') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
          </div>
        </div>
      </section>

      <!-- Credits -->
      <panel-box :divider="false">
        <h2 class="text-2xl font-bold">{{ $t('Thanks') }}</h2>
        <!-- The name copies the designer's Discord username, as in the footer credit; the sentence is split around it so
             the name can sit anywhere in a translation -->
        <p class="max-w-[60rem]">
          <template v-for="(part, index) in thanksParts" :key="index">
            <button v-if="part === null" type="button" class="inline-flex items-center gap-1.5 font-semibold text-white hover:underline" :title="copiedDiscord ? $t('Copied') : $t('Copy Discord username')" @click="copyDiscord">Bluray<i :class="copiedDiscord ? 'fas fa-check' : 'fab fa-discord'"></i></button>
            <template v-else>{{ part }}</template>
          </template>
        </p>
        <a href="/Team" class="group/bt inline-flex items-center w-fit text-teal"><span class="group-hover/bt:underline">{{ $t('Meet the team') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
      </panel-box>
    </div>
  </div>
</template>

<script>
// Grid areas for each page's After sketch: blocks named a-f placed on a small grid
const layout = (areas, columns, blocks) => ({ areas, columns, blocks });

export default {
  name: 'RedesignPage',
  props: {
    // Every site language with a few strings in it, from RedesignController
    languages: { type: Array, default: () => [] },
    locale: { type: String, default: 'en' },
    // Bluray's Discord username, from config/team.php
    discord: { type: String, default: null },
  },
  data() {
    return {
      copiedDiscord: false,
      after: true,
      // Side by side: the page showing, and how far across the old screenshot reaches
      screenshot: 'home',
      wipe: 50,
      // The nav replica's open menu on phones, where there's no hover
      openMenu: null,
      // The site nav's menus, in its order; each key has a before and after shot in public/images/redesign/2026
      // (design-review/redesign-shots.mjs). Pages that need a hero show Abathur, player pages Zemill
      navMenus: [
        { label: 'Global Hero Stats', groups: [{ items: [
          { key: 'herostats', label: 'Hero Stats' },
          { label: 'Talent Stats', flyout: [{ key: 'talentsall', label: 'All Heroes' }, { key: 'talents', name: 'Abathur' }] },
          { label: 'Map Stats', flyout: [{ key: 'heromapsall', label: 'All Heroes' }, { key: 'heromaps', name: 'Abathur' }] },
          { label: 'Matchup Stats', flyout: [{ key: 'matchupsall', label: 'All Heroes' }, { key: 'matchups', name: 'Abathur' }] },
          { key: 'talentmatchups', label: 'Matchup Talent Stats' },
          { key: 'compositions', label: 'Compositional Stats' },
          { label: 'Draft Stats', flyout: [{ key: 'draftall', label: 'All Heroes' }, { key: 'draft', name: 'Abathur' }] },
          { key: 'party', label: 'Party Stats' },
        ] }] },
        { key: 'leaderboards', label: 'Leaderboards' },
        { label: 'Tools', groups: [
          { label: 'Tools', items: [
            { key: 'drafter', label: 'Drafter' },
            { key: 'search', label: 'Replay Search' },
            { key: 'upload', label: 'Replay Uploader' },
            { key: 'builder', label: 'Talent Builder' },
          ] },
          { label: 'Info', items: [
            { key: 'activity', label: 'Activity Graphs' },
            { key: 'heroes', label: 'Heroes' },
            { key: 'twitch', label: 'Twitch Streamers (Beta)' },
          ] },
          { label: 'Fun', items: [
            { key: 'autobattler', label: 'Auto Battler' },
            { key: 'prediction', label: 'Match Prediction Game' },
            { key: 'randomize', label: 'Randomize Me' },
          ] },
        ] },
        { key: 'esports', label: 'Community Esports' },
        { label: 'Help', groups: [{ items: [
          { key: 'changelog', label: 'Change Log' },
          { key: 'faq', label: 'FAQ' },
          { key: 'contact', label: 'Contact Us' },
        ] }] },
        { key: 'api', label: 'API' },
        { label: 'Zemill', groups: [{ items: [
          { key: 'profile', label: 'Profile' },
          { key: 'friendfoe', label: 'Friends and Foes' },
          { key: 'playerparty', label: 'Party' },
          { label: 'Heroes', flyout: [{ key: 'playerheroes', label: 'All Heroes' }, { key: 'playerhero', name: 'Abathur' }] },
          { label: 'Roles', flyout: [
            { key: 'playerroles', label: 'All Roles' },
            { key: 'rolebruiser', label: 'Bruiser' },
            { key: 'rolehealer', label: 'Healer' },
            { key: 'rolemelee', label: 'Melee Assassin' },
            { key: 'roleranged', label: 'Ranged Assassin' },
            { key: 'rolesupport', label: 'Support' },
            { key: 'roletank', label: 'Tank' },
          ] },
          { label: 'Maps', flyout: [{ key: 'playermaps', label: 'All Maps' }, { key: 'playermap', name: 'Cursed Hollow' }] },
          { key: 'playermatchups', label: 'Matchups' },
          { label: 'Talents', flyout: [{ key: 'playertalentsall', label: 'All Talents' }, { key: 'playertalents', name: 'Abathur' }] },
          { key: 'mmr', label: 'HP MMR Breakdown' },
          { key: 'awards', label: 'Awards' },
          { key: 'history', label: 'Match History' },
          { key: 'latest', label: 'Latest Match' },
        ] }] },
        // Pages the nav doesn't link to directly
        { label: 'Other', wide: true, groups: [
          { label: 'Pages', items: [
            { key: 'prematch', label: 'Prematch' },
            { key: 'heropage', name: 'Abathur' },
            { key: 'settings', label: 'Settings' },
            { key: 'team', label: 'Team' },
          ] },
          { label: 'NGS', items: [
            { key: 'ngs', label: 'Overview' },
            { key: 'ngsstandings', label: 'Standings' },
            { key: 'ngsdivisions', label: 'Divisions' },
            { key: 'ngsteams', label: 'Teams' },
            { key: 'ngsplayersearch', label: 'Player Search' },
            { key: 'ngsrecent', label: 'Recent Matches' },
            { key: 'ngsherostats', label: 'Overall Hero Stats' },
            { key: 'ngstalentstats', label: 'Overall Talent Stats' },
            { key: 'ngsplayer', label: 'Player menu' },
          ] },
          { label: 'Esports', items: [
            { key: 'ccl', name: 'CCL' },
            { key: 'mastersclash', name: 'Masters Clash' },
            { key: 'nutcup', name: 'Nut Cup' },
            { key: 'heroesinternational', name: 'Heroes International' },
            { key: 'esportsother', label: 'Other Esports' },
          ] },
          { label: 'Sign in', items: [
            { key: 'login', label: 'Login' },
            { key: 'loginfailed', label: 'Battle.net login failed' },
            { key: 'patreonfailed', label: 'Patreon login failed' },
          ] },
          { label: 'Errors', items: [
            { key: 'error404', name: '404' },
            { key: 'error403', name: '403' },
            { key: 'error429', name: '429' },
            { key: 'error500', name: '500' },
          ] },
          { label: 'Legal & help', items: [
            { key: 'privacy', label: 'Privacy Policy' },
            { key: 'terms', label: 'Terms of Service' },
            { key: 'guidelines', label: 'Streamer Code of Conduct' },
          ] },
          { label: 'API', items: [
            { key: 'apilogin', label: 'Sign in' },
            { key: 'apiregister', label: 'Register' },
            { key: 'apidocs', label: 'Docs' },
          ] },
        ] },
      ],
      // Pages with no old screenshot: Team is new, and the old site has no way to show its 403, 429 and 500 pages
      afterOnly: ['team', 'error403', 'error429', 'error500'],
      numbers: [
        { title: 'Commits', value: '260+' },
        { title: 'Files changed', value: '670+' },
        { title: 'Pages redesigned', value: '98' },
        { title: 'Lines of code added or modified', value: '27,000+' },
        { title: 'Languages', value: this.languages.length },
        { title: 'Started', value: new Date(2026, 9, 1).toLocaleDateString(this.locale, { month: 'long', year: 'numeric' }) },
      ],
      palette: {
        before: ['#0F121D', '#333333', '#DDDDDD', '#008B8B', '#213D7A'],
        after: ['#141725', '#23252F', '#303340', '#008B8B', '#8CD9B8', '#E5461D'],
      },
      sampleRows: [
        { hero: 'Abathur', winRate: '51.2%', games: '10,432' },
        { hero: 'Alarak', winRate: '49.8%', games: '8,915' },
        { hero: 'Anub\'arak', winRate: '52.6%', games: '12,004' },
      ],
      lookCards: [
        { key: 'palette', title: 'Colours', text: 'A black page and flat greys became layered dark surfaces with teal accents, all from one palette. The site is dark only now.' },
        { key: 'nav', title: 'Navigation', text: 'A slimmer nav with grouped menus, search built in and a language picker.' },
        { key: 'headings', title: 'Page headings', text: 'The teal banner became a heading with the page\'s filters beside it.' },
        { key: 'tables', title: 'Tables', text: 'Light tables with cell borders became dark cards, with the sorted column highlighted and headers that stay in view.' },
        { key: 'filters', title: 'Filters', text: 'Grey filters, a clear button on multi-selects, and advanced filters remembered across pages.' },
        { key: 'loading', title: 'Loading', text: 'Placeholders in the shape of the page replace the full-page spinner, so nothing jumps when the data arrives.' },
        { key: 'popups', title: 'Popups', text: 'Hover cards for heroes, players and talents, one at a time, closing as soon as you move away.' },
        { key: 'mobile', title: 'Phones', text: 'Every page was reworked for phones: cards in place of wide tables, columns that snap and full-screen menus.' },
      ],
      pageCards: [
        { key: 'profile', title: 'Player profile', text: 'Rank, recent matches, best heroes and maps, awards and week day win rates, each in its own card.',
          layout: layout('"a a a" "b c c" "b d d"', '1fr 1fr 1fr', [{ area: 'a' }, { area: 'b' }, { area: 'c', class: 'bg-surface-1' }, { area: 'd' }]) },
        { key: 'match', title: 'Single match', text: 'Team cards, Match Scores sorted by HP Rating, Draft Order, talents and each player\'s MMR change, map MMR included.',
          layout: layout('"a b" "c c" "d d"', '1fr 1fr', [{ area: 'a', class: 'bg-teal/60' }, { area: 'b', class: 'bg-loss/60' }, { area: 'c' }, { area: 'd' }]) },
        { key: 'tierlist', title: 'Hero Stats', text: 'The tier list, a chart view, and the patch notes for the patch you picked.', href: '/Global/Hero',
          layout: layout('"a a" "b c" "b c"', '1fr 2fr', [{ area: 'a', class: 'bg-surface-1' }, { area: 'b' }, { area: 'c' }]) },
        { key: 'builder', title: 'Talent Builder', text: 'Pick talents tier by tier with their win rates, then find replays that used the build.', href: '/Global/Talents/Builder',
          layout: layout('"a a a a" "b b c c" "b b c c"', 'repeat(4, 1fr)', [{ area: 'a', class: 'bg-surface-1' }, { area: 'b' }, { area: 'c', class: 'bg-teal/40' }]) },
        { key: 'heroes', title: 'Hero pages', text: 'Every hero\'s abilities and talents with a level slider, stats and lore.', href: '/Heroes',
          layout: layout('"a a b" "c c b" "c c b"', '1fr 1fr 1fr', [{ area: 'a', class: 'bg-surface-1' }, { area: 'b', class: 'bg-surface-3' }, { area: 'c' }]) },
        { key: 'leaderboards', title: 'Leaderboards', text: 'Leaderboards by map as well as by hero and role, and a card with your own HP Rating.', href: '/Global/Leaderboard',
          layout: layout('"a" "b" "b"', '1fr', [{ area: 'a', class: 'bg-surface-1' }, { area: 'b' }]) },
        { key: 'esports', title: 'Esports', text: 'Every league now has the player, team and match pages NGS has, with standings and recent matches.', href: '/Esports',
          layout: layout('"a a a" "b c d" "b c d"', '1fr 1fr 1fr', [{ area: 'a', class: 'bg-surface-1' }, { area: 'b' }, { area: 'c' }, { area: 'd' }]) },
        { key: 'tools', title: 'Tools', text: 'The Drafter, Randomize Me, the Match Prediction Game and the Auto Battler, all redesigned.', href: '/Drafter',
          layout: layout('"a b c" "d d d" "d d d"', '1fr 1fr 1fr', [{ area: 'a', class: 'bg-teal/40' }, { area: 'b', class: 'bg-surface-1' }, { area: 'c', class: 'bg-loss/40' }, { area: 'd' }]) },
      ],
    };
  },
  computed: {
    // The thanks sentence around the name; null is where the name goes
    thanksParts() {
      const marker = '\u0000';
      const [before, after = ''] = this.$t('Thanks to :name for the updated design.', { name: marker }).split(marker);
      return [before, null, after];
    },
  },
  methods: {
    async copyDiscord() {
      if (!this.discord) {
        return;
      }
      try {
        await navigator.clipboard.writeText(this.discord);
      } catch (error) {
        // The browser refused the clipboard
        return;
      }
      this.copiedDiscord = true;
      clearTimeout(this.copiedTimer);
      this.copiedTimer = setTimeout(() => { this.copiedDiscord = false; }, 2000);
    },
    pickScreenshot(key) {
      this.screenshot = key;
      this.openMenu = null;
    },
    menuHas(menu, key) {
      return menu.groups.some((group) => group.items.some((item) => item.key === key || (item.flyout || []).some((entry) => entry.key === key)));
    },
  },
};
</script>
