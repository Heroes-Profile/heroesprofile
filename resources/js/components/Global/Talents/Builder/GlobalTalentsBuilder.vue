<template>
  <div>
    <page-heading :infoText1="infoText1" :infoText2="infoText2" :heading="'Talent Builder'"></page-heading>

    <div v-if="!selectedHero">
      <hero-selection :heroes="heroes"></hero-selection>
    </div>

    <div v-else>
      <filters 
      :onFilter="filterData" 
      :filters="filters" 
      :isLoading="isLoading"

      :timeframetypeinput="timeframetype"
      :timeframeinput="timeframe"
      :gametypeinput="gametype"
      :regioninput="region"
      :herolevelinput="herolevel"
      :gamemapinput="gamemap"
      :playerrankinput="playerrank"
      :herorankinput="herorank"
      :rolerankinput="rolerank"
      :mirrormatchinput="mirrormatch"

      :gametypedefault="gametypedefault"
      :includetimeframetype="true"
      :includetimeframe="true"
      :includeregion="true"
      :includeherolevel="true"
      :includegametype="true"
      :includegamemap="true"
      :includeplayerrank="true"
      :includeherorank="true"
      :includerolerank="true"
      :includemirror="true"
      :advancedfiltering="advancedfiltering"
      >
    </filters>
    <takeover-ad :patreon-user="patreonUser"></takeover-ad>

  </div>


  <div v-if="isLoading">
    <loading-component @cancel-request="cancelAxiosRequest" v-if="determineIfLargeData()" :textoverride="true">Large amount of data.<br/>Please be patient.<br/>Loading Data...</loading-component>
    <loading-component @cancel-request="cancelAxiosRequest" v-else></loading-component>
  </div>
  <div v-else-if="dataError" class="flex items-center justify-center">
      Error: Reload page/filter
  </div>
  <div v-if="data">

    <div class="flex px-3 gap-5 mx-auto justify-center">
      <talent-builder-column :data="data['1']" :level="1" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['4']" :level="4" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['7']" :level="7" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['10']" :level="10" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['13']" :level="13" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['16']" :level="16" :clickedData="clickedData"></talent-builder-column>
      <talent-builder-column :data="data['20']" :level="20" :clickedData="clickedData"></talent-builder-column>


    </div>
    <div class=" my-5 bg-teal py-5 px-2">
      <infobox class="max-w-[1500px] mx-auto " :input="'Build win rate and how many players have played that exact build. Win rate for builds to level 20 are inflated because teams that make it to level 20 win more. Therefore it is not an accurate representation of a builds viabllity. See table below for that calculation'"></infobox>


      <infobox class="max-w-[1500px] mx-auto " :input="'Calculated build win chance tries to gauge what the builds win rate will be at any point during the game.'"></infobox>
    </div>
    <table v-if="builddata" class="">
      <thead>
        <tr>
          <th>
            Talents
          </th>
          <th>
            Copy Build to Game
          </th>
          <th>
            Games Played with Build
          </th>
          <th>
            Win rate
          </th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="flex gap-x-1 mx-2 items-center">
              <talent-image-wrapper v-if="builddata.level_one" :talent="builddata.level_one" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_four" :talent="builddata.level_four" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_seven" :talent="builddata.level_seven" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_ten" :talent="builddata.level_ten" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_thirteen" :talent="builddata.level_thirteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_sixteen" :talent="builddata.level_sixteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper v-if="builddata.level_twenty" :talent="builddata.level_twenty" :size="'medium'"></talent-image-wrapper>
            </div>
          </td>
          <td class="py-2 px-3 ">
            {{ this.getCopyBuildToGame(builddata.level_one, builddata.level_four, builddata.level_seven, builddata.level_ten, builddata.level_thirteen, builddata.level_sixteen, builddata.level_twenty, selectedHero) }}
            <custom-button @click="copyToClipboard(builddata)" text="COPY TO CLIPBOARD" alt="COPY TO CLIPBOARD" size="small" :ignoreclick="true">COPY TO CLIPBOARD</custom-button>
          </td>
          <td>
            {{ builddata.games_played.toLocaleString('en-US') }}
          </td>
          <td>
            {{ builddata.win_rate.toFixed(2) }}
          </td>
        </tr>
      </tbody>
    </table>


    <div class=" my-5 bg-teal py-5 px-2"><infobox class="max-w-[1500px] mx-auto " :input="'Possible Replays do not take into account Hero Level, Hero Rank, Role Rank, or Mirror Match Filter options'"></infobox></div>

    <table class="">
      <thead>
        <tr>
          <th>
            Game ID
          </th>
          <th>
            Map
          </th>
          <th>
            Winner
          </th>
          <th>
            Talents
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in replays" :key="index">
          <td>
            <a class="link" :href="`/Match/Single/${row.replayID}`">{{ row.replayID}}</a>
          </td>
          <td>
            {{ row.name}}
          </td>
          <td>
            {{ row.winner == 0 ? "False" : "True" }}
          </td>
          <td>
            <div class="flex gap-x-1 mx-2 items-center">
              <talent-image-wrapper :talent="row.level_one" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_four" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_seven" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_ten" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_thirteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_sixteen" :size="'medium'"></talent-image-wrapper>
              <talent-image-wrapper :talent="row.level_twenty" :size="'medium'"></talent-image-wrapper>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>


