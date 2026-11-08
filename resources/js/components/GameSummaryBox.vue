<template>
  <div v-if="!esport || data.hero" :class="['group/replay relative', compact ? 'mb-1' : 'mb-1.5']">
  <!-- Phones: the compact row as two short lines -->
  <match-row-mobile v-if="compact" class="md:hidden !mb-0" bg="group-odd/replay:bg-surface-1 group-even/replay:bg-surface-2" :href="getHref()" :hero="data.hero" :title="data.game_map.name" :date="formatRowDate(data.game_date)" :winner="data.winner === 1" :result="data.winner === 1 ? 'Win' : 'Loss'" :map-art="data.game_map.sanitized_map_name">
    <template v-if="esport">Game {{ data.game }} · Round {{ data.round }} · {{ $ngsTeam(data.own_team).name }} vs {{ $ngsTeam(data.opponent_team).name }}</template>
    <template v-else>{{ data.game_type.name }} · <span class="text-white font-medium">{{ data.player_mmr.toLocaleString('en-US') }}</span><span v-if="data.player_change !== null" :class="data.player_change >= 0 ? 'text-win' : 'text-red'"> ({{ data.player_change > 0 ? '+' : '' }}{{ data.player_change.toFixed(2) }})</span><template v-if="data.kills !== undefined"> · {{ data.kills }}/{{ data.deaths }}/{{ data.assists }}</template></template>
  </match-row-mobile>
  <!-- One line per game: the match history's Compact view -->
  <div v-if="compact" class="max-md:hidden group/row relative flex items-center justify-between rounded overflow-hidden group-odd/replay:bg-surface-1 group-even/replay:bg-surface-2">
    <div class="absolute inset-0 pointer-events-none transition-colors duration-200 ease-out group-hover/row:bg-white/10"></div>
    <a :href="getHref()" class="absolute inset-0 z-[1]" aria-label="Open match"></a>
    <div class="relative w-[20rem] shrink-0 self-stretch flex items-center pl-5 py-2.5">
      <div class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="data.winner === 1 ? 'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
      <div class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"></div>
      <div class="absolute inset-y-0 left-0 w-1.5" :class="data.winner === 1 ? 'bg-win' : 'bg-red'"></div>
      <div class="relative flex items-center gap-4">
        <hero-image-wrapper size="row" :hero="data.hero" :excludehover="true"></hero-image-wrapper>
        <div class="flex flex-col whitespace-nowrap">
          <span class="font-bold">{{ data.game_map.name }}</span>
          <span class="text-xs">{{ formatRowDate(data.game_date) }}</span>
        </div>
      </div>
    </div>
    <span class="relative w-[10rem] shrink-0 px-4 font-medium text-header">{{ esport ? `Game ${data.game} · Round ${data.round}` : data.game_type.name }}</span>
    <span class="relative z-[2] w-[8.75rem] shrink-0 px-4"><a class="group/bt inline-flex items-center" :href="getHref()"><span class="group-hover/bt:underline">{{ data.replayID }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></span>
    <!-- Esports games: their team against the other, where a ranked game shows MMR -->
    <div v-if="esport" class="relative w-[16.875rem] shrink-0 px-4 flex flex-col min-w-0">
      <span class="font-semibold truncate">{{ $ngsTeam(data.own_team).name }}</span>
      <span class="text-sm text-subtext truncate">vs {{ $ngsTeam(data.opponent_team).name }}</span>
    </div>
    <div v-else class="relative w-[8.125rem] shrink-0 px-4 flex flex-col">
      <span class="text-2xl font-semibold">{{ data.player_mmr.toLocaleString('en-US') }}</span>
      <span v-if="data.player_change !== null" class="text-sm font-medium" :class="data.player_change >= 0 ? 'text-win' : 'text-red'">{{ data.player_change > 0 ? '+' : '' }}{{ data.player_change.toFixed(2) }}</span>
    </div>
    <!-- Esports rows give this space to the teams -->
    <div v-if="!esport" class="relative w-[8.75rem] shrink-0 flex flex-col items-center text-center">
      <template v-if="data.kills !== undefined">
        <span class="grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] gap-x-[0.4375rem] w-[8.75rem] text-2xl font-semibold whitespace-nowrap tabular-nums">
          <span class="text-right text-win">{{ data.kills }}</span><span>|</span><span class="font-normal text-red">{{ data.deaths }}</span><span>|</span><span class="text-left font-normal">{{ data.assists }}</span>
        </span>
        <span class="text-sm font-medium text-subtext">{{ kda }} KDA</span>
      </template>
    </div>
    <div class="relative z-[2] w-[26.625rem] flex gap-1.5 items-center px-4 pr-20 shrink-0">
      <template v-for="level in talentLevels" :key="level">
        <talent-image-wrapper v-if="data[level]" :talent="data[level]" size="talent"></talent-image-wrapper>
        <div v-else class="w-[2.625rem] h-[2.625rem] shrink-0 rounded bg-surface-3"></div>
      </template>
    </div>
  </div>
  <div v-else :class="['group/row relative flex flex-wrap items-center justify-between gap-y-4 rounded overflow-hidden group-odd/replay:bg-surface-1 group-even/replay:bg-surface-2']">
    <div class="absolute inset-0 pointer-events-none transition-colors duration-200 ease-out group-hover/row:bg-white/10"></div>
    <!-- The whole row opens the match; talents, player names and the replay icons sit on top of it -->
    <a :href="getHref()" class="absolute inset-0 z-[1]" aria-label="Open match"></a>
    <div class="relative flex-1 max-w-[25.625rem] min-w-[18.75rem] self-stretch flex items-center pl-6 py-4">
      <div class="absolute inset-0 [mask-image:linear-gradient(to_right,black_60%,transparent)]" :class="data.winner === 1 ?'bg-[rgba(0,139,139,0.35)]' : 'bg-[rgba(179,54,22,0.13)]'"></div>
      <div class="absolute inset-0 bg-cover bg-center opacity-40 [mask-image:linear-gradient(to_right,black_70%,transparent)]" :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"></div>
      <div class="absolute inset-y-0 left-0 w-1.5" :class="data.winner === 1 ?'bg-win' : 'bg-red'"></div>

      <div class="relative flex items-center gap-4">
        <hero-image-wrapper size="match" :hero="data.hero" :excludehover="true"></hero-image-wrapper>
        <div class="flex flex-col gap-1.5 whitespace-nowrap">
          <span class="font-bold">{{ data.game_map.name }}</span>
          <div class="flex flex-col">
            <span class="text-base font-medium text-header">{{ esport ? `Game ${data.game} · Round ${data.round}` : data.game_type.name }}</span>
            <span v-if="data.game_length" class="text-sm">{{ Math.round(data.game_length / 60) }} minutes</span>
          </div>
          <span class="text-xs">{{ formatRowDate(data.game_date) }}</span>
        </div>
      </div>
    </div>

    <!-- Esports games: their team against the other, where a ranked game shows MMR -->
    <div v-if="esport" class="relative flex flex-col gap-0.5 justify-center px-4 w-[26.875rem] shrink-0">
      <span class="text-base font-medium text-subtext">{{ data.winner === 1 ? 'Won' : 'Lost' }} with</span>
      <span class="text-xl font-semibold truncate">{{ $ngsTeam(data.own_team).name }}</span>
      <span class="text-sm text-subtext truncate">vs {{ $ngsTeam(data.opponent_team).name }}</span>
    </div>
    <div v-else class="relative flex gap-5 items-center px-4 w-[26.875rem] shrink-0">
      <div v-for="stat in mmrStats" :key="stat.title" class="flex flex-col gap-0.5 flex-none w-[7.375rem] min-w-0">
        <span class="text-base font-medium text-subtext whitespace-nowrap">{{ stat.title }}</span>
        <span class="text-2xl font-semibold">{{ stat.value.toLocaleString('en-US') }}</span>
        <span v-if="stat.change !== null" class="text-sm font-medium" :class="stat.change >= 0 ?'text-win' : 'text-red'">{{ stat.change > 0 ? '+' : '' }}{{ stat.change.toFixed(2) }}</span>
      </div>
    </div>

    <div class="relative w-[8.75rem] shrink-0 flex flex-col gap-0.5 items-center text-center">
      <template v-if="data.kills !== undefined">
        <!-- Same grid as the header's icons. Kills right-align against the first bar so their
             ones digits line up row to row; deaths sit centred; assists start right after the second bar. -->
        <span class="grid grid-cols-[1fr_0.75rem_1fr_0.75rem_1fr] gap-x-[0.4375rem] w-[8.75rem] text-2xl font-semibold whitespace-nowrap text-center tabular-nums">
          <span class="text-right text-win">{{ data.kills }}</span><span>|</span><span class="font-normal text-red">{{ data.deaths }}</span><span>|</span><span class="text-left font-normal">{{ data.assists }}</span>
        </span>
        <span class="text-sm font-medium text-subtext">{{ kda }} KDA</span>
      </template>
    </div>

    <div class="relative z-[2] flex flex-wrap gap-1.5 items-center px-4 w-[22.625rem] shrink-0">
      <template v-for="level in talentLevels" :key="level">
        <talent-image-wrapper v-if="data[level]" :talent="data[level]" size="talent"></talent-image-wrapper>
        <div v-else class="w-[2.625rem] h-[2.625rem] shrink-0 rounded bg-surface-3"></div>
      </template>
    </div>

    <div v-if="data.players && data.players.length" class="relative z-[2] flex gap-2 pl-1.5 pr-12 py-4 w-[22.625rem] shrink-0">
      <div v-for="(team, teamIndex) in data.players" :key="teamIndex" class="flex flex-col gap-1.5 w-[9.375rem]">
        <!-- A party's icon sits centred beside a line in its colour running down the group -->
        <div v-for="group in partyGroups(team)" :key="group.start" class="flex items-center">
          <span class="w-4 shrink-0 flex items-center justify-center">
            <img v-if="group.party" class="w-4 h-4" :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${group.party}.png`" alt=""/>
          </span>
          <span class="w-[2px] ml-[0.1875rem] self-stretch shrink-0 rounded-l-full" :style="group.party && group.players.length > 1 ? { backgroundColor: partyColors[group.party] } : null"></span>
          <div class="flex flex-col gap-1.5 flex-1 min-w-0 ml-0.5">
            <div v-for="(player, groupIndex) in group.players" :key="groupIndex" class="flex gap-1 h-4 items-center">
              <img v-if="player.hero" class="w-4 h-4 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${player.hero.short_name}.png`" :alt="player.hero.name"/>
              <!-- The profile's own player comes first -->
              <span v-if="player.check" class="flex-1 min-w-0 truncate text-sm font-medium text-subtext">Hidden</span>
              <icon-with-hover v-else class="flex-1" popupsize="fit">
                <template #trigger>
                  <a :href="player.link || `/Player/${player.battletag}/${player.blizz_id}/${player.region}`" @click="!player.link && $redirectToProfile(player.battletag, player.blizz_id, player.region, false)" :class="['group/bt min-w-0 inline-flex items-center text-sm font-medium hover:text-lteal', player.own ? 'text-white' : 'text-subtext']"><span class="truncate group-hover/bt:underline">{{ player.battletag }}</span><i class="fas fa-chevron-right text-[0.625rem] ml-1 shrink-0 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a>
                </template>
                <div class="flex items-center gap-2 text-base font-bold text-white">
                  <img v-if="player.hero" class="w-6 h-6 rounded-full object-cover bg-surface-3 text-[0rem]" :src="`/images/heroes/${player.hero.short_name}.png`" alt=""/>
                  <span class="min-w-0 break-words">{{ player.battletag }}</span>
                  <img v-if="player.party" class="w-5 h-5" :src="`/images/party_icons/ui_ingame_loadscreen_partylink_${player.party}.png`" alt=""/>
                </div>
                <image-hover-box
                  :paragraph-one="player.hero ? `Hero: ${player.hero.name}` : null"
                  :paragraph-two="player.account_level != null ? `Account Level: ${player.account_level}` : null"
                  :paragraph-three="player.hero_level != null ? `Hero Level: ${player.hero_level}` : null"
                  :paragraph-four="player.player_mmr != null ? `Player MMR: ${player.player_mmr.toLocaleString('en-US')}` : null"
                ></image-hover-box>
              </icon-with-hover>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="data.downloadable || isWatchableGame" :class="['absolute z-[3] flex transition-opacity', compact ? 'right-4 top-1/2 -translate-y-1/2 flex-row-reverse gap-4' : 'right-2 top-2 flex-col gap-2']" class="md:opacity-0 md:group-hover/replay:opacity-100 md:focus-within:opacity-100">
    <a v-if="data.downloadable && replayViewerAllowed && !replayDownloadBlocked" class="max-md:hidden hover:text-lteal" :href="'/Match/Single/' + data.replayID + '/Download'" title="Download Replay"><i class="fa-solid fa-download"></i></a>
    <span v-else-if="data.downloadable && !replayViewerAllowed" class="max-md:hidden opacity-60" title="Log in to download replays"><i class="fa-solid fa-download"></i></span>
    <a v-if="isWatchableGame && replayViewerAllowed" class="hover:text-lteal" :href="'/Analytics/Match/Single/' + data.replayID" title="Watch Replay"><i class="fa-solid fa-video"></i></a>
    <span v-else-if="isWatchableGame" class="opacity-60" title="Log in with Battle.net to watch replays"><i class="fa-solid fa-video"></i></span>
  </div>
  </div>

  <div v-else :class="[
  'relative ml-10 ',
  {
    'ml-10' : !esport
  } ]">
    <a :href="getHref()">
      <div :class="[
      'mt-4  m-l-auto w-full text-right min-h-4 py-2 md:pl-[6em] break-words',
      {
        'md:pl-[6em]' : esport != true
      }
      ]
      ">{{ getCaptions() }}</div>
      <div
        :class="[
          'flex border border-2 bg-cover bg-no-repeat bg-center  rounded border-red  md:pl-10',
          {
            'border-white': !esport || data.winner !== 0,
            'border-hred': esport && data.winner === 0,
            'border-teal': data.winner === 1,
            'md:pl-[6em] ': esport != true
          }
          ]"
          :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"
          >    

          <div class="flex flex-wrap justify-between gap-2 w-full hover:backdrop-brightness-125 py-1 max-md:p-2">
            <div v-for="(item, index) in data.heroes">
              <hero-image-wrapper  v-if="item" size="big" :hero="item.hero" :excludehover="true"></hero-image-wrapper>
            </div>
            <stat-box class="ml-auto md:w-[30%] md:mr-10" :title="'Teams'" :value="data.team_0_name + ' vs ' + data.team_1_name"></stat-box>
          </div>
        </div>
    </a>
  </div>
