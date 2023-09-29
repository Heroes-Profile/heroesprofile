<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="'NGS'" :heading-image="'/images/NGS/600-600-ngs_large_header.png'" :heading-image-url="'https://www.nexusgamingseries.org/'"></page-heading>

    <div v-if="data">
      <h1>Division {{ division }}</h1>
      <img :src="'/images/NGS/Divisions/Division ' + division + '.png'"/>

      <stat-box :title="'Vengeances'" :value="data.vengeances"></stat-box>
      <stat-box :title="'Escapes'" :value="data.escapes"></stat-box>

      <stat-bar-box :title="'Avg. Game Length to 30min'" :value="data.length_to_30"></stat-bar-box>

      <stat-box :title="'Avg. Hero Damage'" :value="data.hero_damage"></stat-box>
      <stat-box :title="'Avg. Siege Damage'" :value="data.siege_damage"></stat-box>
      <stat-box :title="'Avg. Siege Damage'" :value="data.siege_damage"></stat-box>
      <stat-box :title="'Takedowns'" :value="data.takedowns"></stat-box>
      <stat-box :title="'Kills'" :value="data.kills"></stat-box>
      <stat-box :title="'Assists'" :value="data.assists"></stat-box>
      <stat-box :title="'# of Games'" :value="data.total_games"></stat-box>
      <stat-box :title="'Avg. Healing'" :value="data.healing"></stat-box>
      <stat-box :title="'Total Time Spend Dead'" :value="data.time_spent_dead"></stat-box>

      <h1>Teams in Division {{ division }}</h1>
      <div class="flex flex-wrap gap-2">
        <round-image v-for="(item, index) in data.teams" :size="'big'" :image="item.image" :showTooltip="true" :hovertextstyleoverride="true">
          <image-hover-box :title="item.team_name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
        </round-image>
      </div>

      <div class="bg-lighten p-10 ">
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Heroes</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played (5+ games)'" :data="data.hero_top_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate (5+ games)'" :data="data.hero_top_three_highest_win_rate"></group-box>
            <group-box :text="'Lowest Win Rate (5+ games)'" :data="data.hero_top_three_lowest_win_rate"></group-box>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <hero-image-wrapper v-for="(item, index) in data.heroes" :size="'big'" :hero="item.hero">
            <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
          </hero-image-wrapper>
        </div>
      </div>



      <div>
        <div class=" max-w-[90em] ml-auto mr-auto">
          <h2 class="text-3xl font-bold py-5 text-center">Maps</h2>
          <div class="flex flex-wrap justify-center">
            <group-box :text="'Most Played (5+ games)'" :data="data.map_top_three_most_played"></group-box>
            <group-box :text="'Highest Win Rate (5+ games)'" :data="data.map_top_three_highest_win_rate"></group-box>
            <group-box :text="'Lowest Win Rate (5+ games)'" :data="data.map_top_three_lowest_win_rate"></group-box>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <map-image-wrapper v-for="(item, index) in data.maps" :size="'big'" :map="item.map">
            <image-hover-box :title="item.name" :paragraph-one="'Win Rate: ' + item.win_rate" :paragraph-two="'Games Played: ' + (item.wins + item.losses)"></image-hover-box>
          </map-image-wrapper>
        </div>
      </div>



      <div class="p-10 max-w-[90em] ml-auto mr-auto">
        <h2 class="text-3xl font-bold py-5">Most Recent matches</h2>

                 
          <game-summary-box v-for="(item, index) in data.matches" 
            :data="item" 
            :caption="`${item.game_map.name} | Round ${item.round} Game ${item.game} | ${formatDate(item.game_date)}`"  
            :esport="true" 
            :esport-league="'NGS'"
          >
          </game-summary-box>
        
          <custom-button class="flex justify-end " text="View Match History" :esport="true" :esport-league="'NGS'"></custom-button>

      </div>

    </div>
    <div v-else>
      <loading-component :overrideimage="'/images/NGS/no-image-clipped.png'"></loading-component>
    </div>
  </div>
</template>

<script>
  import moment from 'moment-timezone';

export default {
  name: 'NgsSingleDivision',
  components: {
  },
  props: {
    division: String,
    defaultseason: Number,
    filters: Object,
  },
  data(){
    return {
      preloadedImage: new Image(),
      isLoading: false,
      data: null,
      infoText1: "Heroes of the Storm statistics and comparison for the Nexus Gaming Series",
      season: null,
    }
  },
  created(){
    this.preloadedImage.src = '/images/NGS/no-image-clipped.png';
    this.season = this.defaultseason;
  },
  mounted() {
    this.getData();
  },
  computed: {
  },
  watch: {
  },
  methods: {    
    async getData(){
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/esports/ngs/division/single", {
          season: this.season,
          division: this.division,
        });
        this.data = response.data;
            console.log(this.data);

      }catch(error){
        //Do something here
      }
      this.isLoading = false;
    },
    formatDate(dateString) {
      const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
      const localDate = originalDate.clone().tz(moment.tz.guess());

      return localDate.format('MM/DD/YYYY h:mm:ss a');
    },
  }
}
</script>