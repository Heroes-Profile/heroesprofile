<template>
  <div>
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
            <th class="text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              <slot name="heading">Talents</slot>
            </th>
            <th class="whitespace-nowrap text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Games
            </th>                
            <th class="whitespace-nowrap py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Win Chance %
            </th>        
            <th v-if="statfilter && statfilter != 'win_rate'" class="whitespace-nowrap py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              {{ statFilterLabel(statfilter) }}
            </th>                           
            <th class="text-right text-sm leading-4 text-gray-500 tracking-wider cursor-pointer">
              Copy Build top Game
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in talentbuilddata" :key="row">
            <td class="py-2 px-3">
              <div class="flex flex-wrap md:flex-nowrap gap-4">
                <talent-image-wrapper :talent="row.level_one"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_four"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_seven"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_ten"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_thirteen"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_sixteen"></talent-image-wrapper>
                <talent-image-wrapper :talent="row.level_twenty"></talent-image-wrapper>
              </div>
            </td>
            <td class="py-2 px-3 whitespace-nowrap">{{ getValueLocal(row.games_played) }}</td>
            <td class="py-2 px-3 whitespace-nowrap">{{ row.win_rate ? row.win_rate.toFixed(2) : 0 }}</td>
            <td v-if="statfilter && statfilter != 'win_rate'" class="py-2 px-3 whitespace-nowrap">{{ getValueLocal(row.total_filter_type) }}</td>
            <td class="py-2 px-3 ">
              <div class="flex items-center justify-end gap-2">
                <span class="text-xs text-right">{{ getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero) }}</span>
                <custom-button class="max-sm:text-xs max-sm:p-0" @click="copyToClipboard(index, row)" :text="buildCopyText[index]" alt="COPY" size="small" :ignoreclick="true" :color="buildCopyColor[index]">{{ buildCopyText[index] }}</custom-button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { statFilterLabel } from '../../../utils/statFilterLabel';

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
      buildCopyText: [],
      buildCopyColor: [],
    };
  },
  created() {
    for (let i = 0; i < this.talentbuilddata.length; i++) {
      this.buildCopyText[i] = "COPY";
    }
    for (let i = 0; i < this.talentbuilddata.length; i++) {
      this.buildCopyColor[i] = "blue";
    }
  },

  mounted() {
    
   /* var responsivetable = this.$refs.responsivetable2;
    if (responsivetable && this.windowWidth < 1500) {
            var newTableWidth = this.windowWidth /responsivetable.clientWidth;
            responsivetable.style.transformOrigin = 'top left';
            responsivetable.style.transform = `scale(${newTableWidth})`;
            var container = this.$refs.tablecontainer2;
            this.tablewidth = newTableWidth;
            container.style.height = (responsivetable.clientHeight * newTableWidth) + 'px';
            //container.style.width = this.windowWidth;
          }
         */
  },
  computed: {
  },
  watch: {
  },
  methods: {
    statFilterLabel,
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
    copyToClipboard(index, row) {
      this.buildCopyText[index] = "COPIED";
      this.buildCopyColor[index] = "teal";
      for (let i = 0; i < this.talentbuilddata.length; i++) {
        if(i != index){
          this.buildCopyText[i] = "COPY";
        }
        if(i != index){
          this.buildCopyColor[i] = "blue";
        }
      }
      
      const textToCopy = this.getCopyBuildToGame(row.level_one, row.level_four, row.level_seven, row.level_ten, row.level_thirteen, row.level_sixteen, row.level_twenty, row.hero);
      navigator.clipboard.writeText(textToCopy).then(function() {
      }).catch(function(err) {
      });
    },
    getValueLocal(value){
      return value ? value.toLocaleString('en-US') : "";
    },
  }
}
</script>