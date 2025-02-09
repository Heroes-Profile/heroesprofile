<template>
  <div>
    <div class="flex   p-20 bg-lighten flex-wrap justify-center">


      <div class="text-center md:w-[15%] mb-15 mx-5 mb-20"  v-for="(row, index) in data" :key="index">
        <a v-if="esport == 'CCL'" :href="`/Esports/CCL/Organization/${row.team_name}?season=${season}`">
          <img class="h-full" :src="`/images/CCL/Organizations/Logos/${row.image}`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
 
        <a v-else-if="esport == 'MastersClash'" :href="`/Esports/MastersClash/Team/${row.team_name}?season=${season}`">
          <img class="h-full"  :src="`/images/MCL/${row.image}`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
 
        <a v-else-if="esport == 'hi'" :href="`/Esports/HeroesInternational/Team/${row.team_name}?tournament=main&season=${season}`">
          <img class="h-full"  :src="`/images/HI/Team/Logos/${row.image}.png`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>

        <a v-else-if="esport == 'hi_nc'" :href="`/Esports/HeroesInternational/Team/${row.team_name}?tournament=nationscup&season=${season}`">
          <img class="h-full"  :src="`/images/HI/Flags/${row.image}.png`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
        <a v-else-if="esport == 'HGCMore'" :href="`/Esports/Other/${row.name}`">
          <img class="h-full"  :src="`/images/EsportOther/${row.icon}`" :alt="row.name"/>
          <h3>{{ row.name }}</h3>
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