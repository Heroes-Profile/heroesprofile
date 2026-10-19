<template>
  <div>
    <!-- The answers first -->
    <div class="flex flex-wrap justify-center gap-4 mb-8">
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Orders/Min'" :value="orDash(round(ordersPerMinute('you'), 1))" :secondstat="orDash(round(ordersPerMinute('global'), 1))" :secondcaption="'global'" color="teal"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Lvl 10 Talent'" :value="clock(talentRow('you', 3).avg_seconds)" :secondstat="clock(talentRow('global', 3).avg_seconds)" :secondcaption="'global'" color="yellow"></stat-box>
      <stat-box class="w-[30%] max-md:w-[48%]" :title="'Lvl 20 Talent'" :value="clock(talentRow('you', 6).avg_seconds)" :secondstat="clock(talentRow('global', 6).avg_seconds)" :secondcaption="'global'" color="teal"></stat-box>
    </div>

    <analytics-heading :title="'Activity over the game'" :takeaway="apmTakeaway">
      Everything on this tab comes from the orders saved in your replays: each ability key press, attack order and talent pick, with the game time it happened. Replays don't record what happened on screen, such as each auto-attack or whether an ability hit. Global is a sample of other players' games under the same filters.
      <br /><br />
      This chart is the average number of orders given in each game minute, over the games still running at that minute. An order is anything with an ability attached: ability key presses, attack, stop, mount and hearth. Plain move clicks and other mouse movement are not counted. Late minutes come from only a few long games, so they jump around more.
    </analytics-heading>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      :labels="minuteLabels"
      :yLabel="'Orders per minute'"
      :series="apmSeries"
    ></multi-series-chart>

    <analytics-heading :title="'When you pick talents'" :takeaway="talentTakeaway">
      Average game time when the talent for each tier was chosen. That's when you clicked the talent, which can be later than when your team reached the level. Choose a hero in the filters to compare like for like.
    </analytics-heading>
    <multi-series-chart
      class="max-w-[1200px] mx-auto mb-8"
      type="bar"
      format="mmss"
      :labels="tierLabels"
      :yLabel="'Game time'"
      :series="talentSeries"
    ></multi-series-chart>

    <analytics-heading :title="'Ability usage'" :takeaway="abilityTakeaway">
      Presses count every time you pressed an ability's key or gave that order. Casts estimate how many times you actually used it: a press within 2 seconds of the previous press is the same cast, and for single-charge abilities with a known cooldown, a press before 60% of the base cooldown has passed isn't a new cast. Attack shows the difference best: you give hundreds of attack orders a game, but casts roughly count separate bursts of attacking, since the auto-attacks themselves aren't in replays. Per game figures average over all your games on that hero, including games where you never pressed it.
    </analytics-heading>
    <p v-if="!abilityHeroes.length" class="text-center text-gray-light">No ability data for these filters yet.</p>
    <div v-for="hero in abilityHeroes" :key="hero.id" class="max-w-[1200px] mx-auto mb-6">
      <h3 class="text-lg font-bold">{{ heroName(hero.id) }} <span class="text-sm text-gray-light">({{ hero.games }} games)</span></h3>
      <p v-if="hero.takeaway" class="text-base mb-2">{{ hero.takeaway }}</p>
      <multi-series-chart type="bar" :horizontal="true" :labels="hero.chart.labels" :series="hero.chart.series"></multi-series-chart>
      <analytics-numbers :label="'Show every ability; click one for its usage over the game'">
        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th>Ability</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Presses per game
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Every key press or order for this ability, averaged over all your games on this hero. Pressing the key again while it's on cooldown still adds a press.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Casts per game
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Presses grouped into estimated uses: repeats within 2 seconds count once, and presses while a single-charge ability is likely still on cooldown don't count. Abilities whose cooldown gets reduced or reset can be undercounted.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Global presses
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">The global columns are the same figures from a sample of other players' games on this hero, under the same filters.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>Global casts</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  Games used in
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Share of your games on this hero where you pressed this ability at least once.</p></slot>
                  </round-image>
                </span>
              </th>
              <th>Global games used in</th>
              <th>
                <span class="inline-flex items-center gap-1">
                  First use
                  <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                    <slot><p class="text-sm p-2 text-left">Average game time of your first press, over the games where you used it.</p></slot>
                  </round-image>
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="row in hero.abilities" :key="row.ability_id">
              <tr class="cursor-pointer" :class="{ 'font-bold': isSelected(hero.id, row) }" @click="selectAbility(hero.id, row)">
                <td>
                  <div class="flex items-center gap-2">
                    <img v-if="row.icon" :src="`/images/talents/${row.icon}`" :alt="row.name" class="w-8 h-8" />
                    <span>{{ row.name }} <span class="text-sm text-gray-light">{{ slotLabel(row.slot) }}</span></span>
                  </div>
                </td>
                <td>{{ perGame(row.you) }}</td>
                <td>{{ castsPerGame(row.you) }}</td>
                <td>{{ perGame(row.global) }}</td>
                <td>{{ castsPerGame(row.global) }}</td>
                <td>{{ row.you ? share(row.you.games_used, row.you.games) : '-' }}</td>
                <td>{{ row.global ? share(row.global.games_used, row.global.games) : '-' }}</td>
                <td>{{ row.you ? clock(row.you.avg_first_use) : '-' }}</td>
              </tr>
              <!-- The clicked ability's graph opens right under it; clicking the row again closes it -->
              <tr v-if="isSelected(hero.id, row)">
                <td colspan="8">
                  <loading-component v-if="timelineLoading"></loading-component>
                  <p v-else-if="timelineError" class="text-center text-gray-light">{{ timelineError }}</p>
                  <p v-else-if="timeline && !timelineHasData" class="text-center text-gray-light">Per-minute data for this ability is still being filled in for your games. Check back soon.</p>
                  <template v-else-if="timeline">
                    <multi-series-chart
                      :labels="timelineMinutes.map(m => m + ' min')"
                      :yLabel="'Per game, in that minute'"
                      :y2Label="'Deaths / takedowns per game, in that minute'"
                      :series="timelineSeries"
                    ></multi-series-chart>
                    <div class="text-center text-sm text-gray-light">
                      <span class="inline-flex items-center gap-1">
                        {{ row.name }} over the game. Click a legend item to show or hide it, e.g. your deaths and takedowns.
                        <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
                          <slot><p class="text-sm p-2 text-left">Per game is the average count in that game minute, over your games on this hero still running at that minute; games where you didn't press it count as 0. Your lines stop once fewer than 5 of your games were still running at that minute, since one or two games aren't a fair average. Deaths and takedowns use the right axis.</p></slot>
                        </round-image>
                      </span>
                    </div>
                  </template>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </analytics-numbers>
    </div>
  </div>
