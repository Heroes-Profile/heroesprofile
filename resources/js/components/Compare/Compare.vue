<template>
  <div>

    <page-heading :infoText1="infoText" :heading="selectedHero ? selectedHero.name + ' Compare' : 'Compare'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <filters 
        :onFilter="filterData" 
        :filters="filters" 
        :isLoading="isLoading"
        :gametypedefault="gametypedefault"
        :includeherorole="true"
        :includehero="true"
        :includesinglegamemap="true"
        :includesinglegametype="true"
        :includeseasonwithall="true"
        :defaultHero="selectedHero.id"
        :defaultSeason="season"
        >
      </filters>
      <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

      <div class="flex justify-center gap-2  mx-auto  ">
        <div class="border  rounded-md compare-selection-box"  v-for="index in range" :key="index">

          <div v-if="playerChosen(index)">
            <div class=" p-2 rounded-t-md compare-selection-title">
              {{ playerData[index].battletag_short }}({{ playerData[index].region_name }})
            </div>
            <div v-if="data">
              Games Played: {{ data[playerData[index].battletag].games_played }}
              <stat-bar-box class="w-full" size="full" :title="'Win Rate'" :value="data[playerData[index].battletag].win_rate.toFixed(2)" color="blue"></stat-bar-box>         
            </div>
          </div>
        
          
          <div v-else class="p-2 mx-auto">
            <hero-or-league-choice-box :index="index" @onDataReturn="handleDataReturn" @data-loading="isLoading = true" @data-loading-finished="isLoading = false"></hero-or-league-choice-box>
          </div>
        </div>
        <div class="border p-2 flex flex-col rounded-md" v-if="this.range.length < 5" @click="newPlayerAddded">
          <custom-button class="my-auto mx-auto text-xl"   text="+" alt="Change to a plus sign with text 'Add New Player to Compare'" size="small" :ignoreclick="true"></custom-button>
          Add a player to compare
        </div>
      </div>

      <div v-if="data && !isLoading">
        <div class="flex justify-center gap-10 mt-10">
          <div v-if="this.data" class="flex flex-col gap-10">
            <div v-for="(stat, index) in stats.slice(0,4)" :key="stat" class="">
              <div v-for="(player, playerIndex) in playerData" :key="player.battletag" class="flex flex-col" >
                <stat-bar-box align="right"  :color="colors[playerIndex]"  :title="`${player.battletag_short} ${stat}`" :displaytext="getStatText(stat.replace(/ /g, '_').toLowerCase(), player.battletag)" :value="getStatValue(stat.replace(/ /g, '_').toLowerCase(), player.battletag)"></stat-bar-box>
             </div>
           </div>
          </div>
          <img :src="getHeroImage()" />
          <div v-if="this.data" class="flex flex-col gap-10">
            <div v-for="(stat, index) in stats.slice(4)" :key="stat" class="">
              <div v-for="(player, playerIndex) in playerData" :key="player.battletag" class="flex flex-col" >
                <stat-bar-box align="left"  :color="colors[playerIndex]"  :title="`${player.battletag_short} ${stat}`" :displaytext="getStatText(stat.replace(/ /g, '_').toLowerCase(), player.battletag)" :value="getStatValue(stat.replace(/ /g, '_').toLowerCase(), player.battletag)"></stat-bar-box>
              </div>
            </div>
          </div>
        </div>
        <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

        <table v-for="(section, sectionIndex) in sections" :key="sectionIndex" class="table-fixed">
          <thead>
            <tr>
              <td  class="teal"></td>

              <td
                v-for="(player, index) in data"
                :key="index"
                width="25%"
                >
                <a :href="`/Player/${player.battletag_short}/${player.blizz_id}/${player.region}`">{{ player.battletag_short }}</a>
              </td>

            </tr>
          </thead>
          <tbody>

            <tr v-for="(row, rowIndex) in section.rows" :key="rowIndex">
              <td class="flex-1">{{ row.label }}</td>
              <td class="flex-1" v-for="(player, playerIndex) in data" :key="playerIndex">{{ formatValue(player.averages[row.key].avg_value) }}</td>
            </tr>
            
          </tbody>
        </table>
      </div>

      <div v-else-if="isLoading || overrideloading">
        <loading-component @cancel-request="cancelAxiosRequest"></loading-component>
      </div>
    </div>
</div>
</template>

