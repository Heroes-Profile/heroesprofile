<template>
  <div class="w-64 h-64 bg-blue-500 border border-gray-300 p-4">

    <div v-if="!playerClicked && !leagueTierClicked">
      <!-- Content here -->
      <button @click="playerClicked = true; leagueTierClicked = false" class="bg-green-500 text-white px-4 py-2 rounded">
        Player
      </button>
      <button @click="leagueTierClicked = true; playerClicked = false" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
        League Tier
      </button>
    </div>
    <div v-else>

      <div v-if="playerClicked">
        <search-player @onDataReturn="handleDataReturn"></search-player>
      </div>

      <div v-else-if="leagueTierClicked">
        <button @click="returnData('League Tier', 1, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Bronze
        </button>
         <button @click="returnData('League Tier', 2, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Silver
        </button>
        <button @click="returnData('League Tier', 3, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Gold
        </button>
        <button @click="returnData('League Tier', 4, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Platinum
        </button>
        <button @click="returnData('League Tier', 5, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Diamond
        </button>
        <button @click="returnData('League Tier', 6, index)" class="bg-green-500 text-white px-4 py-2 rounded ml-2">
          Master
        </button>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'HeroOrLeagueChoiceBox',
  components: {
  },
  props: {
    index: Number,
  },
  data(){
    return {
      playerClicked: false,
      leagueTierClicked: false,
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
    returnData(type, result, index) {
      this.$emit('onDataReturn', { type, result, index });
    },
    handleDataReturn(payload){
      console.log(payload);
    this.$emit('onDataReturn', { 'player', payload, index });

    },
  }
}
</script>