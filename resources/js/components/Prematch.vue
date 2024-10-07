<template>
    <div v-if="data">
      Team 1

      <div>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. Account Level'" :value="data[0].average_account_level" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. QM MMR'" :value="data[0].average_qm_mmr + '|' + data[0].average_qm_rank" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. SL MMR'" :value="data[0].average_sl_mmr + '|' + data[0].average_sl_rank" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. AR MMR'" :value="data[0].average_ar_mmr + '|' + data[0].average_ar_rank" :color="'teal'"></stat-box>

        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Top ACC. Level'" :value="data[0].highest_account_level_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best QM Rank'" :value="data[0].highest_qm_mmr_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best SL Rank'" :value="data[0].highest_sl_mmr_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best ARAM Rank'" :value="data[0].highest_ar_mmr_battletag" :color="'blue'"></stat-box>
      </div>


      <div>
        <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
          <thead class=" top-0 w-full  z-40">
            <tr class="">
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Player
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stats
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in data[0].players">
              <tr>
                <td>
                  <a class="link cursor-pointer" @click="this.$redirectToProfile(row.battletag, row.blizz_id, row.region, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`">{{ row.battletag }}</a>
                  {{ row.account_level }}
                  Top 3 Heroes           
                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[0].hero">
                    <image-hover-box :title="row.top_heroes[0].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[0].count"></image-hover-box>
                  </hero-image-wrapper>
 
                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[1].hero">
                    <image-hover-box :title="row.top_heroes[1].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[1].count"></image-hover-box>
                  </hero-image-wrapper>

                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[2].hero">
                    <image-hover-box :title="row.top_heroes[2].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[2].count"></image-hover-box>
                  </hero-image-wrapper>

                </td>
                <td>
                  <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
                    <thead class=" top-0 w-full  z-40">
                      <tr class="">
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Game Type
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          MMR
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Rank
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Win Rate
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Games Played
                        </th>
                      </tr>
                    </thead>
                      <tr>
                        <td>
                          Quick Match
                        </td>
                        <td>
                          {{ row.qm_mmr }}
                        </td>
                        <td>
                          {{ row.qm_rank}}
                        </td>
                        <td>
                          {{ row.qm_win_rate}}
                        </td>
                        <td>
                          {{ row.qm_games_played}}
                        </td>
                      </tr>

                      <tr>
                        <td>
                          Storm League
                        </td>
                        <td>
                          {{ row.sl_mmr }}
                        </td>
                        <td>
                          {{ row.sl_rank}}
                        </td>
                        <td>
                          {{ row.sl_win_rate}}
                        </td>
                        <td>
                          {{ row.sl_games_played}}
                        </td>
                      </tr>

                      <tr>
                        <td>
                          ARAM
                        </td>
                        <td>
                          {{ row.ar_mmr }}
                        </td>
                        <td>
                          {{ row.ar_rank}}
                        </td>
                        <td>
                          {{ row.ar_win_rate}}
                        </td>
                        <td>
                          {{ row.ar_games_played}}
                        </td>
                      </tr>

                  </table>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      Team 2

      <div>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. Account Level'" :value="data[1].average_account_level" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. QM MMR'" :value="data[1].average_qm_mmr + '|' + data[1].average_qm_rank" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. SL MMR'" :value="data[1].average_sl_mmr + '|' + data[1].average_sl_rank" :color="'teal'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Avg. AR MMR'" :value="data[1].average_ar_mmr + '|' + data[1].average_ar_rank" :color="'teal'"></stat-box>

        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Top ACC. Level'" :value="data[1].highest_account_level_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best QM Rank'" :value="data[1].highest_qm_mmr_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best SL Rank'" :value="data[1].highest_sl_mmr_battletag" :color="'blue'"></stat-box>
        <stat-box class="min-w-[30%]" v-if="!esport" :title="'Best ARAM Rank'" :value="data[1].highest_ar_mmr_battletag" :color="'blue'"></stat-box>
      </div>


      <div>
        <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
          <thead class=" top-0 w-full  z-40">
            <tr class="">
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Player
              </th>
              <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                Stats
              </th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(row, index) in data[1].players">
              <tr>
                <td>
                  <a class="link cursor-pointer" @click="this.$redirectToProfile(row.battletag, row.blizz_id, row.region, false)" :href="`/Player/${row.battletag}/${row.blizz_id}/${row.region}`">{{ row.battletag }}</a>
                  {{ row.account_level }}
                  Top 3 Heroes           
                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[0].hero">
                    <image-hover-box :title="row.top_heroes[0].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[0].count"></image-hover-box>
                  </hero-image-wrapper>

                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[1].hero">
                    <image-hover-box :title="row.top_heroes[1].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[1].count"></image-hover-box>
                  </hero-image-wrapper>

                  <hero-image-wrapper :size="'small'" :hero="row.top_heroes[2].hero">
                    <image-hover-box :title="row.top_heroes[2].hero.name" :paragraph-one="'Games Played:' + row.top_heroes[2].count"></image-hover-box>
                  </hero-image-wrapper>

                </td>
                <td>
                  <table id="responsive-table" class="responsive-table  relative " ref="responsivetable">
                    <thead class=" top-0 w-full  z-40">
                      <tr class="">
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Game Type
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          MMR
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Rank
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Win Rate
                        </th>
                        <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
                          Games Played
                        </th>
                      </tr>
                    </thead>
                      <tr>
                        <td>
                          Quick Match
                        </td>
                        <td>
                          {{ row.qm_mmr }}
                        </td>
                        <td>
                          {{ row.qm_rank}}
                        </td>
                        <td>
                          {{ row.qm_win_rate}}
                        </td>
                        <td>
                          {{ row.qm_games_played}}
                        </td>
                      </tr>

                      <tr>
                        <td>
                          Storm League
                        </td>
                        <td>
                          {{ row.sl_mmr }}
                        </td>
                        <td>
                          {{ row.sl_rank}}
                        </td>
                        <td>
                          {{ row.sl_win_rate}}
                        </td>
                        <td>
                          {{ row.sl_games_played}}
                        </td>
                      </tr>

                      <tr>
                        <td>
                          ARAM
                        </td>
                        <td>
                          {{ row.ar_mmr }}
                        </td>
                        <td>
                          {{ row.ar_rank}}
                        </td>
                        <td>
                          {{ row.ar_win_rate}}
                        </td>
                        <td>
                          {{ row.ar_games_played}}
                        </td>
                      </tr>

                  </table>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
</template>

<script>
export default {
  name: 'Prematch',
  components: {
  },
  props: {
    prematchid: Number,
  },
  data(){
    return {
      isLoading: false,
      windowWidth: window.innerWidth,
      data: null,

    }
  },
  created(){
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
      this.isLoading = true;
      try{
        const response = await this.$axios.post("/api/v1/prematch", {
          prematchid: this.prematchid,
        });
        this.data = response.data; 
      }catch(error){
      //Do something here
      }finally {
        this.isLoading = false;
        this.$nextTick(() => {
          if(this.windowWidth < 1500){
            this.resizeTables();
          }
        });
      }
    },
  }
}
</script>