<script>
  export default {
    name: 'Compare',
    components: {
    },
    props: {
      heroes: Array,
      filters: {
        type: Object,
        required: true
      },
      gametypedefault: Array,
      inputhero: Object,
      gametypedefault: Array,
      patreonUser: Boolean,
    },
    data(){
      return {
        cancelTokenSource: null,
        isLoading: false,
        overrideloading: false,
        range: [0],
        playerData: [],
        data: null,
        infoText: "Choose players using the boxes below to see a detailed stat comparison between multiple players. You can also choose to compare different league tiers. Use the filters above to refine your comparison.",
        selectedHero: null,
        gametype: null,
        season: null,
        gamemap: null,
        colors: ['blue', 'teal', 'red', 'yellow'],

        stats: [
          'Takedowns',
          'Deaths',
          'Experience Contribution',
          'Siege Damage',
          'Hero Damage',
          'Healing',
          'Damage Taken',
          ],
        sections: [
        {
          title: 'Combat',
          rows: [
            { label: 'Kills', key: 'kills' },
            { label: 'Assists', key: 'assists' },
            { label: 'Takedowns', key: 'takedowns' },
            { label: 'Deaths', key: 'deaths' },
            ],
        },
        {
          title: 'Player',
          rows: [
            { label: 'Regeneration Globes', key: 'regen_globes' },
            { label: 'Hero Damage', key: 'hero_damage' },
            { label: 'Physical Damage Done', key: 'physical_damage' },
            { label: 'Spell Damage Done', key: 'spell_damage' },
            { label: 'Damage Taken', key: 'damage_taken' },
            { label: 'Time Spent Dead', key: 'time_spent_dead' },
            { label: 'Enemy Silence Duration', key: 'silencing_enemies' },
            { label: 'Enemy Rooted Duration', key: 'rooting_enemies' },
            { label: 'Enemy Stunned Duration', key: 'stunning_enemies' },
            { label: 'Escapes', key: 'escapes' },
            { label: 'Vengeances', key: 'vengeance' },
            { label: 'Outnumbered Deaths', key: 'outnumbered_deaths' },
            ],
        },
        {
          title: 'Siege',
          rows: [
            { label: 'Siege Damage', key: 'siege_damage' },
            { label: 'Structure Damage', key: 'structure_damage' },
            { label: 'Minion Damage', key: 'minion_damage' },
            { label: 'Lane Merc. Damage', key: 'creep_damage' },
            { label: 'Summon Damage', key: 'summon_damage' },
            ],
        },
        {
          title: 'Macro',
          rows: [
            { label: 'Experience Contribution', key: 'experience_contribution' },
            { label: 'Merc. Camp Captures', key: 'merc_camp_captures' },
            { label: 'Watch Tower Captures', key: 'watch_tower_captures' },
            { label: 'Team Exp.', key: 'meta_experience' },
            ],
        },
        {
          title: 'Team Fight',
          rows: [
            { label: 'Teamfight Damage Taken', key: 'teamfight_damage_taken' },
            { label: 'Teamfight Hero Damage', key: 'teamfight_hero_damage' },
            { label: 'Teamfight Escapes', key: 'teamfight_escapes' },
            { label: 'Teamfight Healing', key: 'teamfight_healing' },
            ],
        },
        {
          title: 'Defense/Healing',
          rows: [
            { label: 'Healing', key: 'healing' },
            { label: 'Self Healing', key: 'self_healing' },
            { label: 'Clutch Heals', key: 'clutch_heals' },
            { label: 'Ally Protection', key: 'protection_allies' },
            { label: 'Crowd Control Enemies', key: 'time_cc_enemy_heroes' },
            ],
        },
        {
          title: 'Other',
          rows: [
            { label: 'Town Kills', key: 'town_kills' },
            { label: 'Kill Streak', key: 'highest_kill_streak' },
            { label: 'Multikills', key: 'multikill' },
            ],
        },
        ],
      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.gametype = this.gametypedefault;
      this.season = "All";
    },
    mounted() {
    },
    computed: {
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
          const response = await this.$axios.post("/api/v1/compare", {
            hero: this.selectedHero.name, 
            game_type: this.gametype, 
            season: this.season, 
            game_map: this.gamemap, 
            player1: this.playerData[0] ? this.playerData[0] : null,
            player2: this.playerData[1] ? this.playerData[1] : null,
            player3: this.playerData[2] ? this.playerData[2] : null,
            player4: this.playerData[3] ? this.playerData[3] : null,
            player5: this.playerData[4] ? this.playerData[4] : null,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });

          this.data = response.data;
        }catch(error){
        //Do something here
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.overrideloading = false;
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        this.charttype = filteredData.single["Chart Type"] ? filteredData.single["Chart Type"] : "Account Level";
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : [];
      },
      handleDataReturn(index, payload) {
        this.playerData[index] = payload;
        this.data = null;
        this.overrideloading = true;
        this.getData();
      },
      newPlayerAddded(){
        this.range.push(this.range.length);
      },
      playerChosen(index) {
        if (index in this.playerData) {
          return true;
        }
        return false;
      },
      clickedHero(hero) {
        this.selectedHero = hero;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);
      },
      getHeroImage(){
        return `/images/heroes_rectangle_large/${this.selectedHero.short_name}.jpg`;
      },
      getStatValue(stat, battletag){
        const statValue = this.data[battletag].averages[stat];

        if (typeof statValue === "undefined") {
          return 0;
        }
        return this.data[battletag].averages[stat].scaled_value;
      },
      getStatText(stat, battletag){
        const statValue = this.data[battletag].averages[stat];

        if (typeof statValue === "undefined") {
          return 0;
        }
        return this.data[battletag].averages[stat].avg_value.toFixed(2).toLocaleString('en-US');
      },
      formatValue(value){
        return value ? value.toLocaleString('en-US') : 0;
      }
    }
  }
</script>