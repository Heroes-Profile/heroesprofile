<template>
  <div class="data-table">
    <table class="table">
      Map: {{ map }}
<br/>
Game Type: {{ gametype }}
      <tbody>
     <tr class="" v-if="loading">
        <td class="lead text-center">Loading...</td>
      </tr>
      <tr class="" v-else-if="tabledata.length === 0">
          <td class="lead text-center">No Data Found.</td>
      </tr>
     <tr v-for="(data, key1) in tabledata" :key="data.name" class="m-datatable__row" v-else>

        <td v-for="(value, key) in data">{{ value }}</td>
      </tr>
      </tbody>

    </table>
  </div>
</template>

<script type="text/ecmascript-6">

export default {
  props: [],
    data() {
      return{
        tabledata: [],
        map: "All",
        gametype: "All",
        loading: false
      }
    },

  created () {
    this.fetchData()
  },
  watch: {

    '$route': 'fetchData'
  },
  methods: {
    fetchData () {
      this.loading = true
      this.map = this.sanitizeParams(this.$route.params.map)
      this.gametype = this.sanitizeParams(this.$route.params.gametype)
      axios.get('/get_heroes_stats_table_data', {
        params: {
          map: this.map,
          gametype: this.gametype
        }
      }).then(response => {
           this.tabledata = response.data
           this.loading = false
         })
    },
    sanitizeParams(param){
      param = param.replace(/_/g, ' ')
      param = decodeURI(param)
      return param
    }
  }
}
</script>

<style scoped>
</style>
