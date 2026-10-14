<template>
  <div>
    <h2 class="text-xl font-bold mb-2 text-teal">Your games</h2>
    <p v-if="data.games.length === 0" class="text-center">No processed games match these filters.</p>
    <table v-else class="min-w-0 w-full responsive-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Map</th>
          <th>Hero</th>
          <th>Result</th>
          <th>Length</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="game in data.games" :key="game.replay_id">
          <td>{{ formatDate(game.game_date) }}</td>
          <td>
            <div class="flex items-center gap-2">
              <map-image-wrapper v-if="game.map" :map="game.map" :size="'small'"></map-image-wrapper>
              <span>{{ game.map ? game.map.name : '-' }}</span>
            </div>
          </td>
          <td>
            <div class="flex items-center gap-2">
              <hero-image-wrapper v-if="game.hero" :hero="game.hero" :size="'small'"></hero-image-wrapper>
              <span>{{ game.hero ? game.hero.name : '-' }}</span>
            </div>
          </td>
          <td :class="game.winner ? 'text-teal' : 'text-red'">{{ game.winner ? 'Win' : 'Loss' }}</td>
          <td>{{ clock(game.game_length) }}</td>
          <td>
            <custom-button
              :href="'/Analytics/Match/Single/' + game.replay_id"
              :text="'Watch'"
              :alt="'Watch this game'"
              size="small"
            ></custom-button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import analytics from '../../utils/deepAnalyticsFormat.js';

export default {
  name: 'DeepAnalyticsGames',
  mixins: [analytics],
  props: {
    data: Object,
    maps: Object,
    heroes: Array,
  },
  methods: {
    formatDate(value) {
      // game_date is stored as UTC
      return new Date(value.replace(' ', 'T') + 'Z').toLocaleString();
    },
  },
};
</script>
