<template>
  <div class="max-md:w-full">
    <div id="table-container2" ref="tablecontainer2" class=" max-md:overflow-hidden max-md:w-full min-w-0 max-sm:text-xs  2xl:mx-auto " style=" ">
      <table class="min-w-0 w-auto mx-auto max-sm:text-xs" ref="responsivetable2">
          <thead v-if="!$slots.heading">
            <tr>
              <th :colspan="statfilter ? 5 : 4" class="text-center py-2 px-3 ">
                Builds
              </th>
            </tr>
          </thead>
        <thead>
          <tr>
            <th class="text-center text-sm leading-4 cursor-pointer">
              <slot name="heading">Talents</slot>
            </th>
            <th class="whitespace-nowrap text-center text-sm leading-4 cursor-pointer">
              Games
            </th>                
            <th class="whitespace-nowrap py-2 px-3 text-center text-sm leading-4 cursor-pointer">
              Win Chance %
            </th>        
            <th v-if="statfilter && statfilter != 'win_rate'" class="whitespace-nowrap py-2 px-3 text-center text-sm leading-4 cursor-pointer">
              {{ statFilterLabel(statfilter) }}
            </th>                           
            <th class="text-right text-sm leading-4 cursor-pointer">
              Copy Build top Game
            </th>
          </tr>
        </thead>
        <tbody class="text-center">
          <tr v-for="(row, index) in talentbuilddata" :key="row">
            <td class="py-2 px-3">
              <build-code :build="row" :hero="row.hero" show="talents" align="start"></build-code>
            </td>
            <td class="py-2 px-3 whitespace-nowrap">{{ getValueLocal(row.games_played) }}</td>
            <td class="py-2 px-3 whitespace-nowrap">{{ row.win_rate ? row.win_rate.toFixed(2) : 0 }}</td>
            <td v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-3 whitespace-nowrap">{{ getValueLocal(row.total_filter_type) }}</td>
            <td class="py-2 px-3 ">
              <build-code :build="row" :hero="row.hero" show="code" align="end"></build-code>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';
import { scaleTableToFit } from '../../../utils/scaleTable';

export default {
  name: 'GlobalTalentBuildsSection',
  components: {
  },
  props: {
    talentbuilddata: {
      Type: Object|Array
    },
    buildtype: String,
    statfilter: String,
    talentimages: Array,
  },
  data() {
    return {
      windowWidth: window.innerWidth,
    };
  },

  mounted() {
    this.$nextTick(() => {
      if (window.matchMedia('(max-width: 767px)').matches) {
        scaleTableToFit(this.$refs.responsivetable2, this.$refs.tablecontainer2);
      }
    });
  },
  computed: {
  },
  watch: {
  },
  methods: {
    statFilterLabel,
    getValueLocal(value){
      return value ? value.toLocaleString('en-US') : "";
    },
  }
}
</script>