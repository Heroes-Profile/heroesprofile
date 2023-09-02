<template>
  <div>
    <h1>Global Talent Statistics</h1>
    <infobox :input="infoText"></infobox>


    <div v-if="!selectedHero">
      <div v-for="hero in heroes" :key="hero.id">
        <hero-box :hero="hero" @click="clickedHero(hero)"></hero-box>
      </div>
    </div>

    <div v-else>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :gametypedefault="gametypedefault"
        :includetimeframetype="true"
        :includetimeframe="true"
        :includeregion="true"
        :includestatfilter="true"
        :includeherolevel="true"
        :includegametype="true"
        :includegamemap="true"
        :includeplayerrank="true"
        :includeherorank="true"
        :includerolerank="true"
        :includemirror="true"
        >
      </filters>



    <div id="level_one">
      <table class="min-w-full bg-white">
        <thead>
          <tr>
            <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Talent
            </th>
            <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Rate
            </th>
            <th @click="sortTable('popularity')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Popularity
            </th>                
            <th @click="sortTable('games_played')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games Played
            </th>                                 
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in sortedData" :key="row.hero_id">
            <td class="py-2 px-3 border-b border-gray-200"><hero-box :hero="row"></hero-box>{{ row.name }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ "&#177;" }}{{ row.confidence_interval }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.win_rate_change }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.popularity }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.pick_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.ban_rate }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.influence }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ row.games_played }}</td>
            <td v-if="this.showStatTypeColumn" class="py-2 px-3 border-b border-gray-200">{{ row.total_filter_type }}</td>
            <td class="py-2 px-3 border-b border-gray-200">{{ "View Talent Builds" }}</td>
          </tr>
        </tbody>
      </table>
    </div>




    </div>
  </div>
</template>

<script>
export default {
  name: 'GlobalTalentsStats',
  components: {
  },
  props: {
    filters: Object,
    inputhero: Object,
    heroes: Array,
    gametypedefault: Array,
  },
  data(){
    return {
    	infoText: "Talents",
      selectedHero: null,
      talentdetaildata: null,
      talentbuildData: null,
    }
  },
  created(){    
    if(this.inputhero){
      this.selectedHero = this.inputhero;
      this.getTalentData();
      this.getTalentBuildData();
    }
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
    clickedHero(hero) {
      this.selectedHero = hero;
      let currentPath = window.location.pathname;
      history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      this.getTalentData();
      this.getTalentBuildData();
    },
  	async getTalentData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents", {
          hero: this.selectedHero.name,
        });

        console.log(response.data["1"].talentInfo);
      }catch(error){
        console.log(error);
      }
    },
    async getTalentBuildData(){
      try{
        const response = await this.$axios.post("/api/v1/global/talents/build", {
          hero: this.selectedHero.name,
        });

        console.log(response.data);
      }catch(error){
        console.log(error);
      }
    },
    filterData(filteredData){
    },
  }
}
</script>