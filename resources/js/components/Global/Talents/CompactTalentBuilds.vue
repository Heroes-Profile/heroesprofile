<template>
  <section aria-label="Talent builds">
    <div v-if="buildData.length > 5" class="flex justify-end mb-2">
      <button type="button" class="link text-sm" :aria-expanded="showAll" @click="showAll = !showAll">
        {{ showAll ? 'Show Top 5 ↑' : 'View More Builds →' }}
      </button>
    </div>
    <div v-if="buildData.length" class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr>
            <th scope="col" class="p-2 text-left">#</th>
            <th scope="col" class="p-2 text-left">Talent Build</th>
            <th scope="col" class="p-2 text-left">Total Games</th>
            <th scope="col" class="p-2 text-left">Win Chance</th>
            <th v-if="statFilter && statFilter !== 'win_rate'" scope="col" class="p-2 text-left">Avg {{ statFilter.replace(/_/g, ' ') }}</th>
            <th scope="col" class="p-2 text-left">Copy</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(build, index) in visibleBuilds" :key="copyCode(build)">
            <td class="p-2">{{ index + 1 }}</td>
            <td class="p-2">
              <div class="flex gap-2 w-max">
                <talent-image-wrapper v-for="level in buildLevels" :key="level" :talent="build[level]"></talent-image-wrapper>
              </div>
            </td>
            <td class="p-2">{{ formatNumber(build.games_played) }}</td>
            <td class="p-2">{{ Number(build.win_rate || 0).toFixed(2) }}%</td>
            <td v-if="statFilter && statFilter !== 'win_rate'" class="p-2">{{ formatNumber(build.total_filter_type) }}</td>
            <td class="p-2">
              <button type="button" class="bg-blue hover:bg-teal text-white rounded px-3 py-2 whitespace-nowrap"
                :title="copyCode(build)" :aria-label="'Copy build ' + copyCode(build)" @click="copyBuild(build)">
                {{ copiedCode === copyCode(build) ? 'Copied' : 'Copy' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <p v-else class="p-4 text-center bg-lighten">No talent builds match these filters.</p>
    <p v-if="copyError" role="alert" class="mt-2">Could not copy. Copy this code manually: <code class="select-all">{{ copyError }}</code></p>
    <span class="sr-only" role="status">{{ copiedCode ? 'Build copied to clipboard' : '' }}</span>
  </section>
</template>

<script>
export default {
  props: {
    buildData: { type: Array, required: true },
    statFilter: { type: String, default: 'win_rate' },
  },
  data() {
    return {
      showAll: false,
      copiedCode: null,
      copyError: null,
      buildLevels: ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'],
    };
  },
  computed: {
    visibleBuilds() {
      return this.showAll ? this.buildData : this.buildData.slice(0, 5);
    },
  },
  watch: {
    buildData() {
      this.showAll = false;
      this.copiedCode = null;
      this.copyError = null;
    },
  },
  methods: {
    copyCode(build) {
      return `[T${this.buildLevels.map(level => build[level]?.sort ?? 0).join('')},${build.hero.build_copy_name}]`;
    },
    async copyBuild(build) {
      this.copyError = null;
      this.copiedCode = null;
      const code = this.copyCode(build);
      try {
        await navigator.clipboard.writeText(code);
        this.copiedCode = code;
      } catch {
        this.copyError = code;
      }
    },
    formatNumber(value) {
      return value == null ? '—' : Number(value).toLocaleString('en-US');
    },
  },
};
</script>