</template>


<script>
  import moment from 'moment-timezone';

  export default {
    name: 'GameSummaryBox',
    components: {
    },
    props: {
      data: Object,
      esport: Boolean,
      esportLeague: String,
      esportSeries: String,
      tournament: String,
      // Download and Watch Replay icons, with the single match page's rules
      replayViewerAllowed: Boolean,
      analyticsCutover: String,
      replayDownloadBlocked: Boolean,
      // A single line per game, as in the match history's Compact view
      compact: Boolean,
    },
    data(){
      return {
        userTimezone: moment.tz.guess(),
      // Sampled from the party icons
      partyColors: { purple: '#9b2bed', blue: '#2060ae', red: '#ae2023', yellow: '#c88b00' },
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
      mmrStats(){
        const hasChange = this.data.player_change !== null;
        return [
          { title: 'Player MMR', value: this.data.player_mmr, change: hasChange ? this.data.player_change : null },
          { title: 'Hero MMR', value: this.data.hero_mmr, change: hasChange ? this.data.hero_change : null },
          { title: 'Role MMR', value: this.data.role_mmr, change: hasChange ? this.data.role_change : null },
        ];
      },
      kda(){
        return parseFloat(((this.data.kills + this.data.assists) / Math.max(this.data.deaths, 1)).toFixed(2));
      },
      // A game from the season start on: the ones the replay viewer can show
      isWatchableGame(){
        return !!this.analyticsCutover && this.data.game_date >= this.analyticsCutover;
      },
      talentLevels(){
        return ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];
      },
    },
    watch: {
    },
    methods: {
      // Runs of players in the same party; the backend already sorts party members together
      partyGroups(team){
        return team.reduce((groups, player, index) => {
          const last = groups[groups.length - 1];
          if(last && player.party && last.party === player.party){
            last.players.push(player);
          }else{
            groups.push({ party: player.party, start: index, players: [player] });
          }
          return groups;
        }, []);
      },
      getHref(){
        if(this.esport){
          if(this.esportSeries){
            return '/Esports/' + this.esportLeague + '/' + this.esportSeries + '/Match/Single/' + this.data.replayID;
          }

          if(this.esportLeague == "HeroesInternational"){
            return '/Esports/' + this.esportLeague + '/Match/Single/' + this.data.replayID + '/?tournament=' + this.tournament;
          }

          return '/Esports/' + this.esportLeague + '/Match/Single/' + this.data.replayID;
        }
        return '/Match/Single/' + this.data.replayID;
      },
      formatDate(dateString) {
        const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
        const localDate = originalDate.clone().tz(moment.tz.guess());

        return localDate.format('MM/DD/YYYY h:mm:ss a');
      },
      // In the viewer's own date format and timezone, e.g. "October 22, 2026 at 10:57 PM" in the US
      formatRowDate(dateString) {
        return moment.utc(dateString).toDate().toLocaleString(undefined, {
          dateStyle: 'long',
          timeStyle: 'short',
          timeZone: this.userTimezone,
        });
      },
      getCaptions(){
        if(!this.esport){
          return this.data.game_map.name + "|" + this.data.game_type.name + "|" + this.formatDate(this.data.game_date);
        }else{
          return this.data.game_map.name + "|" + "Round " + this.data.round + " Game " + this.data.game + "|" + this.formatDate(this.data.game_date);
        }
      }
    }
  }
</script>