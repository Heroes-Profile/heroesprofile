<template>
  <div>
    <div class="flex max-w-[1920px] mx-auto">
      <div class="">
      <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'All Role data for ' + battletag + '. Click a role to see individual role statistics'" -->
      <page-heading v-if="esport" :heading="'Role Stats'" :battletag="battletag" :esport="esport" heading-image="/images/NGS/ngs_large.png" heading-image-url="/Esports/NGS"></page-heading>
      <page-heading v-else :heading="'Role Stats'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>
      </div>
      <div class="w-full">
      <filters v-if="!esport"
      :onFilter="filterData"  
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :minimumgamesdefault="'0'"
      :includegametypefull="true"
      :includeminimumgames="true"
      :hideadvancedfilteringbutton="true"
      :includegamemap="true"
      :includemultiseason="true"
      :includegamedaterange="true"
      :includehero="true"
      >
    </filters>
      <panel-box v-else-if="esport && ngsFilterOptions">
        <div class="filter-row justify-center">
          <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
          <single-select-filter :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
          <single-select-filter :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="getNgsData" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
        </div>
      </panel-box>
      </div>
    </div>
  <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

  <div v-if="data">
    <div  class="relative max-w-[1500px] mx-auto">
      <custom-button class="ml-auto" @click="showOptions = !showOptions" :text="showOptions ? 'Hide Column Selection' : 'Show Column Selection'" :ignoreclick="true"></custom-button>

      <div v-if="showOptions">
        <div class="absolute left-0 mt-2 w-full variable-background border border-gray-300 rounded shadow-lg text-black z-50 flex flex-wrap  p-2 ">
          <input class="w-full p-2" type="text" v-model="searchQuery" placeholder="Search..." />
          <div v-for="(stat, index) in filteredStats" :class="[
            'flex-1 min-w-[24%] border-gray border p-1 cursor-pointer variable-hover',
            {
              'bg-teal text-white' : stat.selected
            } ]
            ">
            <label class="cursor-pointer"><input type="checkbox" v-model="stat.selected" :disabled="isDisabled(stat)" @change="onCheckboxChange(stat)" />
            {{ stat.name }}</label>
          </div>
        </div>
      </div>
    </div>
    <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[100vw]   2xl:mx-auto  " style=" ">

<table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
      <thead>
        <tr>
          <th @click="sortTable('name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Role<sort-indicator :column="'name'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>    
          <th @click="sortTable('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate<sort-indicator column="win_rate" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('games_played')" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Total Games<sort-indicator column="games_played" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <th @click="sortTable('kda')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            <div class="">
              KDA
            </div><sort-indicator :column="'kda'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>  
          <th @click="sortTable('kdr')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            <div class="">
              KDR
            </div><sort-indicator :column="'kdr'" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
          </th>
          <template   v-for="stat in selectedStats">
            <th  v-if="stat.selected" @click="sortTable(stat.value)" class="py-2 px-3  text-center text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ stat.name }}<sort-indicator :column="stat.value" :sort-key="sortKey" :sort-dir="sortDir"></sort-indicator>
            </th>
          </template> 
          
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in sortedData" :key="row.id">
          <td class="py-2 px-3 "><a :href="getPlayerRolePageUrl(row.name)"><role-box :role="row.name"></role-box></a></td>
          <td class="py-2 px-3"><div class="min-w-[8em]"><div class="text-sm font-semibold text-header tabular-nums mb-1.5">{{ row.win_rate }}<span class="text-xs">%</span></div><stat-bar :value="row.win_rate" display-text="" :color="getWinRateColor(row.win_rate)"></stat-bar></div></td>
          <td class="py-2 px-3 text-center">{{ (row.wins + row.losses) }}</td>
          <td class="py-2 px-3 ">{{ row.kda }}</td>
          <td class="py-2 px-3 ">{{ row.kdr }}</td>
          
          <template v-for="stat in selectedStats" >
            <td 
              v-if="stat.selected" 
              :class="{ flash: stat.flash }"
              class="py-2 px-3 text-center">
              {{ showStatValue(row[stat.value]) }}
            </td>
          </template>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
  <div v-else-if="isLoading || asyncLoading" class="max-w-[1500px] mx-auto px-4 flex flex-col gap-6">
    <loading-component v-if="asyncLoading" :compact="true" :textoverride="true" :overrideimage="esport ? '/images/NGS/no-image-clipped.png' : undefined">This is taking longer than expected. A background task is computing your results. Please be patient.</loading-component>
    <loading-component v-else :compact="true" :textoverride="true" :timer="!esport" :starttime="timertime" :overrideimage="esport ? '/images/NGS/no-image-clipped.png' : undefined">Large amount of data. Please be patient.</loading-component>
    <div>
      <skeleton-box class="h-14 rounded-b-none"></skeleton-box>
      <table-skeleton :bare="true" :rows="6" :columns="['portrait', 'bar', 'stat', 'text', 'text', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat', 'stat']"></table-skeleton>
    </div>
  </div>
