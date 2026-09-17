<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Awards'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

    <filters
      :onFilter="filterData"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :includegametypefull="true"
      :includehero="true"
      :includerole="true"
      :includegamemap="true"
      :includemultiseason="true"
      :includegamedaterange="true"
      :hideadvancedfilteringbutton="true"
      >
    </filters>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data" class="max-w-[1500px] mx-auto px-4 max-md:px-2">
      <p class="text-center mb-4">Awards across {{ data.total_games.toLocaleString('en-US') }} games</p>

      <div class="flex justify-center mb-4">
        <input type="text" v-model="awardSearch" placeholder="Search awards" class="md:w-[300px] w-full h-[40px] border-solid border-[1px] border-white bg-blue p-2 text-white focus:outline-none" />
      </div>

      <div class="flex flex-wrap justify-center gap-4 mb-10">
        <p v-if="filteredAwards.length == 0">No awards match "{{ awardSearch }}".</p>
        <div v-for="award in filteredAwards" :key="award.award_id" @click="selectAward(award)" :class="['bg-gray-dark rounded p-4 w-[12em] max-md:w-[45%] flex flex-col items-center text-center cursor-pointer border-2 hover:border-lteal', selectedAward && selectedAward.award_id == award.award_id ? 'border-teal' : 'border-gray-dark']">
          <img :src="`/images/awards/${award.icon}_blue.png`" :alt="award.title" class="w-14 h-14" />
          <span class="mt-2">{{ award.title }}</span>
          <span v-if="award.description" class="text-xs opacity-75 mb-1">{{ award.description }}</span>
          <span class="text-2xl font-bold">{{ award.rate.toFixed(2) }}%</span>
          <span class="text-sm">{{ award.count.toLocaleString('en-US') }} games</span>
        </div>
      </div>

      <div ref="gamestable" class="flex items-center gap-4 mb-2 scroll-mt-4">
        <h2 class="text-xl flex items-center gap-2">
          <img v-if="selectedAward" :src="`/images/awards/${selectedAward.icon}_blue.png`" :alt="selectedAward.title" class="w-10 h-10" />
          {{ selectedAward ? selectedAward.title + ' Games' : 'Recent Awards' }}
        </h2>
        <custom-button v-if="selectedAward" @click="clearAward" :text="'Show Recent Awards'" :alt="'Show Recent Awards'" size="small" :ignoreclick="true"></custom-button>
      </div>

      <template v-if="selectedAward">
        <div v-if="awardGamesLoading">
          <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
        </div>
        <template v-else-if="awardGames">
          <p v-if="awardGames.data.length == 0">No {{ selectedAward.title }} games for the selected filters.</p>
          <ul v-if="awardGames.last_page > 1" class="pagination flex justify-between mb-2 text-sm">
            <li v-if="awardGames.current_page != 1" class="page-item underline underline-offset-4 mr-auto">
              <a class="page-link" @click.prevent="getAwardGames(awardGames.current_page - 1)" href="#">Previous</a>
            </li>
            <li v-if="awardGames.current_page != awardGames.last_page" class="page-item underline underline-offset-4 ml-auto">
              <a class="page-link" @click.prevent="getAwardGames(awardGames.current_page + 1)" href="#">Next</a>
            </li>
          </ul>
        </template>
      </template>

      <p v-if="!selectedAward && data.recent_awards.length == 0">No awards for the selected filters.</p>
      <table v-if="tableRows.length" class="min-w-0 w-full responsive-table max-md:text-xs">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game ID</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Date</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Type</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Game Map</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Hero</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Award</th>
            <th class="py-2 px-3 text-left text-sm leading-4 text-gray-500 tracking-wider">Winner</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in tableRows" :key="row.replayID">
            <td><a class="link" :href="'/Match/Single/' + row.replayID">{{ row.replayID }}</a></td>
            <td>{{ formatDate(row.game_date) }}</td>
            <td>{{ row.game_type.name }}</td>
            <td>{{ row.game_map.name }}</td>
            <td>
              <div class="flex items-center gap-1">
                <hero-image-wrapper :hero="row.hero" :mobileClick="true"></hero-image-wrapper><span class="max-md:hidden">{{ row.hero.name }}</span>
              </div>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <img :src="`/images/awards/${row.award.icon}_${row.winner == 1 ? 'blue' : 'red'}.png`" :alt="row.award.title" class="w-8 h-8" /><span class="max-md:hidden">{{ row.award.title }}</span>
              </div>
            </td>
            <td>{{ row.winner == 1 ? 'True' : 'False' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="asyncLoading">
      <loading-component :textoverride="true">This is taking longer than expected.<br/>A background task is computing your results.<br/>Please be patient.</loading-component>
    </div>
    <div v-else-if="isLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
  </div>
</template>

<script>
  import moment from 'moment-timezone';

  export default {
    name: 'PlayerAwards',
    components: {
    },
    props: {
      filters: Object,
      playerloadsetting: {
        type: [String, Boolean]
      },
      battletag: String,
      blizzid: String,
      region: String,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      regionsmap: Object,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        asyncLoading: false,
        infoText: "How often " + this.battletag + " earns each end of match award. Awards are only tracked for games after award tracking began.",
        data: null,
        gametype: null,
        hero: null,
        role: null,
        gamemap: null,
        season: null,
        startdate: null,
        enddate: null,
        awardSearch: '',
        selectedAward: null,
        awardGames: null,
        awardGamesLoading: false,
        awardGamesRequestId: 0,
      }
    },
    created(){
      this.gametype = this.gametypedefault;
    },
    mounted() {
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
      filteredAwards(){
        const search = this.awardSearch.trim().toLowerCase();
        if(!search){
          return this.data.awards;
        }
        return this.data.awards.filter(award => award.title.toLowerCase().includes(search) || (award.description || '').toLowerCase().includes(search));
      },
      tableRows(){
        if(this.selectedAward){
          return this.awardGames && !this.awardGamesLoading ? this.awardGames.data : [];
        }
        return this.data.recent_awards;
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
      async getData(){
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$globalAsyncPost("/api/v1/player/awards", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            hero: this.hero,
            role: this.role,
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
        }
      },
      async getAwardGames(page){
        if (this.awardGamesLoading || page < 1 || (this.awardGames && page > this.awardGames.last_page)) {
          return;
        }

        this.awardGamesLoading = true;
        // Bumped whenever the selected award changes, so a slower response for the previous award is dropped.
        const requestId = this.awardGamesRequestId;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/player/awards/games", {
            battletag: this.battletag,
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            hero: this.hero,
            role: this.role,
            game_map: this.gamemap,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
            award_id: this.selectedAward.award_id,
            pagination_page: page,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });

          if (requestId !== this.awardGamesRequestId) return;
          this.awardGames = response.data;
        }catch(error){
          //Do something here
        }finally {
          if (requestId === this.awardGamesRequestId) {
            this.cancelTokenSource = null;
            this.awardGamesLoading = false;
          }
        }
      },
      selectAward(award){
        if(this.selectedAward && this.selectedAward.award_id == award.award_id){
          this.clearAward();
          return;
        }
        this.selectedAward = award;
        this.awardGames = null;
        this.awardGamesRequestId++;
        this.awardGamesLoading = false;
        this.getAwardGames(1);
        this.$nextTick(() => {
          this.$refs.gamestable?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      },
      clearAward(){
        this.selectedAward = null;
        this.awardGames = null;
        this.awardGamesRequestId++;
        this.awardGamesLoading = false;
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
        this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
        this.role = filteredData.single.Role ? filteredData.single.Role : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
        this.startdate = filteredData.single["From Date"] || null;
        this.enddate = filteredData.single["To Date"] || null;

        this.data = null;
        this.clearAward();
        this.getData();
      },
      formatDate(dateString) {
        const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik');
        const localDate = originalDate.clone().tz(moment.tz.guess());

        return localDate.format('MM/DD/YYYY h:mm:ss a');
      },
    }
  }
</script>
