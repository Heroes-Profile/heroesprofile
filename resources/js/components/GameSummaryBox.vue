<template>
  <div :class="[
  'relative ml-10 ',
  {
    'ml-10' : !esport
  } ]">
    <a :href="getHref()">
      <div :class="[
      'mt-4  m-l-auto w-full text-right min-h-4 py-2 pl-[6em]',
      {
        'pl-[6em]' : esport != true
      }
      ]
      ">{{ getCaptions() }}</div>
      <div
        :class="[
          'flex border border-white border-2 bg-cover bg-no-repeat bg-center  rounded-2xl border-red  md:pl-10',
          {
            'border-teal': data.winner === 1,
            'md:pl-[6em] ': esport != true
          }
          ]"
          :style="{ backgroundImage: `url('/images/maps/match/match-${data.game_map.sanitized_map_name}.jpg')` }"
          >    

          <div v-if="!esport && esport != true" class=" bg-red-500 absolute -left-10 md:-bottom-[1em]">
            <hero-image-wrapper size="xl" :hero="data.hero" :excludehover="true"></hero-image-wrapper>
          </div>

          <div v-else-if="esport && esport == true" class="flex flex-wrap justify-between gap-2 w-full hover:backdrop-brightness-125 py-1 max-md:p-2">
            <div v-for="(item, index) in data.heroes">
              <hero-image-wrapper  v-if="item" size="big" :hero="item.hero" :excludehover="true"></hero-image-wrapper>
            </div>
            <stat-box class="ml-auto md:w-[30%] md:mr-10" :title="'Teams'" :value="data.team_0_name + ' vs ' + data.team_1_name"></stat-box>
          </div>

          <div v-if="!esport && esport != true" class="flex w-full hover:backdrop-brightness-125 max-md:flex-col">
            <div class="flex w-full md:ml-10 max-md:flex-col">
              <stat-box :title="'HP Player MMR'" :value="data.player_mmr.toLocaleString('en-US')" :secondstat="data.player_change !== null ? data.player_change.toFixed(2) : null" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'blue' : 'red'"></stat-box>
              <stat-box :title="'HP Hero MMR'" :value="data.hero_mmr.toLocaleString('en-US')" :secondstat="data.player_change !== null ? data.hero_change.toFixed(2): null" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'blue' : 'red'"></stat-box>
              <stat-box :title="'HP Role MMR'" :value="data.role_mmr.toLocaleString('en-US')" :secondstat="data.player_change !== null ? data.role_change.toFixed(2) : null" :secondcaption="'Change'" secondtype="mmrchange" :color="data.winner === 1 ? 'blue' : 'red'"></stat-box>
            </div>
            <div class="flex gap-x-1 mx-2 items-center justify-start md:w-[450px] pl-2 max-md:flex-wrap">
              <div class="flex-1"><talent-image-wrapper :talent="data.level_one" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"><talent-image-wrapper :talent="data.level_four" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"><talent-image-wrapper :talent="data.level_seven" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"><talent-image-wrapper :talent="data.level_ten" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"><talent-image-wrapper :talent="data.level_thirteen" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"> <talent-image-wrapper :talent="data.level_sixteen" :size="'medium'"></talent-image-wrapper></div>
              <div class="flex-1"><talent-image-wrapper :talent="data.level_twenty" :size="'medium'"></talent-image-wrapper></div>
            </div>
          </div>
        </div>
    </a>
  </div>
</template>


<script>
  import moment from 'moment-timezone';

  export default {
    name: 'GameSummaryBox',
    components: {
    },
    props: {
      data: Object,
      esport: Boolean,
      esportLeague: String,
      esportSeries: String,
      tournament: String,
    },
    data(){
      return {
        userTimezone: moment.tz.guess(),
      }
    },
    created(){
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      getHref(){
        if(this.esport){
          if(this.esportSeries){
            return '/Esports/' + this.esportLeague + '/' + this.esportSeries + '/Match/Single/' + this.data.replayID;
          }

          if(this.esportLeague == "HeroesInternational"){
            return '/Esports/' + this.esportLeague + '/Match/Single/' + this.data.replayID + '/?tournament=' + this.tournament;
          }

          return '/Esports/' + this.esportLeague + '/Match/Single/' + this.data.replayID;
        }
        return '/Match/Single/' + this.data.replayID;
      },
      formatDate(dateString) {
        const originalDate = moment.tz(dateString, 'Atlantic/Reykjavik'); // Assuming date strings are in UTC
        const localDate = originalDate.clone().tz(moment.tz.guess());

        return localDate.format('MM/DD/YYYY h:mm:ss a');
      },
      getCaptions(){
        if(!this.esport){
          return this.data.game_map.name + "|" + this.data.game_type.name + "|" + this.formatDate(this.data.game_date);
        }else{
          return this.data.game_map.name + "|" + "Round " + this.data.round + " Game " + this.data.game + "|" + this.formatDate(this.data.game_date);
        }
      }
    }
  }
</script>