</template>

<script>
import analytics from '../../utils/analyticsFormat.js';

// Fewest of your games still running for a minute to be drawn on the per-ability graph
const MIN_TIMELINE_GAMES = 5;

export default {
  name: 'AnalyticsActivity',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
    // The page's current filters as sent to /Analytics/Data
    filterParams: Object,
  },
  data() {
    return {
      tierLabels: ['Level 1', 'Level 4', 'Level 7', 'Level 10', 'Level 13', 'Level 16', 'Level 20'],
      slotOrder: ['Basic', 'Trait', 'Heroic', 'Activable', 'Mount', 'Hearth'],
      maxHeroes: 5,
      selected: null,
      timeline: null,
      timelineLoading: false,
      timelineError: '',
      timelineCache: {},
    };
  },
  computed: {
    minutes() {
      return [...new Set(this.data.apm.map(r => r.minute))].sort((a, b) => a - b);
    },
    minuteLabels() {
      return this.minutes.map(m => m + ' min');
    },
    apmSeries() {
      return [
        { label: 'You', data: this.apm('you'), color: 'teal', n: this.apmGames('you') },
        { label: 'Global', data: this.apm('global'), color: 'yellow', n: this.apmGames('global') },
      ];
    },
    talentSeries() {
      return [
        { label: 'You', data: this.talents('you'), color: 'teal', n: this.talentPicks('you') },
        { label: 'Global', data: this.talents('global'), color: 'yellow', n: this.talentPicks('global') },
      ];
    },
    apmTakeaway() {
      const mine = this.ordersPerMinute('you');
      if (mine === null) return null;
      return 'You give ' + this.round(mine) + ' orders a minute on average' + this.versus(mine, this.ordersPerMinute('global')) + '.';
    },
    talentTakeaway() {
      // Average gap to global over the tiers both have
      const you = this.talents('you');
      const global = this.talents('global');
      const gaps = you.map((v, i) => (v !== null && global[i] !== null ? v - global[i] : null)).filter(v => v !== null);
      if (!gaps.length) return null;
      const gap = gaps.reduce((a, b) => a + b, 0) / gaps.length;
      if (Math.abs(gap) < 3) return 'You pick talents at about the same time as most players.';
      return 'You pick talents ' + this.clock(Math.abs(gap)) + (gap > 0 ? ' later' : ' sooner') + ' than most players, on average.';
    },
    abilityTakeaway() {
      return this.moreHeroes ? 'Your ' + this.abilityHeroes.length + ' most played heroes; choose a hero in the filters to see another.' : null;
    },
    // Your heroes, most played first, each with its abilities paired you / global
    heroesPlayed() {
      return (this.data.hero_games || [])
        .filter(r => r.who === 'you')
        .sort((a, b) => b.games - a.games)
        .map(r => ({ id: r.hero_id, games: r.games }));
    },
    abilityHeroes() {
      const rows = this.data.ability_usage || [];
      return this.heroesPlayed.slice(0, this.maxHeroes).map(hero => {
        const byAbility = {};
        rows.filter(r => r.hero_id === hero.id).forEach(r => {
          byAbility[r.ability_id] = byAbility[r.ability_id] || { ability_id: r.ability_id, name: r.name, slot: r.slot, icon: r.icon };
          byAbility[r.ability_id][r.who] = r;
        });
        const abilities = Object.values(byAbility)
          .filter(a => a.you)
          .sort((a, b) => this.slotRank(a.slot) - this.slotRank(b.slot) || b.you.uses - a.you.uses);
        return { ...hero, abilities, chart: this.abilityChart(abilities), takeaway: this.abilityGap(abilities) };
      }).filter(hero => hero.abilities.length);
    },
    timelineMinutes() {
      return [...new Set((this.timeline || []).map(r => r.minute))].sort((a, b) => a - b);
    },
    // Games loaded before per-minute counts existed have none, which reads as all zeros
    timelineHasData() {
      return (this.timeline || []).some(r => Number(r.presses) > 0);
    },
    timelineSeries() {
      // Your lines stop where fewer than MIN_TIMELINE_GAMES of your games were still running: one or two games aren't an average
      const line = (who, field) => this.timelineMinutes.map(m => {
        const row = this.timeline.find(r => r.who === who && r.minute === m);
        if (!row || row[field] === null || (who === 'you' && Number(row.games) < MIN_TIMELINE_GAMES)) return null;
        return this.round(row[field], 2);
      });
      return [
        { label: 'Your presses', data: line('you', 'presses'), color: 'teal' },
        { label: 'Your casts', data: line('you', 'casts'), color: 'teal', dashed: true },
        { label: 'Global presses', data: line('global', 'presses'), color: 'yellow' },
        { label: 'Global casts', data: line('global', 'casts'), color: 'yellow', dashed: true },
        { label: 'Your deaths', data: line('you', 'deaths'), color: 'red', axis: 'right', hidden: true },
        { label: 'Your takedowns', data: line('you', 'takedowns'), color: 'blue', axis: 'right', hidden: true },
      ];
    },
    moreHeroes() {
      return this.heroesPlayed.length > this.maxHeroes;
    },
  },
  methods: {
    apm(who) {
      return this.minutes.map(m => {
        const row = this.data.apm.find(r => r.who === who && r.minute === m);
        return row ? this.round(row.commands, 1) : null;
      });
    },
    apmGames(who) {
      return this.minutes.map(m => {
        const row = this.data.apm.find(r => r.who === who && r.minute === m);
        return row ? row.games : null;
      });
    },
    // Over every minute played, so long games' late minutes weigh in by how many games reached them
    ordersPerMinute(who) {
      const rows = this.data.apm.filter(r => r.who === who);
      const minutes = rows.reduce((sum, r) => sum + Number(r.games), 0);
      return minutes ? rows.reduce((sum, r) => sum + Number(r.commands) * Number(r.games), 0) / minutes : null;
    },
    talentRow(who, tier) {
      return this.data.talent_timing.find(r => r.who === who && r.tier === tier) || {};
    },
    talentPicks(who) {
      return [0, 1, 2, 3, 4, 5, 6].map(tier => this.talentRow(who, tier).picks ?? null);
    },
    // Top abilities by your presses, presses and casts side by side
    abilityChart(abilities) {
      const top = [...abilities].sort((a, b) => b.you.uses - a.you.uses).slice(0, 4);
      const labels = top.flatMap(a => [a.name + ' presses', a.name + ' casts']);
      const values = who => top.flatMap(a => [this.perGameValue(a[who]), this.castsValue(a[who])]);
      return {
        labels,
        series: [
          { label: 'You', data: values('you'), color: 'teal' },
          { label: 'Global', data: values('global'), color: 'yellow' },
        ],
      };
    },
    // The ability you cast most differently from other players
    abilityGap(abilities) {
      let best = null;
      abilities.forEach(a => {
        const mine = this.castsValue(a.you);
        const global = this.castsValue(a.global);
        if (mine === null || !global) return;
        const ratio = Math.abs(mine - global) / global;
        if (!best || ratio > best.ratio) best = { ratio, name: a.name, mine, global };
      });
      if (!best || best.ratio < 0.15) return best ? 'You use your abilities about as often as most players.' : null;
      return 'You cast ' + best.name + ' ' + (best.mine > best.global ? 'more' : 'less') + ' than most players: ' + best.mine + ' times a game vs ' + best.global + '.';
    },
    slotBase(slot) {
      return (slot || '').replace(' (sub)', '');
    },
    slotRank(slot) {
      const rank = this.slotOrder.indexOf(this.slotBase(slot));
      return rank === -1 ? this.slotOrder.length : rank;
    },
    slotLabel(slot) {
      const base = this.slotBase(slot);
      return base === 'Activable' ? 'Active' : base;
    },
    castsPerGame(row) {
      return this.orDash(this.castsValue(row));
    },
    castsValue(row) {
      // A game loaded just before casts were recorded has none yet; scale from the games that do
      if (!row || !row.games || !row.casts_games) {
        return null;
      }
      return this.round((row.casts * (row.games_used / row.casts_games)) / row.games, 1);
    },
    isSelected(heroId, row) {
      return this.selected && this.selected.hero === heroId && this.selected.ability === row.ability_id;
    },
    async selectAbility(heroId, row) {
      if (this.isSelected(heroId, row)) {
        this.selected = null;
        return;
      }
      this.selected = { hero: heroId, ability: row.ability_id, name: row.name };
      const params = { ...this.filterParams, ability_hero: heroId, ability_id: row.ability_id };
      const key = JSON.stringify(params);
      this.timelineError = '';
      if (this.timelineCache[key]) {
        this.timeline = this.timelineCache[key];
        return;
      }
      this.timeline = null;
      this.timelineLoading = true;
      try {
        const response = await this.$axios.post('/Analytics/Data/ability', params);
        this.timelineCache[key] = response.data.timeline;
        if (this.isSelected(heroId, row)) {
          this.timeline = response.data.timeline;
        }
      } catch (e) {
        this.timelineError = 'Could not load this ability. Please try again in a minute.';
      } finally {
        this.timelineLoading = false;
      }
    },
    perGame(row) {
      return this.orDash(this.perGameValue(row));
    },
    perGameValue(row) {
      return row && row.games ? this.round(row.uses / row.games, 1) : null;
    },
    talents(who) {
      return [0, 1, 2, 3, 4, 5, 6].map(tier => {
        const row = this.talentRow(who, tier);
        return row.avg_seconds !== undefined && row.avg_seconds !== null ? Math.round(row.avg_seconds) : null;
      });
    },
  },
};
</script>
