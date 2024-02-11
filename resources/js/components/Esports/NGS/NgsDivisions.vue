<template>
  <div>
    <table class="max-w-[800px] mmd:min-w-[800px] max-md:text-sm">
      <thead>
        <tr>
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            Division
          </th>            
          <th class="py-2 px-3  text-left text-sm leading-4 text-gray-500 tracking-wider">
            Games Played
          </th>  
        </tr>
      </thead>
      <tbody>
        <tr v-for="division in data" :key="division">
          <td><a class="link" :href="getUrl(division.division_0)">{{ division.division_0 }}</a></td>
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