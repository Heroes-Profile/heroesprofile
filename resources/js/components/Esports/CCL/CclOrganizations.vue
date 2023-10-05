<template>
  <div>
    <div class="flex   p-20 bg-lighten flex-wrap justify-center">


      <div class="text-center md:w-[15%] mb-15 mx-5"  v-for="(row, index) in data" :key="index">
        <a :href="`/Esports/CCL/Organization/${row.team_name}?season=${season}`">
          <img :src="`/images/CCL/Organizations/Logos/${row.image}`" :alt="row.team_name"/>
          <h3>{{ row.team_name }}</h3>
        </a>
 
      </div>


      </div>
  </div>
</template>

<script>
export default {
  name: 'CclOrganizations',
  components: {
  },
  props: {
    data: Object,
    season: Number,
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