</div>
</template>

<script>
import { scaleTableToFit } from '../../../utils/scaleTable';
  export default {
    name: 'PlayerRolesAllStats',
    components: {
    },
    props: {
      filters: {
        type: Object,
        required: true
      },
      playerloadsetting: {
        type: [String, Boolean]
      },
      battletag: String,
      blizzid: String, 
      region: String,
      regionsmap: Object,
      accountlevel: Number,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
    // 'NGS' on the NGS player pages, with the season, division and team from the URL
    esport: String,
    esportfilters: Object,
    },
    data(){
      return {
        windowWidth: window.innerWidth,
        cancelTokenSource: null,
        showOptions: false,
        isLoading: false,
        asyncLoading: false,
        infoText: "Select a hero below to view detailed stats for that hero. Use the search box above to filter the list of heroes. Or scroll down to the advanced section for table view.",
        gametype: null,
        data: null,
        sortKey: '',
        sortDir: 'desc',
        role: null,
        hero: null,
        minimumgames: 0,
        searchQuery: '',
        gamemap: null,
        season: null,
        startdate: null,
        enddate: null,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
        stats: [
          { name: "Avg Assists", value: 'avg_assists', selected: true, flash: false},
          { name: "Avg Clutch Heals", value: 'avg_clutch_heals', selected: false, flash: false},
          { name: "Avg Creep Damage", value: 'avg_creep_damage', selected: false, flash: false},
          { name: "Avg Damage Taken", value: 'avg_damage_taken', selected: true, flash: false},
          { name: "Avg Deaths", value: 'avg_deaths', selected: true, flash: false},
          { name: "Avg Escapes", value: 'avg_escapes', selected: false, flash: false},
          { name: "Avg Experience Contribution", value: 'avg_experience_contribution', selected: true, flash: false},
          { name: "Avg First to Ten", value: 'avg_first_to_ten', selected: false, flash: false},
          { name: "Avg Healing", value: 'avg_healing', selected: false, flash: false},
          { name: "Avg Hero Damage", value: 'avg_hero_damage', selected: true, flash: false},
          { name: "Avg Highest Kill Streak", value: 'avg_highest_kill_streak', selected: false, flash: false},
          { name: "Avg Kills", value: 'avg_kills', selected: true, flash: false},
          { name: "Avg Merc Camp Captures", value: 'avg_merc_camp_captures', selected: false, flash: false},
          { name: "Avg Minion Damage", value: 'avg_minion_damage', selected: false, flash: false},
          { name: "Avg Multikill", value: 'avg_multikill', selected: false, flash: false},
          { name: "Avg Outnumbered Deaths", value: 'avg_outnumbered_deaths', selected: false, flash: false},
          { name: "Avg Physical Damage", value: 'avg_physical_damage', selected: false, flash: false},
          { name: "Avg Protection Allies", value: 'avg_protection_allies', selected: false, flash: false},
          { name: "Avg Regen Globes", value: 'avg_regen_globes', selected: false, flash: false},
          { name: "Avg Rooting Enemies", value: 'avg_rooting_enemies', selected: false, flash: false},
          { name: "Avg Self Healing", value: 'avg_self_healing', selected: false, flash: false},
          { name: "Avg Siege Damage", value: 'avg_siege_damage', selected: true, flash: false},
          { name: "Avg Silencing Enemies", value: 'avg_silencing_enemies', selected: false, flash: false},
          { name: "Avg Spell Damage", value: 'avg_spell_damage', selected: false, flash: false},
          { name: "Avg Structure Damage", value: 'avg_structure_damage', selected: false, flash: false},
          { name: "Avg Stunning Enemies", value: 'avg_stunning_enemies', selected: false, flash: false},
          { name: "Avg Summon Damage", value: 'avg_summon_damage', selected: false, flash: false},
          { name: "Avg Takedowns", value: 'avg_takedowns', selected: false, flash: false},
          { name: "Avg Teamfight Damage Taken", value: 'avg_teamfight_damage_taken', selected: false, flash: false},
          { name: "Avg Teamfight Escapes", value: 'avg_teamfight_escapes', selected: false, flash: false},
          { name: "Avg Teamfight Healing", value: 'avg_teamfight_healing', selected: false, flash: false},
          { name: "Avg Teamfight Hero Damage", value: 'avg_teamfight_hero_damage', selected: false, flash: false},
          { name: "Avg Time CC Enemy Heroes", value: 'avg_time_cc_enemy_heroes', selected: false, flash: false},
          { name: "Avg Time on Fire", value: 'avg_time_on_fire', selected: false, flash: false},
          { name: "Avg Time Spent Dead", value: 'avg_time_spent_dead', selected: false, flash: false},
          { name: "Avg Town Kills", value: 'avg_town_kills', selected: false, flash: false},
          { name: "Avg Vengeance", value: 'avg_vengeance', selected: false, flash: false},
          { name: "Avg Watch Tower Captures", value: 'avg_watch_tower_captures', selected: false, flash: false},
          { name: "Avg Total Healing", value: 'combined_healing', selected: false, flash: false},
          { name: "KDA", value: 'kda', selected: false, flash: false},
          { name: "KDR", value: 'kdr', selected: false, flash: false},
          { name: "Losses", value: 'losses', selected: false, flash: false},
          { name: "Max Assists", value: 'max_assists', selected: false, flash: false},
          { name: "Max Clutch Heals", value: 'max_clutch_heals', selected: false, flash: false},
          { name: "Max Creep Damage", value: 'max_creep_damage', selected: false, flash: false},
          { name: "Max Damage Taken", value: 'max_damage_taken', selected: false, flash: false},
          { name: "Max Deaths", value: 'max_deaths', selected: false, flash: false},
          { name: "Max Escapes", value: 'max_escapes', selected: false, flash: false},
          { name: "Max Experience Contribution", value: 'max_experience_contribution', selected: false, flash: false},
          { name: "Max First to Ten", value: 'max_first_to_ten', selected: false, flash: false},
          { name: "Max Healing", value: 'max_healing', selected: false, flash: false},
          { name: "Max Hero Damage", value: 'max_hero_damage', selected: false, flash: false},
          { name: "Max Highest Kill Streak", value: 'max_highest_kill_streak', selected: false, flash: false},
          { name: "Max Kills", value: 'max_kills', selected: false, flash: false},
          { name: "Max Merc Camp Captures", value: 'max_merc_camp_captures', selected: false, flash: false},
          { name: "Max Minion Damage", value: 'max_minion_damage', selected: false, flash: false},
          { name: "Max Multikill", value: 'max_multikill', selected: false, flash: false},
          { name: "Max Outnumbered Deaths", value: 'max_outnumbered_deaths', selected: false, flash: false},
          { name: "Max Physical Damage", value: 'max_physical_damage', selected: false, flash: false},
          { name: "Max Protection Allies", value: 'max_protection_allies', selected: false, flash: false},
          { name: "Max Regen Globes", value: 'max_regen_globes', selected: false, flash: false},
          { name: "Max Rooting Enemies", value: 'max_rooting_enemies', selected: false, flash: false},
          { name: "Max Self Healing", value: 'max_self_healing', selected: false, flash: false},
          { name: "Max Siege Damage", value: 'max_siege_damage', selected: false, flash: false},
          { name: "Max Silencing Enemies", value: 'max_silencing_enemies', selected: false, flash: false},
          { name: "Max Spell Damage", value: 'max_spell_damage', selected: false, flash: false},
          { name: "Max Structure Damage", value: 'max_structure_damage', selected: false, flash: false},
          { name: "Max Stunning Enemies", value: 'max_stunning_enemies', selected: false, flash: false},
          { name: "Max Summon Damage", value: 'max_summon_damage', selected: false, flash: false},
          { name: "Max Takedowns", value: 'max_takedowns', selected: false, flash: false},
          { name: "Max Teamfight Damage Taken", value: 'max_teamfight_damage_taken', selected: false, flash: false},
          { name: "Max Teamfight Escapes", value: 'max_teamfight_escapes', selected: false, flash: false},
          { name: "Max Teamfight Healing", value: 'max_teamfight_healing', selected: false, flash: false},
          { name: "Max Teamfight Hero Damage", value: 'max_teamfight_hero_damage', selected: false, flash: false},
          { name: "Max Time CC Enemy Heroes", value: 'max_time_cc_enemy_heroes', selected: false, flash: false},
          { name: "Max Time on Fire", value: 'max_time_on_fire', selected: false, flash: false},
          { name: "Max Time Spent Dead", value: 'max_time_spent_dead', selected: false, flash: false},
          { name: "Max Town Kills", value: 'max_town_kills', selected: false, flash: false},
          { name: "Max Vengeance", value: 'max_vengeance', selected: false, flash: false},
          { name: "Max Watch Tower Captures", value: 'max_watch_tower_captures', selected: false, flash: false},
          { name: "Wins", value: 'wins', selected: false, flash: false},
          ],
}
},
created(){
  this.gametype = this.gametypedefault;
},
mounted() {
  if(this.esport){
    this.getNgsData();
    return;
  }
  if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
},
computed: {
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    ngsPlayerUrl() {
      return `/Esports/NGS/Player/${this.battletag}/${this.blizzid}`;
    },
  timertime(){
    return parseInt(this.accountlevel * 3 * .003);
  },
  filteredStats() {
    return this.stats.filter(stat => stat.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
  },
  selectedStatsCount() {
    return this.stats.filter(stat => stat.selected).length;
  },
  // Same order as the in-game scoreboard, then anything else picked
  selectedStats() {
    const order = ['avg_kills', 'avg_assists', 'avg_deaths', 'avg_siege_damage', 'avg_hero_damage', 'avg_damage_taken', 'avg_experience_contribution'];
    const rank = stat => (order.includes(stat.value) ? order.indexOf(stat.value) : order.length);
    return this.stats.filter(stat => stat.selected).sort((a, b) => rank(a) - rank(b));
  },
  sortedData() {
    if (!this.sortKey) return this.data;
    return this.data.slice().sort((a, b) => {
      const valA = a[this.sortKey];
      const valB = b[this.sortKey];
      if (this.sortDir === 'asc') {
        return valA < valB ? -1 : 1;
      } else {
        return valA > valB ? -1 : 1;
      }
    });
  },
  isOwner(){
    if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
      return true;
    }
    return false;
  },
},
watch: {
},
methods: {
    ngsParams(extra = {}) {
      return { battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam, ...extra };
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field) {
        this[field] = eventPayload.value;
      }
      const params = new URLSearchParams();
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      history.pushState({}, '', window.location.pathname + (query ? `?${query}` : ''));
    },
  async getNgsData(){
    this.data = null;
    this.isLoading = true;
    try{
      const response = await this.$axios.post("/api/v1/esports/ngs/player/roles", this.ngsParams());
      this.ngsFilterOptions = response.data.filters;
      this.data = response.data.rows;
    }catch(error){
      //Do something here
    }finally {
      this.isLoading = false;
      this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
        if (responsivetable && this.windowWidth < 1500) {
          scaleTableToFit(responsivetable, this.$refs.tablecontainer);
        }
      });
    }
  },
  async getData(type){
    this.isLoading = true;

    if (this.cancelTokenSource) {
      this.cancelTokenSource.cancel('Request canceled');
    }
    this.cancelTokenSource = this.$axios.CancelToken.source();

    try{
      const response = await this.$globalAsyncPost("/api/v1/player/roles/all", {
        battletag: this.battletag,
        blizz_id: this.blizzid,
        region: this.region,
        game_type: this.gametype,
        hero: this.hero,
        role: this.role,
        minimumgames: this.minimumgames,
        type: "all",
        page: "role",
        game_map: this.gamemap,
        season: this.season,
        start_date: this.startdate,
        end_date: this.enddate,
      },
      {
        cancelToken: this.cancelTokenSource.token,
        onLoadStatus: (status, meta) => {
          if (meta.phase === 'polling') {
            this.asyncLoading = true;
          }
        },
      });

      this.data = response.data;
    }catch(error){
      //Do something here
    }finally {
      this.cancelTokenSource = null;
      this.isLoading = false;
      this.asyncLoading = false;
      this.$nextTick(() => {
        const responsivetable = this.$refs.responsivetable;
          if (responsivetable && this.windowWidth < 1500) {
            scaleTableToFit(responsivetable, this.$refs.tablecontainer);
          }
        });
    }
  },
  cancelAxiosRequest() {
    if (this.cancelTokenSource) {
      this.cancelTokenSource.cancel('Request canceled by user');
    }
  },
  filterData(filteredData){
    this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
    this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
    // The filter gives a hero id; the server takes the name.
    const hero = filteredData.single.Heroes
      ? this.filters.heroes.find(h => h.code === filteredData.single.Heroes)
      : null;
    this.hero = hero ? hero.name : null;
    this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
    this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
    this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
    this.startdate = filteredData.single["From Date"] || null;
    this.enddate = filteredData.single["To Date"] || null;

    this.data = null;
    this.sortKey = '';
    this.sortDir ='asc';
    this.getData();
  },
  sortTable(key) {
    if (key === this.sortKey) {
      this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
    } else {
      this.sortDir = 'desc';
    }
    this.sortKey = key;
  },
  showGameTypeColumn(game_type){
    return this.gametype.includes(game_type);
  },
  getPlayerRolePageUrl(role){
    if (this.esport) {
      return `${this.ngsPlayerUrl}/Role/${role}${window.location.search}`;
    }
    return "/Player/" + this.battletag + "/" + this.blizzid + "/" + this.region + "/" + "Role/" + role;
  },
  isDisabled(stat) {
    return this.selectedStatsCount >= 15 && !stat.selected;
  },
  onCheckboxChange(inputStat) {
    inputStat.flash = true;
    setTimeout(() => {
      inputStat.flash = false;
    }, 1000);
  },
  getWinRateColor(win_rate){
    if(win_rate < 40){
      return "red";
    }else if(win_rate < 50){
      return "yellow";
    }
    return "blue";
  },
  showStatValue(value) {
      if (!value || isNaN(value)) {
        return 0;
      }

      if (value < 1000) {
        return value.toFixed(2);
      } else {
        return Math.round(value).toLocaleString('en-US');
      }
    },
  }
}
</script>