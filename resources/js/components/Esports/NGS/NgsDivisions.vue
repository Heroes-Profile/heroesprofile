<template>
  <div>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Division
          </th>            
          <th class="py-2 px-3 border-b border-gray-200 text-left text-sm leading-4 text-gray-500 tracking-wider">
            Games Played
          </th>  
        </tr>
      </thead>
      <tbody>
        <tr v-for="division in data" :key="division">
          <td><a :href="'/Esports/NGS/Division/' + division.division_0">{{ division.division_0 }}</a></td>
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