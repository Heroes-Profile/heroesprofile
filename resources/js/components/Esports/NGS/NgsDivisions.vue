<template>
  <div>
    <table class="max-w-[800px] mmd:min-w-[800px] max-md:text-sm">
      <thead>
        <tr>
          <th class="py-2 px-3 text-center text-sm leading-4">
            Division
          </th>            
          <th class="py-2 px-3 text-center text-sm leading-4">
            Games Played
          </th>  
        </tr>
      </thead>
      <tbody class="text-center">
        <tr v-for="division in data" :key="division">
          <td><a class="group/bt inline-flex items-center" :href="getUrl(division.division_0)"><span class="group-hover/bt:underline">{{ division.division_0 }}</span><i class="fas fa-chevron-right text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></i></a></td>
          <td>{{ division.count }}</td>
        </tr>
      </tbody>
    </table>
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