</div>
</template>

<script>
  export default {
    name: 'GlobalTalentsBuilder',
    components: {
    },
    props: {
      filters: Object,
      inputhero: Object,
      heroes: Array,
      gametypedefault: Array,
      defaulttimeframetype: String,
      defaulttimeframe: Array,
      defaultbuildtype: String,
      talentimages: Object,
      advancedfiltering: Boolean,
      patreonUser: Boolean,
      urlparameters: Object,

    },
    data(){
      return {
        dataError: false,
        cancelTokenSource: null,
        isLoading: false,
        selectedHero: null,
        data: null,
        replays: null,
        builddata: null,
        infoText1: "Pick a talent in any tier below to start. The tool will calculate talent win rates dynamically as you change your build choices.",
        infoText2: "Build win chance and win rate, along with games played by the build can be found below the talents.",
        clickedData: {
          1: null,
          4: null,
          7: null,
          10: null,
          13: null,
          16: null,
          20: null,
        },

      //Sending to filter
        timeframetype: null,
        timeframe: null,
        region: null,
        herolevel: null,
        role: null,
        hero: null,
        gametype: null,
        gamemap: null,
        playerrank: null,
        herorank: null,
        rolerank: null,
        mirrormatch: 0,

      }
    },
    created(){
      this.selectedHero = this.inputhero;
      this.timeframe = this.defaulttimeframe;
      this.gametype = this.gametypedefault;
      this.timeframetype = this.defaulttimeframetype;

      if(this.urlparameters){
        this.setURLParameters();
      }
      
      if(this.selectedHero){
        this.getData();
      }
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      async getData(){
        this.dataError = false;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();
      
        this.replays = null;
        try{
          const response = await this.$axios.post("/api/v1/global/talents/builder", {
            hero: this.selectedHero.name,
            selectedtalents: this.clickedData,
            timeframe_type: this.timeframetype,
            timeframe: this.timeframe,
            region: this.region,
            hero_level: this.herolevel,
            game_type: this.gametype,
            game_map: this.gamemap,
            league_tier: this.playerrank,
            hero_league_tier: this.herorank,
            role_league_tier: this.rolerank,
            mirrormatch: this.mirrormatch,
          }, 
          {
            cancelToken: this.cancelTokenSource.token,
          });


          this.data = response.data.talentData;
          this.replays = response.data.replays;
          this.builddata = response.data.buildData;
        }catch(error){
          this.dataError = true;
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      talentClicked(talent, index, level){
        this.clickedData[level] = talent.talent_id;
        this.getData();
      },

      clickedHero(hero) {
        this.selectedHero = hero;
        this.preloadTalentImages(hero);

        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}/${this.selectedHero.name}`);

        this.getData();
      },
      preloadTalentImages(hero) {
        if(hero){
          this.talentimages[hero.name].forEach((image) => {
            const img = new Image();
            img.src = image;
          });
        }
      },
      filterData(filteredData){
        this.timeframetype = filteredData.single["Timeframe Type"] ? filteredData.single["Timeframe Type"] : this.timeframetype;
        this.timeframe = filteredData.multi.Timeframes ? Array.from(filteredData.multi.Timeframes): this.defaulttimeframe;
        this.region = filteredData.multi.Regions ? [...Array.from(filteredData.multi.Regions)] : null;
        this.herolevel = filteredData.multi["Hero Level"] ? Array.from(filteredData.multi["Hero Level"]) : null;
        this.gametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.gamemap = filteredData.multi.Map ? Array.from(filteredData.multi.Map) : null;
        this.playerrank = filteredData.multi["Player Rank"] ? Array.from(filteredData.multi["Player Rank"]) : null;
        this.herorank = filteredData.multi["Hero Rank"] ? Array.from(filteredData.multi["Hero Rank"]) : null;
        this.rolerank = filteredData.multi["Role Rank"] ? Array.from(filteredData.multi["Role Rank"]) : null;
        this.mirrormatch = filteredData.single["Mirror Matches"] ? filteredData.single["Mirror Matches"] : this.mirrormatch;
        this.talentbuildtype = filteredData.single["Talent Build Type"] ? filteredData.single["Talent Build Type"] : this.defaultbuildtype;


        let queryString = `?timeframe_type=${this.timeframetype}`;
        queryString += `&timeframe=${this.timeframe}`;
        queryString += `&game_type=${this.gametype}`;

        if(this.region){
          queryString += `&region=${this.region}`;
        }

        if(this.herolevel){
          queryString += `&hero_level=${this.herolevel}`;
        }

        if(this.gamemap){
          queryString += `&game_map=${this.gamemap}`;
        }

        if(this.playerrank){
          queryString += `&league_tier=${this.convertRankIDtoName(this.playerrank)}`;
        }

        if(this.herorank){
          queryString += `&hero_league_tier=${this.convertRankIDtoName(this.herorank)}`;
        }

        if(this.rolerank){
          queryString += `&role_league_tier=${this.convertRankIDtoName(this.rolerank)}`;
        }

        queryString += `&mirror=${this.mirrormatch}`;

        const currentUrl = window.location.href;
        let currentPath = window.location.pathname;
        history.pushState(null, null, `${currentPath}${queryString}`);


        this.data = null;
        this.replays = null;
        this.builddata = null;
        this.getData();
      },
      getCopyBuildToGame(level_one, level_four, level_seven, level_ten, level_thirteen, level_sixteen, level_twenty, hero) {
        return "[T" + 
        (level_one ? level_one.sort : '0') + 
        (level_four ? level_four.sort : '0') + 
        (level_seven ? level_seven.sort : '0') + 
        (level_ten ? level_ten.sort : '0') + 
        (level_thirteen ? level_thirteen.sort : '0') + 
        (level_sixteen ? level_sixteen.sort : '0') + 
        (level_twenty ? level_twenty.sort : '0') +
        "," + hero.build_copy_name + "]"
      },
      copyToClipboard(row) {
        const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, this.selectedHero);
        navigator.clipboard.writeText(textToCopy).then(function() {
        }).catch(function(err) {
        });
      },
      determineIfLargeData(){
        if(this.timeframetype == "major" || this.timeframe.length >= 3){
          return  true;
        }
        return false;
      },
      removeLevelSelections(level){
        this.clickedData[level] = null;

        this.data = null;
        this.replays = null;
        this.builddata = null;

        this.getData();
      },
      convertRankIDtoName(rankIDs) {
        return rankIDs.map(rankID => this.filters.rank_tiers.find(tier => tier.code == rankID).name);
      },
      setURLParameters(){
        if(this.urlparameters["timeframe_type"]){
          this.timeframetype = this.urlparameters["timeframe_type"];
        }
        
        if(this.urlparameters["timeframe"]){
          this.timeframe = this.urlparameters["timeframe"].split(',');
        }

        if(this.urlparameters["game_type"]){
          this.gametype = this.urlparameters["game_type"].split(',');
        }

        if(this.urlparameters["region"]){
          this.region = this.urlparameters["region"].split(',');
        }

        if(this.urlparameters["statfilter"]){
          this.statfilter = this.urlparameters["statfilter"];
        }
        
        if(this.urlparameters["hero_level"]){
          this.herolevel = this.urlparameters["hero_level"].split(',');
        }

        if(this.urlparameters["hero"]){
          this.hero = this.urlparameters["hero"];
        }

        if(this.urlparameters["role"]){
          this.role = this.urlparameters["role"];
        }

        if(this.urlparameters["game_map"]){
          this.gamemap = this.urlparameters["game_map"].split(',');
        }

        if (this.urlparameters["league_tier"]) {
          this.playerrank = this.urlparameters["league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }

        if (this.urlparameters["hero_league_tier"]) {
          this.herorank = this.urlparameters["hero_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }

        if (this.urlparameters["role_league_tier"]) {
          this.rolerank = this.urlparameters["role_league_tier"].split(',').map(tierName => this.filters.rank_tiers.find(tier => tier.name === tierName)?.code);
        }


        if (this.urlparameters["build_type"]) {
          this.talentbuildtype = this.urlparameters["build_type"];
        }

        if (this.urlparameters["mirror"]) {
          this.mirrormatch = this.urlparameters["mirror"];
        }
      },
    }
  }
</script>