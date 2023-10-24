<template>
  <div>
    <div class="flex   p-20 bg-lighten flex-wrap justify-center">


      <div class="text-center md:w-[15%] mb-15 mx-5"  v-for="(row, index) in data" :key="index">
        <a v-if="esport == 'CCL'" :href="`/Esports/CCL/Organization/${row.team_name}?season=${season}`">
          <img :src="`/images/CCL/Organizations/Logos/${row.image}`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
 
        <a v-else-if="esport == 'MastersClash'" :href="`/Esports/MastersClash/Team/${row.team_name}?season=${season}`">
          <img :src="`/images/MCL/${row.image}`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
 
      </div>


      </div>
  </div>
</template>

<script>
export default {
  name: 'EsportsOrganizations',
  components: {
  },
  props: {
    data: Object,
    season: Number,
    esport: String,
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
  }
}
</script>