<template>
  <div>
    <page-heading :heading="'Deep Analytics'"></page-heading>

    <div class="mx-auto w-full max-w-[800px] mt-[2vh] bg-lighten rounded-lg p-8">
      <h2 class="text-xl font-bold mb-2 text-teal">{{ battletag }}</h2>
      <p class="mb-6 text-gray-light">
        Season games (from Oct 2, 2026) processed for deep analysis.
      </p>

      <div class="mb-2 flex justify-between text-sm">
        <span>{{ status.done }} of {{ status.total }} games</span>
        <span v-if="status.total > 0">{{ percent }}%</span>
      </div>
      <div class="w-full h-3 bg-darken rounded mb-6 overflow-hidden">
        <div class="h-3 bg-teal transition-all duration-500" :style="{ width: percent + '%' }"></div>
      </div>

      <div v-if="status.total === 0" class="text-gray-light">
        No season games found for this account yet.
      </div>
      <div v-else-if="status.done >= status.total" class="text-teal">
        All your season games are processed.
      </div>
      <div v-else>
        <button
          class="bg-teal text-white px-6 py-2 rounded"
          :class="{ 'opacity-50 cursor-not-allowed': requesting || polling }"
          :disabled="requesting || polling"
          @click="request"
        >
          {{ polling ? 'Processing...' : 'Process my games' }}
        </button>
      </div>

      <p v-if="message" class="mt-4 text-sm text-gray-light">{{ message }}</p>
      <p v-if="error" class="mt-4 text-sm text-lred">{{ error }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DeepAnalyticsPage',
  props: {
    battletag: String,
    initialStatus: Object,
  },
  data() {
    return {
      status: { ...this.initialStatus },
      requesting: false,
      polling: false,
      pollTimer: null,
      message: '',
      error: '',
    };
  },
  computed: {
    percent() {
      return this.status.total > 0 ? Math.floor((this.status.done / this.status.total) * 100) : 0;
    },
  },
  beforeUnmount() {
    clearInterval(this.pollTimer);
  },
  methods: {
    async request() {
      this.requesting = true;
      this.error = '';
      try {
        const response = await this.$axios.post('/DeepAnalytics/Request');
        this.message = response.data.already_requested
          ? 'Your games are already being processed.'
          : `Processing ${response.data.queued} games. This page updates as they finish.`;
        this.startPolling();
      } catch (e) {
        this.error = 'Could not start processing. Please try again in a minute.';
      } finally {
        this.requesting = false;
      }
    },
    startPolling() {
      if (this.pollTimer) {
        return;
      }
      this.polling = true;
      this.pollTimer = setInterval(this.refresh, 5000);
    },
    async refresh() {
      try {
        const response = await this.$axios.get('/DeepAnalytics/Status');
        this.status = response.data;
        if (this.status.done >= this.status.total) {
          clearInterval(this.pollTimer);
          this.pollTimer = null;
          this.polling = false;
          this.message = '';
        }
      } catch (e) {
        // A missed poll is retried on the next tick.
      }
    },
  },
};
</script>
