<template>
  <div>
    <page-heading v-if="esport" :infoText1="infoText" :heading="'Friend vs Foe'" :battletag="battletag" :esport="esport" :heading-image="league.headingImage" :heading-image-url="league.headingImageUrl"></page-heading>
    <page-heading v-else :infoText1="infoText" :heading="'Friend vs Foe'"  :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

    <div v-if="esport && ngsFilterOptions" class="flex flex-wrap justify-center max-w-[1500px] mx-auto">
      <single-select-filter :values="ngsFilterOptions.seasons" :text="'Seasons'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsSeason"></single-select-filter>
      <single-select-filter v-if="ngsFilterOptions.divisions" :values="ngsFilterOptions.divisions" :text="'Divisions'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsDivision"></single-select-filter>
      <single-select-filter v-if="ngsFilterOptions.teams.length" :values="ngsTeamOptions" :text="'Teams'" @input-changed="ngsFilterChange" @dropdown-closed="ngsDropdownClosed" :trackclosure="true" :defaultValue="ngsTeam"></single-select-filter>
    </div>

    <filters v-if="!esport"

      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="friendIsLoading || enemyIsLoading"
      :gametypedefault="gametype"
      :includehero="true"
      :includegamemap="true"
      :includegametypefull="true"
      :includemultiseason="true"
      :includegamedaterange="true"
      :includegroupsize="true"
      :hideadvancedfilteringbutton="true"
      :groupSizeDefaultValue="'All'"
      :overrideGroupSizeRemoval="true"
      >
    </filters>
    
    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="asyncLoading">
      <loading-component :textoverride="true">This is taking longer than expected.<br/>A background task is computing your results.<br/>Please be patient.</loading-component>
    </div>

    <div v-if="frienddata && enemydata" class="gap-1 mx-auto  flex justify-center max-w-[1500px] max-md:flex-col max-md:text-sm max-md:max-w-[100vw]">
      <div ref="friendcontainer" class="max-md:overflow-hidden">
        <table ref="friendtable" class="min-w-0 max-w-[750px]">
          <thead >
            <tr >
              <th  @click="sortTableFriend('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableFriend('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[35%]">
                Favorite Hero
              </th>            
              <th @click="sortTableFriend('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[20%]">
                Games Played <br/>With
              </th>
              <th  @click="sortTableFriend('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%] ">
                Win Rate with Friend
              </th>
              <th v-if="!esport" class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider w-[10%]">
                Matches
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataFriends" :key="row.blizz_id">
              <td class="py-2 px-3 ">
                <div class="flex items-center">
                  <a v-if="esport" class="link mr-2" :href="row.link">{{ row.battletag }}</a>
                  <a v-else class="link mr-2" @click="this.$redirectToProfile(row.battletag, row.blizz_id, row.region, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" >{{ row.battletag }}</a>
                  <div class="" v-if="row.hp_owner">
                    <i class="fas fa-crown text" style="color:gold;"></i>
                  </div>
                  <div class="" v-else-if="row.patreon">
                    <i class="fas fa-star" style="color:gold"></i>
                  </div>
                  <void-eye-flair v-if="!esport" :blizz-id="row.blizz_id" :region="row.region" :tooltip="false"></void-eye-flair>

                </div>
              </td>
              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <image-hover-box :title="row.heroData.hero.name" :paragraph-one="'Games Played:' + row.hero_games.toLocaleString('en-US')"></image-hover-box>
                </hero-image-wrapper>
               <span class="max-md:hidden"> {{ row.heroData.hero.name }} </span>
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td v-if="!esport" class="py-2 px-3">
                <a class="link" :href="`/Player/${battletag}/${blizzid}/${region}/Match/History?ff_battletag=${row.battletag}&ff_blizzid=${row.blizz_id}&ff_region=${row.region}`">Matches</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div ref="enemycontainer" class="max-md:overflow-hidden">
        <table ref="enemytable" class="min-w-0 max-w-[750px]">
          <thead class="bg-red">
            <tr>
              <th @click="sortTableEnemy('battletag')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Player
              </th>
              <th @click="sortTableEnemy('hero')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[35%]">
                Favorite Hero
              </th>            
              <th @click="sortTableEnemy('total_games_played')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[20%]">
                Games Played <br/>Against
              </th>
              <th @click="sortTableEnemy('win_rate')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer w-[25%]">
                Foe's Win Rate Against {{ battletag }}
              </th>
              <th v-if="!esport" class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider w-[10%]">
                Matches
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in sortedDataEnemies" :key="row.blizz_id">
              <td class="py-2 px-3 ">

                <div class="flex items-center">
                  <a v-if="esport" class="link mr-2" :href="row.link">{{ row.battletag }}</a>
                  <a v-else class="link mr-2" @click="this.$redirectToProfile(row.battletag, row.blizz_id, row.region, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" >{{ row.battletag }}</a>
                  <div class="" v-if="row.hp_owner">
                    <i class="fas fa-crown text" style="color:gold;"></i>
                  </div>
                  <div class="" v-else-if="row.patreon">
                    <i class="fas fa-star" style="color:gold"></i>
                  </div>
                  <void-eye-flair v-if="!esport" :blizz-id="row.blizz_id" :region="row.region" :tooltip="false"></void-eye-flair>

                </div>
              </td>

              <td class="py-2 px-3 flex items-center gap-1">
                <hero-image-wrapper :hero="row.heroData.hero">
                  <h2>{{ row.heroData.hero.name }}</h2>
                  <p>Games Played: {{ row.hero_games.toLocaleString('en-US') }}</p>
                </hero-image-wrapper>
                <span class="max-md:hidden">{{ row.heroData.hero.name }} </span>
              </td>
              <td class="py-2 px-3 ">{{ row.total_games_played.toLocaleString('en-US') }}</td>
              <td class="py-2 px-3 ">{{ row.win_rate.toFixed(2) }}</td>
              <td v-if="!esport" class="py-2 px-3">
                <a class="link" :href="`/Player/${battletag}/${blizzid}/${region}/Match/History?ff_battletag=${row.battletag}&ff_blizzid=${row.blizz_id}&ff_region=${row.region}`">Matches</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else-if="(friendIsLoading || enemyIsLoading) && !asyncLoading">
      <loading-component @cancel-request="cancelAxiosRequest" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    </div>
  </div>
