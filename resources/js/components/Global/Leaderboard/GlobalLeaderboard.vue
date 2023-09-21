<template>
  <div>
    <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="'Leaderboard'"></page-heading>
      
    <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"
      :gametypedefault="gametypedefault"
      :defaultSeason="defaultseason"
      :includeleaderboardtype="true"
      :includegroupsize="true"
      :includesinglegametype="true"
      :includeseason="true"
      :includesingleleaguetier="true"
      :includesingleregion="true"
      :minimumseason="13"
      >
    </filters>
    <div v-if="data">
      <custom-table :columns="columns" :data="data"></custom-table>
    </div>
    <div v-else>
      <loading-component></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalLeaderboard',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    gametypedefault: Array,
    defaultseason: String,
  },
  data(){
    return {
      infoText1: "Leaderboards are a reflection of user uploaded data. Due to replay file corruption or other issues, the data is not always reflective of real player stats. Please keep that in mind when reviewing leaderboards.",
      infoText2: " Only users who upload replays through an approved automatic uploader will be able to rank on the leaderboards. The main uploader can be found at Heroes Profile Uploader while the secondary uploader that works on platforms other than windows can be found at Heroes Profile Electron Uploader. For any questions, please contact zemill@heroesprofile.com ",
      columns: [
        { text: 'Rank', value: 'rank', sortable: true },
        { text: 'Battletag', value: 'battletag', sortable: true },
        { text: 'Region', value: 'region', sortable: true },
        { text: 'Win Rate %', value: 'win_rate', sortable: true },
        { text: 'Heroes Profile Rating', value: 'rating', sortable: true },
        { text: 'Player MMR', value: 'conservative_rating', sortable: true }, 
        { text: 'Tier', value: 'tier', sortable: true }, 
        { text: 'Games Played', value: 'games_played', sortable: true }, 
        { text: 'Most Played Hero (Games Played with Hero)', value: 'most_played_hero', sortable: false }, 
      ],
      leaderboardtype: "Player",
      groupsize: "Solo",
      gametype: null,
      season: null,
      data: null,
    }
  },
  created(){
    this.gametype = this.gametypedefault;
    this.season = this.defaultseason;
    this.getData();
  },
  mounted() {
  },
  computed: {

  },
  watch: {
  },
  methods: {
    async getData(){
      try{
        const response = await this.$axios.post("/api/v1/global/leaderboard", {
          season: this.season, 
          gameType: this.gametype,
          type: this.leaderboardtype.toLowerCase(),
          groupsize: this.groupsize,
        });

        this.data = response.data;
      }catch(error){
        //Do something here
      }
    },

    filterData(filteredData){
      this.leaderboardtype = filteredData.single["Leaderboard Type"] ? filteredData.single["Leaderboard Type"] : this.leaderboardtype;
      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : this.season;


      this.getData();
    },
  }
}
</script>