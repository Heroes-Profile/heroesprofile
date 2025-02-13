<template>
  <div>
    
    <page-heading :infoText1="'See below the tournaments for filterable list of replays'" :heading-image="headingImage" :heading-image-url="headingImageUrl"></page-heading>

    <div>
      <esports-organizations :data="hgcSeries" :esport="'Other'"></esports-organizations>
      <esports-organizations :data="withoutHGCSeries" :esport="'Other'"></esports-organizations>
    </div>

    <div class="flex flex-wrap gap-2 max-w-[1500px] justify-center mx-auto items-center mb-10">
      <single-select-filter :values="seriesvalues" :text="'Series'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="regions" :text="'Regions'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="tournaments" :text="'Tournaments'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="seasons" :text="'Seasons'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="teams" :text="'Teams'" @input-changed="handleInputChange"></single-select-filter>
      <single-select-filter :values="filters.game_maps" :text="'Map'" @input-changed="handleInputChange"></single-select-filter>

      <input type="text" class="form-control variable-text rounded-l p-2" :placeholder="'Search for player'" :aria-label="'filter'" aria-describedby="basic-addon2" v-model="userinput" @keyup.enter="filter()">

      <custom-button :disabled="isLoading"  @click="filter()" :text="'Filter'" :size="'medium'" class="bg-teal rounded text-white ml-10 px-4 py-2 mt-auto mb-2 hover:bg-lteal" :ignoreclick="true"></custom-button>

    </div>

    <div v-if="data">
      <div>
        <ul class="pagination flex max-w-[1500px] mx-auto px-2 justify-between mb-2 text-sm">
          <li v-if="data.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !data.prev_page_url }">
            <a class="page-link" @click.prevent="getMatches(data.current_page - 1)" href="#">
              Previous
            </a>
          </li>
          <li v-if="data.current_page != data.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !data.next_page_url }">
            <a class="page-link" @click.prevent="getMatches(data.current_page + 1)" href="#">
              Next
            </a>
          </li>
        </ul>
      </div>
      <div id="table-container" ref="tablecontainer" class="w-auto  overflow-hidden w-[25vw]   2xl:mx-auto  " style=" ">
        <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
          <thead>
            <tr>
              <th @click="sortTable('replayID')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Game ID
              </th>    
              <th @click="sortTable('series')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Series
              </th>   
              <th @click="sortTable('region')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Region
              </th>  
              <th @click="sortTable('tournament')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Tournament
              </th>  
              <th @click="sortTable('season')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Season
              </th>  
              <th @click="sortTable('team_0_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Team 1
              </th>  
              <th @click="sortTable('team_1_name')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Team 2
              </th> 
              <th @click="sortTable('game_map')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Map
              </th> 
              <th @click="sortTable('game_date')" class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
                Game Date
              </th> 
            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in sortedData">
              <tr>
                <td>
                  <a class="link" :href="`./Other/${row.series}/Match/Single/${row.replayID}`" >{{ row.replayID }}</a>
                </td>
                <td>
                  {{ row.series }}
                </td>
                <td>
                  {{ getRegionNameByCode(row.region) }}
                </td>
                <td>
                  {{ row.tournament }}
                </td>
                <td>
                  {{ row.season }}
                </td>
                <td>
                  {{ row.team_1_name }}
                </td>
                <td>
                  {{ row.team_0_name }}
                </td>
                <td>
                  {{ row.game_map }}
                </td>
                <td>
                  <format-date :input="row.game_date"><</format-date>
                </td>
              </tr>
              
            </template>
          </tbody>
        </table>
      </div>

      <div>
        <ul class="pagination flex max-w-[1500px] mx-auto px-2 justify-between mb-2 text-sm">
          <li v-if="data.current_page != 1" class="page-item underline underline-offset-4 mr-auto" :class="{ disabled: !data.prev_page_url }">
            <a class="page-link" @click.prevent="getMatches(data.current_page - 1)" href="#">
              Previous
            </a>
          </li>
          <li v-if="data.current_page != data.last_page" class="page-item underline underline-offset-4 ml-auto" :class="{ disabled: !data.next_page_url }">
            <a class="page-link" @click.prevent="getMatches(data.current_page + 1)" href="#">
              Next
            </a>
          </li>
        </ul>
      </div>


    </div>


    
    <div v-if="isLoading">
      <loading-component></loading-component>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OtherMain',
  components: {
  },
  props: {
    filters: {
      type: Object,
      required: true
    },
    series: Array,
    seasons: Array,
    regions: Array,
    tournaments: Array,
    teams: Array,
  },
  data(){
    return {
      isLoading: false,
      data: null,
      sortKey: '',
      sortDir: 'desc',

      inputseries: null,
      region: null,
      tournament: null,
      season: null,
      team: null,
      map: null,
      userinput: null,
    }
  },
  computed: {
    hgcSeries() {
      return this.series.filter(item => item.name === "HGC");
    },
    withoutHGCSeries() {
      return this.series.filter(item => item.name !== "HGC");
    },
    seriesvalues() {
      return this.series.map(seriesItem => ({
        code: seriesItem.series_id,
        name: seriesItem.name
      }));
    },
    sortedData() {
      if (!this.sortKey) return this.data.data;
      return this.data.data.slice().sort((a, b) => {
        const valA = a[this.sortKey];
        const valB = b[this.sortKey];
        if (this.sortDir === 'asc') {
          return valA < valB ? -1 : 1;
        } else {
          return valA > valB ? -1 : 1;
        }
      });
    }
  },
  mounted() {
    this.getMatches(1);
  },
  methods: {
    async getMatches(page){
      this.data = null;
      this.isLoading = true;
      try{
        const response = await this.$axios.post(`/api/v1/esports/other/get/all/matches`, {
          series: this.inputseries,
          region: this.region,
          tournament: this.tournament,
          season: this.season,
          team: this.team,
          map: this.map,
          userinput: this.userinput,
          pagination_page: page,
        }, 
        {
        });

        this.data = response.data;

      }catch(error){
        //Do something here
      }finally {
        this.isLoading = false;
      }
    },
    sortTable(key) {
      if (key === this.sortKey) {
        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortDir = 'desc';
      }
      this.sortKey = key;
    },
    
    handleInputChange(eventPayload) {
      if(eventPayload.field == "Series"){
        this.inputseries = eventPayload.value;
      }else if(eventPayload.field == "Regions"){
        this.region = eventPayload.value;
      }else if(eventPayload.field == "Tournaments"){
        this.tournament = eventPayload.value;
      }else if(eventPayload.field == "Seasons"){
        this.season = eventPayload.value;
      }else if(eventPayload.field == "Teams"){
        this.team = eventPayload.value;
      }else if(eventPayload.field == "Map"){
        this.map = eventPayload.value;
      }
    },

    filter(){
      this.getMatches(1);
    },
    getRegionNameByCode(code) {
      const region = this.regions.find(region => region.code === code);
      return region ? region.name : null;
    }

  },
  
}
</script>



