<template>
  <!-- Laid out like the match history's rows, with the two teams where a player's stats go.
       Compact and Expanded share the match history's saved choice. -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8 py-4 flex flex-col gap-4">
    <div v-if="!alwaysExpanded" class="flex justify-end">
      <tab-button :tab1text="'Compact'" :tab2text="'Expanded'" :ignoreclick="true" @tab-click="setStyle" :overridedefaultside="expanded ? 'right' : 'left'"></tab-button>
    </div>

    <panel-box v-if="expanded">
      <div class="flex justify-between items-center pb-4 mb-4 border-b border-gray-dark font-medium text-subtext max-2xl:hidden">
        <span class="flex-1 max-w-[25.625rem] pl-6">{{ $t('Match') }}</span>
        <span class="flex-1 px-4 text-center">{{ $t('Teams') }}</span>
        <span class="w-[22.625rem] shrink-0 px-4">{{ $t('Players') }}</span>
      </div>

      <div v-for="row in data" :key="row.replayID" class="group/replay relative mb-1.5">
        <div class="group/row relative flex flex-wrap items-center justify-between gap-y-4 rounded overflow-hidden 2xl:flex-nowrap group-odd/replay:bg-surface-1 group-even/replay:bg-surface-2">
          <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
          <!-- The whole row opens the match; the match ID and player links sit on top of it -->
          <a :href="matchUrl(row)" class="absolute inset-0 z-[1]" :aria-label="$t('Open match')"></a>

          <div class="relative flex-1 md:max-w-[25.625rem] min-w-[18.75rem] self-stretch flex items-center max-md:justify-center pl-6 py-4">
            <div class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="mapArt(row)"></div>
            <div class="absolute inset-y-0 left-0 w-1.5 bg-teal"></div>
            <div class="relative flex flex-col gap-1.5 whitespace-nowrap">
              <span class="font-bold">{{ row.game_map ? row.game_map.name : '' }}</span>
              <span class="text-base font-medium text-header">{{ $t('Game :game · Round :round', { game: row.game, round: row.round }) }}</span>
              <span v-if="rowContext(row)" class="text-xs text-subtext">{{ rowContext(row) }}</span>
              <span class="text-xs">{{ formatRowDate(row.game_date) }}</span>
              <span class="relative z-[2] text-sm">
                {{ $t(':leaguename Match ID', { leaguename: leagueName }) }}
                <a class="group/bt inline-flex items-center" :href="matchUrl(row)"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
              </span>
            </div>
          </div>

          <!-- Head to head: each team either side of the series score after this game; this game's winner marked -->
          <div class="relative flex-1 grid grid-cols-[1fr_auto_1fr] items-center gap-6 px-4 py-4 min-w-0">
            <div v-for="(team, position) in sidesOf(row)" :key="team" :class="['flex items-center gap-3 min-w-0 row-start-1', position === 0 ? 'flex-row-reverse text-right col-start-1' : 'col-start-3']">
              <img v-if="teamImage(row, team)" class="w-12 h-12 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="teamImage(row, team)" :alt="teamName(row, team)"/>
              <div :class="['flex flex-col gap-1 min-w-0', position === 0 ? 'items-end' : 'items-start']">
                <span :class="['text-xl font-semibold leading-tight break-words', row.winner === team ? 'text-white' : 'text-subtext']">{{ teamName(row, team) }}</span>
                <span v-if="row.winner === team" class="bg-win text-white rounded px-1.5 text-xs font-semibold">{{ $t('Won') }}</span>
              </div>
            </div>
            <div class="col-start-2 row-start-1 flex flex-col items-center">
              <span class="text-3xl font-semibold tabular-nums whitespace-nowrap">{{ seriesScore(row, sidesOf(row)[0]) }} – {{ seriesScore(row, sidesOf(row)[1]) }}</span>
              <span class="text-xs text-subtext whitespace-nowrap">{{ $t('Series after game :game', { game: row.game }) }}</span>
            </div>
          </div>

          <!-- Each team as one party, drawn like the match history's players column -->
          <div class="relative z-[2] flex gap-2 max-md:justify-center pl-4 2xl:pl-1.5 pr-4 py-4 w-full 2xl:w-[22.625rem] shrink-0">
            <div v-for="(team, position) in sidesOf(row)" :key="team" class="flex flex-col gap-1.5 w-[9.375rem]">
              <div class="flex items-center">
                <span class="w-4 shrink-0 flex items-center justify-center">
                  <img class="w-4 h-4" :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${TEAM_PARTY[position]}.png`" alt=""/>
                </span>
                <span class="w-[2px] ml-[0.1875rem] self-stretch shrink-0 rounded-l-full" :style="{ backgroundColor: PARTY_COLORS[TEAM_PARTY[position]] }"></span>
                <div class="flex flex-col gap-1.5 flex-1 min-w-0 ml-0.5">
                  <div v-for="(player, playerIndex) in teamPlayers(row, team)" :key="playerIndex" class="flex gap-1 h-4 items-center">
                    <img v-if="player.hero" class="w-4 h-4 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${player.hero.short_name}.png`" :alt="player.hero.name" :title="player.hero.name"/>
                    <a v-if="player.battletag" :href="playerUrl(row, player)" class="group/bt min-w-0 inline-flex items-center text-sm font-medium text-subtext hover:text-lteal"><span class="truncate group-hover/bt:underline">{{ player.battletag }}</span><i class="fas fa-chevron-right text-[0.625rem] ml-1 shrink-0 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                    <span v-else class="min-w-0 truncate text-sm font-medium text-subtext">{{ player.hero ? player.hero.name : '' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </panel-box>

    <!-- One line per game, like the match history's compact rows -->
    <panel-box v-else>
      <div class="overflow-x-auto">
        <div class="md:min-w-max w-full">
          <div class="max-md:hidden flex items-center justify-between pb-4 mb-4 border-b border-gray-dark font-medium text-subtext">
            <span class="w-[20rem] shrink-0 pl-5">{{ $t('Match') }}</span>
            <span class="w-[11.25rem] shrink-0 px-4">{{ $t('Game') }}</span>
            <span class="w-[10rem] shrink-0 px-4">{{ $t(':leaguename Match ID', { leaguename: leagueName }) }}</span>
            <span class="w-[35rem] shrink-0 px-4">{{ $t('Teams') }}</span>
            <span class="w-[15rem] shrink-0 px-4">{{ $t('Series') }}</span>
          </div>
          <template v-for="(row, index) in data" :key="row.replayID">
            <match-row-mobile class="md:hidden" :bg="index % 2 ? 'bg-surface-2' : 'bg-surface-1'" :href="matchUrl(row)" :title="row.game_map ? row.game_map.name : ''" :date="formatRowDate(row.game_date)" :map-art="row.game_map ? row.game_map.sanitized_map_name : null">
              {{ teamName(row, sidesOf(row)[0]) }} <span class="text-white font-medium">{{ seriesScore(row, sidesOf(row)[0]) }} – {{ seriesScore(row, sidesOf(row)[1]) }}</span> {{ $t(':teamname · Game :game · Round :round', { teamname: teamName(row, sidesOf(row)[1]), game: row.game, round: row.round }) }}
            </match-row-mobile>
