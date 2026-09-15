<template>
  <div id="filter-label" class="relative">
    <div class="flex flex-col text-sm font-medium text-gray-700 p-2">
      <span>{{ text }}</span>
      <div class="relative flex">
        <input
          type="text"
          v-model="search"
          placeholder="Enter battletag"
          class="md:w-[200px] min-w-[100px] h-[40px] border-solid border-[1px] border-white bg-blue p-2 text-white focus:outline-none"
          @keydown.enter.prevent="lookup"
          @input="results = []; error = null"
        />
        <button type="button" class="h-[40px] bg-blue p-2 border-r-[1px] border-t-[1px] border-b-[1px] hover:bg-teal" :disabled="loading" @click="lookup">{{ loading ? '...' : 'Add' }}</button>

        <div v-if="results.length > 1" class="absolute left-0 top-full z-50 bg-gray-dark border border-white/20 rounded shadow-lg" style="min-width:280px; max-height:340px; overflow-y:auto;">
          <div
            v-for="result in results"
            :key="result.blizz_id + '-' + result.region"
            class="bg-blue hover:bg-lblue p-3 mb-1 rounded flex flex-col items-center cursor-pointer text-sm"
            @click="add(result)"
          >
            <div>{{ result.battletagShort }} ({{ result.regionName }})</div>
            <div>Games Played: {{ result.totalGamesPlayed }}</div>
            <div v-if="result.latestHero">
              <hero-image-wrapper :hero="result.latestHero"></hero-image-wrapper>
            </div>
          </div>
        </div>
      </div>
      <p v-if="error" class="text-red text-xs mt-1">{{ error }}</p>
      <div v-if="selected.length > 1" class="flex items-center gap-2 mt-1">
        <tab-button tab1text="OR" tab2text="AND" :ignoreclick="true" @tab-click="setMatch" :overridedefaultside="match === 'all' ? 'right' : 'left'"></tab-button>
        <span class="text-xs">{{ match === 'all' ? 'Games with every player' : 'Games with any player' }}</span>
      </div>
      <div v-if="selected.length" class="flex flex-wrap gap-1 mt-1 max-w-[260px]">
        <span v-for="player in selected" :key="player.blizz_id + '-' + player.region" class="uppercase font-bold bg-teal rounded px-1 flex items-center gap-1">
          {{ player.battletagShort }} ({{ player.regionName }})
          <button type="button" class="leading-none" @click="remove(player)">x</button>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'PlayerPickerFilter',
    components: {
    },
    props: {
      text: String,
    },
    data(){
      return {
        search: '',
        results: [],
        selected: [],
        match: 'any',
        error: null,
        loading: false,
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
      async lookup() {
        if (!this.search.trim() || this.loading) return;

        this.results = [];
        this.error = null;
        this.loading = true;
        this.$emit('searching', true);
        try {
          // Private and banned accounts are already left out of this search
          const response = await this.$axios.post('/api/v1/battletag/search', {
            userinput: this.search.trim(),
          });
          const results = Object.values(response.data);
          if (results.length === 0) {
            this.error = 'No player found.';
          } else if (results.length === 1) {
            this.add(results[0]);
          } else {
            this.results = results;
          }
        } catch {
          this.error = 'Search failed. Please try again.';
        } finally {
          this.loading = false;
          this.$emit('searching', false);
        }
      },
      add(result) {
        if (!this.selected.some(player => player.blizz_id == result.blizz_id && player.region == result.region)) {
          this.selected = [...this.selected, {
            blizz_id: result.blizz_id,
            region: result.region,
            battletagShort: result.battletagShort,
            regionName: result.regionName,
          }];
          this.emitChange();
        }
        this.search = '';
        this.results = [];
      },
      remove(player) {
        this.selected = this.selected.filter(item => !(item.blizz_id == player.blizz_id && item.region == player.region));
        this.emitChange();
      },
      // 'left' = OR, 'right' = AND, matching the tab button
      setMatch(side) {
        this.match = side === 'right' ? 'all' : 'any';
        this.$emit('input-changed', { field: this.text + ' Match', value: this.match, type: 'single' });
      },
      emitChange() {
        this.$emit('input-changed', { field: this.text, value: this.selected, type: 'multi' });
      },
    }
  }
</script>
