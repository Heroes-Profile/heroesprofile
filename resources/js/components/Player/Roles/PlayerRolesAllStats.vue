<template>
  <div>
    All Heroes
    as played by
    <span><a :href="`/Player/${battletag}/${blizzid}/${region}`" target="_blank">{{ battletag }}</a></span>

    <infobox :input="infoText"></infobox>

    <filters 
    :onFilter="filterData" 
    :filters="filters" 
    :isLoading="isLoading"
    :gametypedefault="gametype"
    :minimumgamesdefault="'0'"
    :includehero="true"
    :includerole="true"
    :includegametypefull="true"
    :includeseason="true"
    :includeminimumgames="true"

    >
  </filters>



  <div v-if="data">
    <div>
      <input type="text" v-model="searchQuery" placeholder="Search..." />

      <div v-for="(stat, index) in filteredStats" :key="index" class="flex items-center">
        <input type="checkbox" v-model="stat.selected" :disabled="isDisabled(stat)" @change="onCheckboxChange(stat)" />
        <label>{{ stat.name }}</label>
      </div>
    </div>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th @click="sortTable('name')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Role
          </th>    
          <th @click="sortTable('win_rate')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            Games
          </th>
          <th @click="sortTable('kda')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            KDA
          </th>  
          <th @click="sortTable('kdr')" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
            KDR
          </th>
          <template   v-for="stat in stats">
            <th  v-if="stat.selected" @click="sortTable(stat.value)" class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ stat.name }}
            </th>
          </template> 
          
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in sortedData" :key="row.id">
          <td class="py-2 px-3 border-b border-gray-200"><a :href="getPlayerRolePageUrl(row.name)"><role-box :role="row.name"></role-box>{{ row.name }}</a></td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.wins }}|{{ row.losses }} {{ row.win_rate }} %</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.kda }} <br>{{ row.avg_kills }}/{{ row.avg_deaths }}/{{ row.avg_assists }}</td>
          <td class="py-2 px-3 border-b border-gray-200">{{ row.kdr }} <br>{{ row.avg_kills }}/{{ row.avg_deaths }}</td>
          
          <template v-for="stat in stats" >
            <td 
            v-if="stat.selected" 
            :class="{ flash: stat.flash }"
            class="py-2 px-3 border-b border-gray-200">
            {{ row[stat.value] }}
          </td>
        </template>

        
      </tr>
    </tbody>
  </table>

</div>
<div v-else>
  <loading-component :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
</div>

</div>
</template>

<script>
  export default {
    name: 'PlayerRolesAllStats',
    components: {
    },
    props: {
      filters: {
        type: Object,
        required: true
      },
      battletag: String,
      blizzid: String, 
      region: String,
    },
    data(){
      return {
        isLoading: false,
        infoText: "Select a hero below to view detailed stats for that hero. Use the search box above to filter the list of heroes. Or scroll down to the advanced section for table view.",
        gametype: ["qm", "ud", "hl", "tl", "sl", "ar"],
        data: null,
        sortKey: '',
        sortDir: 'desc',
        role: null,
        hero: null,
        minimumgames: 0,
        searchQuery: '',
        stats: [
          { name: "Avg Assists", value: 'avg_assists', selected: false, flash: false},
          { name: "Avg Clutch Heals", value: 'avg_clutch_heals', selected: false, flash: false},
          { name: "Avg Creep Damage", value: 'avg_creep_damage', selected: false, flash: false},
          { name: "Avg Damage Taken", value: 'avg_damage_taken', selected: false, flash: false},
          { name: "Avg Deaths", value: 'avg_deaths', selected: false, flash: false},
          { name: "Avg Escapes", value: 'avg_escapes', selected: false, flash: false},
          { name: "Avg Experience Contribution", value: 'avg_experience_contribution', selected: false, flash: false},
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
          { name: "Avg Teamfight Escapes", value: 'avg_teamfight_escapes', selected: false, flash: false},
          { name: "Avg Teamfight Healing", value: 'avg_teamfight_healing', selected: false, flash: false},
          { name: "Avg Teamfight Hero Damage", value: 'avg_teamfight_hero_damage', selected: false, flash: false},
          { name: "Avg Time CC Enemy Heroes", value: 'avg_time_cc_enemy_heroes', selected: false, flash: false},
          { name: "Avg Time on Fire", value: 'avg_time_on_fire', selected: false, flash: false},
          { name: "Avg Time Spent Dead", value: 'avg_time_spent_dead', selected: false, flash: false},
          { name: "Avg Town Kills", value: 'avg_town_kills', selected: false, flash: false},
          { name: "Avg Vengeance", value: 'avg_vengeance', selected: false, flash: false},
          { name: "Avg Watch Tower Captures", value: 'avg_watch_tower_captures', selected: false, flash: false},
          { name: "Avg Total Healing", value: 'combined_healing', selected: true, flash: false},
          { name: "Hero", value: 'hero', selected: false, flash: false},
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
},
mounted() {
  this.getData();
},
computed: {
  filteredStats() {
    return this.stats.filter(stat => stat.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
  },
  selectedStatsCount() {
    return this.stats.filter(stat => stat.selected).length;
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
},
watch: {
},
methods: {
  async getData(type){
    this.isLoading = true;
    try{
      const response = await this.$axios.post("/api/v1/player/roles/all", {
        blizz_id: this.blizzid,
        region: this.region,
        game_type: this.gametype,
        hero: this.hero,
        role: this.role,
        minimumgames: this.minimumgames,
        type: "all",
        page: "role",
      });

      this.data = response.data;
    }catch(error){
      //Do something here
    }
    this.isLoading = false;
  },
  filterData(filteredData){
    this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : this.gametype;
    this.role = filteredData.single["Role"] ? filteredData.single["Role"] : null;
    this.hero = filteredData.single.Heroes ? filteredData.single.Heroes : null;
    this.minimumgames = filteredData.single["Minimum Games"] ? filteredData.single["Minimum Games"] : 0;
    this.data = [];
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
}
}
</script>

<style>
  .flash-animation {
    animation: flash 0.5s;
  }

  @keyframes flash {
    0% { background-color: transparent; }
    50% { background-color: blue; }
    100% { background-color: transparent; }
  }

  .flash {
    animation: flash 1s;
  }
</style>