<div :class="['max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden mb-1', index % 2 ? 'bg-surface-2' : 'bg-surface-1']">
            <div class="absolute inset-0 pointer-events-none group-hover/row:bg-white/10"></div>
            <a :href="matchUrl(row)" class="absolute inset-0 z-[1]" :aria-label="$t('Open match')"></a>
            <div class="relative w-[20rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
              <div class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="mapArt(row)"></div>
              <div class="absolute inset-y-0 left-0 w-1.5 bg-teal"></div>
              <div class="relative flex flex-col whitespace-nowrap">
                <span class="font-bold">{{ row.game_map ? row.game_map.name : '' }}</span>
                <span class="text-xs">{{ formatRowDate(row.game_date) }}</span>
                <span v-if="rowContext(row)" class="text-xs text-subtext">{{ rowContext(row) }}</span>
              </div>
            </div>
            <span class="relative w-[11.25rem] shrink-0 px-4 font-medium text-header">{{ $t('Game :game · Round :round', { game: row.game, round: row.round }) }}</span>
            <span class="relative z-[2] w-[10rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="matchUrl(row)"><span class="group-hover/bt:underline">{{ row.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></span>
            <div class="relative w-[35rem] shrink-0 px-4 flex items-center gap-4">
              <template v-for="(team, position) in sidesOf(row)" :key="team">
                <span v-if="position === 1" class="text-subtext text-sm">{{ $t('vs') }}</span>
                <span class="flex items-center gap-2 min-w-0">
                  <img v-if="teamImage(row, team)" class="w-6 h-6 shrink-0 rounded-full object-contain bg-surface-3 text-[0rem]" :src="teamImage(row, team)" :alt="teamName(row, team)"/>
                  <span :class="['truncate font-medium', row.winner === team ? 'text-white' : 'text-subtext']">{{ teamName(row, team) }}</span>
                  <span v-if="row.winner === team" class="bg-win text-white rounded px-1 text-xs font-semibold">{{ $t('Won') }}</span>
                </span>
              </template>
            </div>
            <span class="relative w-[15rem] shrink-0 px-4 text-sm tabular-nums">{{ seriesScore(row, sidesOf(row)[0]) }} – {{ seriesScore(row, sidesOf(row)[1]) }} <span class="text-subtext">{{ $t('after game :game', { game: row.game }) }}</span></span>
          </div>