</template>

<script>
import { scaleTableToFit } from '../../utils/scaleTable';

export default {
  name: 'FriendFoe',
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
    gametypedefault: Array,
    regionsmap: Object,
    isPatreon: Boolean,
    patreonUser: Boolean,
    // The league on its esports player pages (NGS, CCL, MastersClash, HeroesInternational, Other), with its filters from the URL
    esport: String,
    esportfilters: Object,
  },
  data(){
    return {
      friendIsLoading: false,
      enemyIsLoading: false,
      infoText: "Friends and Foe data showing who " + this.battletag + " plays the most games with and against",
      frienddata: null,
      enemydata: null,
      friendSortKey: '',
      friendSortDir: 'desc',

      enemySortKey: '',
      enemySortDir: 'desc',

      gametype: null,
      gamemap: null,
      season: null,
      startdate: null,
      enddate: null,
      friendCancelTokenSource: null,
      enemyCancelTokenSource: null,
      groupsize: null,
      asyncLoading: false,
      ngsSeason: this.esportfilters?.season ?? null,
      ngsDivision: this.esportfilters?.division ?? null,
      ngsTeam: this.esportfilters?.team ?? null,
      ngsFilterOptions: null,
      ngsFiltersChanged: false,
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
      Promise.allSettled([
      this.getData("friend"),
      this.getData("enemy"),
    ]).then(results => {
      if (results[0].status === "fulfilled") {
        this.frienddata = results[0].value;
      } else {
      }
      
      if (results[1].status === "fulfilled") {
        this.enemydata = results[1].value;
      } else {
      }
    });
  }



  },
  updated() {
    if (window.matchMedia('(max-width: 767px)').matches) {
      scaleTableToFit(this.$refs.friendtable, this.$refs.friendcontainer);
      scaleTableToFit(this.$refs.enemytable, this.$refs.enemycontainer);
    }
  },
  computed: {
    league() {
      return this.esport ? this.$esportLeague(this.esport, this.esportfilters) : null;
    },
    ngsTeamOptions() {
      return [{ code: null, name: 'All' }, ...(this.ngsFilterOptions?.teams || []).map(name => ({ code: name, name: this.$ngsTeam(name).name }))];
    },
    sortedDataFriends() {
      if (!this.friendSortKey) return this.frienddata;
      return this.frienddata.slice().sort((a, b) => {
        const valA = a[this.friendSortKey];
        const valB = b[this.friendSortKey];
        if (this.friendSortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    },
    sortedDataEnemies() {
      if (!this.enemySortKey) return this.enemydata;
      return this.enemydata.slice().sort((a, b) => {
        const valA = a[this.enemySortKey];
        const valB = b[this.enemySortKey];
        if (this.enemySortDir === 'asc') {
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
    ngsParams() {
      return { ...this.league.params, battletag: this.battletag, blizz_id: this.blizzid, season: this.ngsSeason, division: this.ngsDivision, team: this.ngsTeam };
    },
    ngsFilterChange(eventPayload) {
      const field = { Seasons: 'ngsSeason', Divisions: 'ngsDivision', Teams: 'ngsTeam' }[eventPayload.field];
      if (field && this[field] !== eventPayload.value) {
        this[field] = eventPayload.value;
        this.ngsFiltersChanged = true;
      }
      const params = new URLSearchParams(this.league.params);
      if (this.ngsSeason) params.set('season', this.ngsSeason);
      if (this.ngsDivision) params.set('division', this.ngsDivision);
      if (this.ngsTeam) params.set('team', this.ngsTeam);
      const query = params.toString();
      history.pushState({}, '', window.location.pathname + (query ? `?${query}` : ''));
    },
    ngsDropdownClosed() {
      if (this.ngsFiltersChanged) {
        this.ngsFiltersChanged = false;
        this.getNgsData();
      }
    },
    async getNgsData(){
      this.frienddata = null;
      this.enemydata = null;
      this.friendIsLoading = true;
      this.enemyIsLoading = true;
      try {
        const response = await this.$axios.post(`${this.league.api}/friendfoe`, this.ngsParams());
        this.ngsFilterOptions = response.data.filters;
        this.frienddata = response.data.friend;
        this.enemydata = response.data.enemy;
      } catch(error) {
        //Do something here
      } finally {
        this.friendIsLoading = false;
        this.enemyIsLoading = false;
      }
    },
    async getData(type){
      let cancelTokenSource;

      if (type === 'friend') {
        this.friendCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.friendCancelTokenSource;
        this.friendIsLoading = true;
      } else if (type === 'enemy') {
        this.enemyCancelTokenSource = this.$axios.CancelToken.source();
        cancelTokenSource = this.enemyCancelTokenSource;
        this.enemyIsLoading = true;
      }

      try {
        const response = await this.$globalAsyncPost(
          "/api/v1/player/friendfoe",
          {
            type: type,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
            hero: this.hero,
            game_map: this.gamemap,
            groupsize: this.groupsize,
          },
          {
            cancelToken: cancelTokenSource.token,
            onLoadStatus: (status, meta) => {
              if (meta.phase === 'polling') {
                this.asyncLoading = true;
              }
            },
          }
        );

        return response.data;
      } catch(error) {
        //Do something here
      } finally {
        if (type === 'friend') {
          this.friendCancelTokenSource = null;
          this.friendIsLoading = false;
        } else if (type === 'enemy') {
          this.enemyCancelTokenSource = null;
          this.enemyIsLoading = false;
        }
        if (!this.friendIsLoading && !this.enemyIsLoading) {
          this.asyncLoading = false;
        }
      }
    },
    cancelAxiosRequest() {
      if (this.friendCancelTokenSource || this.enemyCancelTokenSource) {
        this.friendCancelTokenSource.cancel('Request canceled by user');
        this.enemyCancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.hero = filteredData.single["Heroes"] ? filteredData.single["Heroes"] : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;
      this.groupsize = filteredData.single["Group Size"] && (filteredData.single["Group Size"] != 'All') ? filteredData.single["Group Size"] : null;

      this.frienddata = null;
      this.enemydata = null;
      Promise.allSettled([
        this.getData("friend"),
        this.getData("enemy"),
      ]).then(results => {
        if (results[0].status === "fulfilled") {
          this.frienddata = results[0].value;
        } else {
          console.error('Friend data retrieval failed', results[0].reason);
        }
        
        if (results[1].status === "fulfilled") {
          this.enemydata = results[1].value;
        } else {
          console.error('Enemy data retrieval failed', results[1].reason);
        }
      });
    },
    sortTableFriend(key) {
      if (key === this.friendSortKey) {
        this.friendSortDir = this.friendSortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.friendSortDir = 'desc';
      }
      this.friendSortKey = key;
    },
    sortTableEnemy(key) {
      if (key === this.enemySortKey) {
        this.enemySortDir = this.enemySortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.enemySortDir = 'desc';
      }
      this.enemySortKey = key;
    },
  }
}
</script>