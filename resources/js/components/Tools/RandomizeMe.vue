<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Randomize Me'"></page-heading>

    <div v-if="!loading">
      <div class="max-w-[900px] mx-auto px-4 py-10">
      <div class="flex justify-center mb-10">
        <button
          @click="randomize"
          :disabled="loading"
          class="bg-teal hover:bg-lteal text-white font-bold px-8 py-3 rounded text-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <i class="fa-solid fa-shuffle mr-2"></i>
          {{ loading ? 'Randomizing...' : (hero ? 'Randomize Again' : 'Randomize!') }}
        </button>
      </div>

      <div v-if="error" class="text-center text-red-400 mb-6">{{ error }}</div>


      <div v-else-if="hero" class="flex flex-col items-center gap-8">
        <div class="flex flex-col items-center">
          <hero-image-wrapper :key="hero.id" :rectangle="true" :hero="hero" :title="hero.name" size="large"></hero-image-wrapper>
          <h2 class="text-2xl font-bold mt-4">{{ hero.name }}</h2>
        </div>

        <div v-if="build" class="w-full">
          <div class="py-2 px-3">
            <div class="flex flex-wrap gap-4 justify-center">
              <talent-image-wrapper :talent="build.level_one"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_four"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_seven"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_ten"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_thirteen"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_sixteen"></talent-image-wrapper>
              <talent-image-wrapper :talent="build.level_twenty"></talent-image-wrapper>
            </div>
            <div class="flex flex-wrap flex-col md:flex-row items-center justify-center gap-2 mt-2">
              <span class="text-sm text-gray-400">{{ copyString }}</span>
              <i
                :class="['fa-solid fa-copy cursor-pointer', copied ? 'text-teal-400' : 'text-gray-400', 'hover:text-white']"
                :title="copied ? 'COPIED' : 'COPY TO CLIPBOARD'"
                @click="copyToClipboard"
              ></i>
            </div>
            <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-400 justify-center">
              <span v-if="build.game_type"><span class="text-gray-500">Mode:</span> {{ build.game_type }}</span>
              <span v-if="build.region"><span class="text-gray-500">Region:</span> {{ build.region }}</span>
              <span v-if="build.game_map"><span class="text-gray-500">Map:</span> {{ build.game_map }}</span>
            </div>
          </div>
        </div>

        <div v-else-if="!loading" class="text-gray-500 text-center">No complete build found for this hero.</div>
      </div>
      </div>
    </div>
    <div v-if="loading">
      <loading-component></loading-component>
    </div>

    

  </div>
</template>

<script>
export default {
  name: 'RandomizeMe',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
    patreonUser: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      infoText: 'Get a random Heroes of the Storm hero and a real talent build pulled from their last 1,000 games. Use it as a fun challenge or to try something new.',
      hero: null,
      build: null,
      loading: false,
      error: null,
      copied: false,
    };
  },
  computed: {
    copyString() {
      if (!this.build || !this.hero) return '';
      const b = this.build;
      return "[T" +
        (b.level_one      ? b.level_one.sort      : '0') +
        (b.level_four     ? b.level_four.sort     : '0') +
        (b.level_seven    ? b.level_seven.sort    : '0') +
        (b.level_ten      ? b.level_ten.sort      : '0') +
        (b.level_thirteen ? b.level_thirteen.sort : '0') +
        (b.level_sixteen  ? b.level_sixteen.sort  : '0') +
        (b.level_twenty   ? b.level_twenty.sort   : '0') +
        "," + this.hero.build_copy_name + "]";
    },
  },
  methods: {
    randomize() {
      if (!this.heroes || this.heroes.length === 0) return;

      this.loading = true;
      this.error = null;
      this.build = null;
      this.copied = false;

      const randomHero = this.heroes[Math.floor(Math.random() * this.heroes.length)];
      this.hero = randomHero;

      this.$axios.post('/api/v1/tools/randomize-me', { hero: randomHero.name })
        .then(response => {
          this.build = response.data.build;
        })
        .catch(() => {
          this.error = 'Failed to load a build. Please try again.';
        })
        .finally(() => {
          this.loading = false;
        });
    },
    copyToClipboard() {
      if (!this.copyString) return;
      navigator.clipboard.writeText(this.copyString).then(() => {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 2000);
      });
    },
  },
};
</script>