</template>
        </div>
      </div>
    </panel-box>
  </div>
</template>

<script>
import { siteLocale } from '../../utils/translate';
import moment from 'moment-timezone';
import Cookies from 'js-cookie';

const LEAGUE_NAMES = { NGS: 'NGS', CCL: 'CCL', MastersClash: 'Masters Clash', HeroesInternational: 'Heroes International', hi: 'Heroes International', hi_nc: 'Heroes International' };
// Each team is one party: the first party colour on each side, as the match history uses
const TEAM_PARTY = ['blue', 'red'];
const PARTY_COLORS = { blue: '#2060ae', red: '#ae2023' };

export default {
  name: 'EsportsRecentMatches',
  props: {
    data: Array,
    esport: String,
    // Other's series, for its links and name
    series: String,
    tournament: String,
    // A short list on another page: always Expanded, without the toggle
    alwaysExpanded: Boolean,
  },
  data() {
    return {
      TEAM_PARTY,
      PARTY_COLORS,
      // The match history's saved layout ('1' is Expanded there too)
      expanded: this.alwaysExpanded || Cookies.get('playerhistorytable') !== '0',
    };
  },
  computed: {
    // Each team's series wins after each game, by team name, worked out from the games on
    // this page. A series is the games of one round between the same two teams.
    seriesScores() {
      const groups = {};
      for (const row of this.data || []) {
        const key = [row.series, row.tournament, row.round, ...[this.teamName(row, 0), this.teamName(row, 1)].sort()].join('|');
        (groups[key] ??= []).push(row);
      }

      const after = {};
      const left = {};
      for (const games of Object.values(groups)) {
        games.sort((a, b) => a.game - b.game);
        // The team on the left in the series' first game stays on the left for every game
        const leftName = this.teamName(games[0], 0);
        for (const game of games) {
          left[game.replayID] = this.teamName(game, 0) === leftName ? [0, 1] : [1, 0];
        }
        const wins = {};
        for (const game of games) {
          if (game.winner === 0 || game.winner === 1) {
            const winnerName = this.teamName(game, game.winner);
            wins[winnerName] = (wins[winnerName] ?? 0) + 1;
          }
          after[game.replayID] = { ...wins };
        }
      }
      return { after, left };
    },
    leagueName() {
      return this.esport === 'Other' ? (this.series ?? '') : (LEAGUE_NAMES[this.esport] ?? this.esport);
    },
  },
  methods: {
    // Saved the way the match history saves it, so the choice follows between the two
    setStyle(side) {
      this.expanded = side === 'right';
      Cookies.set('playerhistorytable', this.expanded ? '1' : '0', { expires: 365, path: '/' });
      this.$axios.post('/api/v1/profile/save/settings', { playerhistorytable: this.expanded }).catch(() => {});
    },
    matchUrl(row) {
      const base = `/Esports/${this.getUrlValue(this.esport, row)}/Match/Single/${row.replayID}`;
      return this.tournament ? `${base}/?tournament=${this.tournament}` : base;
    },
    mapArt(row) {
      return row.game_map ? { backgroundImage: `url('/images/maps/match/match-${row.game_map.sanitized_map_name}.jpg')` } : null;
    },
    seriesScore(row, team) {
      return this.seriesScores.after[row.replayID]?.[this.teamName(row, team)] ?? 0;
    },
    // [left side, right side] for this row, the same way round for every game of its series
    sidesOf(row) {
      return this.seriesScores.left[row.replayID] ?? [0, 1];
    },
    teamName(row, team) {
      if (row.side_names && row.side_names[team]) {
        return row.side_names[team];
      }
      return team === 0 ? (row.team_0_name ?? row.team_0_id) : (row.team_1_name ?? row.team_1_id);
    },
    teamImage(row, team) {
      return row.team_images ? row.team_images[team] : null;
    },
    teamPlayers(row, team) {
      return row.teams ? row.teams[team] : [];
    },
    playerUrl(row, player) {
      return `/Esports/${this.getUrlValue(this.esport, row)}/Player/${player.battletag}/${player.blizz_id}`;
    },
    // A list mixing Other's series says which one each game is from
    rowContext(row) {
      return [row.series, row.tournament, row.region_name].filter(Boolean).join(' · ');
    },
    // In the viewer's own date format and timezone, like the match history rows
    formatRowDate(dateString) {
      return moment.utc(dateString).toDate().toLocaleString(siteLocale(), {
        dateStyle: 'long',
        timeStyle: 'short',
        timeZone: moment.tz.guess(),
      });
    },
    getUrlValue(esport, row){
      if(esport == "hi" || esport == "hi_nc"){
        return "HeroesInternational";
      }
      if(esport == "Other"){
        return `Other/${row?.series ?? this.series}`;
      }

      return esport;
    },
  },
}
</script>
