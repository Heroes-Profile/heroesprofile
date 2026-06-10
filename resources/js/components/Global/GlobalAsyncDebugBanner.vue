<template>
  <div
    v-if="showDebugBanner"
    class="max-w-[1500px] mx-auto mb-4 p-3 rounded border border-yellow-600 bg-yellow-900/40 text-yellow-100 text-xs font-mono whitespace-pre-wrap"
  >
    <div class="font-bold text-yellow-300 mb-1">{{ pageLabel }} async debug (develop only)</div>
    <div v-if="serverDebugConfig">Server: {{ serverDebugSummary }}</div>
    <div>Load: {{ loadDebugStatus || 'waiting...' }}</div>
    <div v-if="headerDebugSummary">Headers: {{ headerDebugSummary }}</div>
    <div v-if="loadMeta && loadMeta.error" class="text-red-300 mt-1">{{ loadMeta.error }}</div>
  </div>
</template>

<script>
import { getLatestGlobalAsyncDebug, subscribeGlobalAsyncDebug } from '../../utils/globalAsyncPost';

export default {
  name: 'GlobalAsyncDebugBanner',
  props: {
    pageLabel: {
      type: String,
      required: true,
    },
  },
  data() {
    const debug = getLatestGlobalAsyncDebug();

    return {
      serverDebugConfig: null,
      loadMeta: debug.loadMeta,
      loadDebugStatus: debug.loadDebugStatus,
      unsubscribeDebug: null,
    };
  },
  computed: {
    showDebugBanner() {
      const host = window.location.hostname;
      return host.includes('develop') || host === 'localhost' || host === '127.0.0.1';
    },
    serverDebugSummary() {
      if (!this.serverDebugConfig) {
        return 'loading...';
      }

      if (this.serverDebugConfig.error) {
        return this.serverDebugConfig.error;
      }

      const cfg = this.serverDebugConfig;
      return [
        `APP_ENV=${cfg.app_env}`,
        `async_runtime=${cfg.global_async_enabled_runtime}`,
        `async_config=${cfg.global_async_enabled_config}`,
        `async_getenv=${cfg.global_async_getenv ?? 'unset'}`,
        `fresh_ttl=${cfg.cache_fresh_seconds_sample}s`,
        `handler=${cfg.cloud_tasks?.handler_url ? 'set' : 'missing'}`,
        `bypass_cache=${cfg.global_bypass_cache_runtime}`,
        `marker=${cfg.deploy_marker}`,
      ].join(' | ');
    },
    headerDebugSummary() {
      if (!this.loadMeta) {
        return null;
      }

      const post = this.$formatResponseHeaders(this.loadMeta.postHeaders);
      const poll = this.$formatResponseHeaders(this.loadMeta.pollHeaders);
      const parts = [];

      if (post !== 'none') {
        parts.push(`POST: ${post}`);
      }

      if (poll !== 'none') {
        parts.push(`POLL: ${poll}`);
      }

      return parts.length ? parts.join(' || ') : null;
    },
  },
  mounted() {
    if (this.showDebugBanner) {
      this.fetchServerDebugConfig();
      this.unsubscribeDebug = subscribeGlobalAsyncDebug(({ loadMeta, loadDebugStatus }) => {
        this.loadMeta = loadMeta;
        this.loadDebugStatus = loadDebugStatus;
      });
    }
  },
  beforeUnmount() {
    if (this.unsubscribeDebug) {
      this.unsubscribeDebug();
    }
  },
  methods: {
    async fetchServerDebugConfig() {
      try {
        const response = await this.$axios.get('/api/v1/global/debug/config');
        this.serverDebugConfig = response.data;
      } catch (error) {
        this.serverDebugConfig = {
          error: error.response?.data?.message || error.message || 'Failed to load debug config',
        };
      }
    },
  },
};
</script>
