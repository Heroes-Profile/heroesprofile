<template>
  <div>
        Lets rethink how filtering is done.
    <h1>Leaderboard</h1>
    <infobox :input="infoText1"></infobox>
    <infobox :input="infoText2"></infobox>

    <filters 
      :onFilter="filterData" 
      :filters="filters" 
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

    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th @click="sortTable('rank')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Rank
          </th>
          <th @click="sortTable('battletag')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Battletag
          </th>
          <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Region
          </th>
          <th @click="sortTable('rating')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Win Rate %
          </th>       
          <th @click="sortTable('conservative_rating')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Heroes Profile Rating
          </th>   
          <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Player MMR
          </th>   
          <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Tier
          </th>   
          <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games Played
          </th>   
          <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Most Played Hero (Games Played with Hero)
          </th>           
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in sortedData" :key="row.rank">
          <td class="py-2 px-3 border-b border-gray-200">{{ row.rank }}</td>
          <td class="py-2 px-3 border-b border-gray-200"><a :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`" target="_blank">{{ row.battletag }}</a></td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.region }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.rating }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.conservative_rating }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.tier }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
          <td class="py-2 px-3 border-b border-gray-200"><hero-box-small :hero="row.most_played_hero"></hero-box-small>{{ row.hero_build_games_played }}</td>
        </tr>
      </tbody>
    </table>
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
      leaderboardtype: "Player",
      groupsize: "Solo",
      gametype: null,
      season: null,
      data: null,
      sortKey: '',
      sortDir: 'asc',
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
        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },

    filterData(filteredData){
      this.leaderboardtype = filteredData.single["Leaderboard Type"] ? filteredData.single["Leaderboard Type"] : this.leaderboardtype;
      this.groupsize = filteredData.single["Group Size"] ? filteredData.single["Group Size"] : this.groupsize;
      this.gametype = filteredData.single["Game Type"] ? filteredData.single["Game Type"] : this.gametype;
      this.season = filteredData.single["Season"] ? filteredData.single["Season"] : this.season;


      this.getData();
    },

    getTier(){
      return "Bad";
    },
  }
}
</script>