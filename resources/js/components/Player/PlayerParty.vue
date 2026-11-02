<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Party Statistics'"  :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner"></page-heading>

    <filters
      :onFilter="filterData"
      :filters="filters"
      :isLoading="isLoading"
      :gametypedefault="gametype"
      :heroinput="hero"
      :includehero="true"
      :includegamemap="true"
      :includegametypefull="true"
      :includemultiseason="true"
      :includegamedaterange="true"
      :hideadvancedfilteringbutton="true"
      >
    </filters>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="asyncLoading">
      <loading-component :textoverride="true">This is taking longer than expected.<br/>A background task is computing your results.<br/>Please be patient.</loading-component>
    </div>

    <div v-if="partydata" class="max-w-[1500px] mx-auto">
      <party-stats-display :partydata="partydata" teamlabel="Your Team"></party-stats-display>
    </div>
    <div v-else-if="isLoading && !asyncLoading">
      <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
    </div>
    <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
    </div>
  </div>
</template>

<script>
export default {
  name: 'PlayerParty',
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
    urlparameters: [Object, Array],
  },
  data(){
    return {
      dataError: false,
      isLoading: false,
      asyncLoading: false,
      infoText: "Win rates for " + this.battletag + " by party setup, comparing the groups on their team against the groups they played against",
      partydata: null,
      cancelTokenSource: null,

      hero: null,
      gametype: null,
      gamemap: null,
      season: null,
      startdate: null,
      enddate: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;

    // ?hero= by name, from the hero page's party size panel
    const hero = this.filters.heroes.find(item => item.name === this.urlparameters?.hero);
    if (hero) {
      this.hero = hero.code;
    }
  },
  mounted() {
    if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
      this.getData();
    }
  },
  computed: {
    isOwner(){
      if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
        return true;
      }
      return false;
    },
  },
  methods: {
    async getData(){
      this.dataError = false;
      this.isLoading = true;

      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled');
      }
      this.cancelTokenSource = this.$axios.CancelToken.source();

      try {
        const response = await this.$globalAsyncPost(
          "/api/v1/player/party",
          {
            blizz_id: this.blizzid,
            region: this.region,
            game_type: this.gametype,
            season: this.season,
            start_date: this.startdate,
            end_date: this.enddate,
            hero: this.hero,
            game_map: this.gamemap,
          },
          {
            cancelToken: this.cancelTokenSource.token,
            onLoadStatus: (status, meta) => {
              if (meta.phase === 'polling') {
                this.asyncLoading = true;
              }
            },
          }
        );

        if(response.data.status == "failure to validate inputs"){
          throw new Error("Failure to validate inputs");
        }
        this.partydata = response.data;
      } catch(error) {
        this.dataError = true;
      } finally {
        this.cancelTokenSource = null;
        this.isLoading = false;
        this.asyncLoading = false;
      }
    },
    cancelAxiosRequest() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel('Request canceled by user');
      }
    },
    filterData(filteredData){
      this.hero = filteredData.single["Heroes"] ? filteredData.single["Heroes"] : null;
      this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
      this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
      this.season = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
      this.startdate = filteredData.single["From Date"] || null;
      this.enddate = filteredData.single["To Date"] || null;

      this.partydata = null;
      this.getData();
    },
  }
}
</script>
