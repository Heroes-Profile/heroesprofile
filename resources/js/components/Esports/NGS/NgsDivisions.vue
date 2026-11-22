<template>
  <!-- In a card at the page's width, as on the global pages -->
  <div class="max-w-[120rem] mx-auto px-4 md:px-8">
   <panel-box>
    <table class="min-w-0 w-full rounded-table max-md:text-sm">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4">
            {{ $t('Division') }}
          </th>            
          <th class="py-2 px-3 text-center text-sm leading-4">
            {{ $t('Games') }}
          </th>  
        </tr>
      </thead>
      <tbody class="text-center">
        <tr v-for="division in data" :key="division">
          <td><a class="group/bt inline-flex items-center" :href="getUrl(division.division_0)"><span class="group-hover/bt:underline">{{ division.division_0 }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></a></td>
          <td>{{ division.count }}</td>
        </tr>
      </tbody>
    </table>
   </panel-box>
  </div>
</template>

<script>
export default {
  name: 'NgsDivisions',
  components: {
  },
  props: {
    data: Array,
    season: {
      type: [String, Number ]
    }
  },
  data(){
    return {
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
    getUrl(division){
      let newURL = `/Esports/NGS/Division/${division}`;
      if (this.season) {
        newURL += `?season=${this.season}`;
      }

      return newURL;
    },
  }
}
</script>