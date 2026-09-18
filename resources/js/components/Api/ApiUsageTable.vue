<template>
  <div>
    <template v-if="compact">
      <p v-if="!activeRows.length" class="text-sm text-gray-medium mb-3">
        No calls have counted against your quota in the current window.
      </p>

      <table v-else class="min-w-0 w-full responsive-table mb-3">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left text-sm">Endpoint</th>
            <th class="py-2 px-3 text-left text-sm">Calls Used</th>
            <th class="py-2 px-3 text-left text-sm">Calls Left</th>
            <th class="py-2 px-3 text-left text-sm">Calls Reset</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in activeRows" :key="row.endpoint">
            <td class="py-2 px-3">{{ row.name }}</td>
            <td class="py-2 px-3">{{ format(row.used) }}</td>
            <td class="py-2 px-3">{{ format(row.remaining) }}</td>
            <td class="py-2 px-3">{{ row.resets_at }}</td>
          </tr>
        </tbody>
      </table>

      <div v-if="totals.cost > 0" class="mb-3">
        <p class="text-sm text-gray-medium">
          Served {{ formatBytes(totals.bytes) }} this window, costing roughly
          <span class="text-lteal">{{ formatCost(totals.cost) }}</span> to deliver.
        </p>

        <p class="text-xs text-gray-medium mt-1">
          <span class="text-lteal">Beta</span> — cost reporting is new and may not be
          accurate. It is a rough guide to what your usage costs us to serve, not a bill,
          and nothing is charged against it.
        </p>
      </div>

      <a href="/Api/Account/Billing" class="text-sm underline hover:text-lteal">
        View every endpoint limit
      </a>
    </template>

    <template v-else>
      <p v-if="!usage.length" class="text-sm text-gray-medium">
        No endpoints are registered yet.
      </p>

      <div v-if="totals.cost > 0" class="bg-darken p-4 mb-6">
        <div class="flex flex-wrap gap-x-8 gap-y-2 mb-2">
          <div>
            <div class="text-xs text-gray-medium">Data Transferred</div>
            <div class="text-lteal">{{ formatBytes(totals.bytes) }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-medium">Server Time</div>
            <div class="text-lteal">{{ formatDuration(totals.computeMs) }}</div>
          </div>
          <div>
            <div class="text-xs text-gray-medium">Estimated Cost</div>
            <div class="text-lteal">{{ formatCost(totals.cost) }}</div>
          </div>
        </div>

        <p class="text-xs text-gray-medium">
          <span class="text-lteal">Beta</span> — cost reporting is new and may not be
          accurate. Across the current window, and a generous estimate at that: server
          time is counted per request, but requests share a container, so the real figure
          is lower. A guide to what the usage costs to serve, not a bill.
        </p>
      </div>

      <div v-for="group in usage" :key="group.title" class="mb-6">
        <h3 class="text-lteal mb-2">{{ group.title }}</h3>

        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Name</th>
              <th class="py-2 px-3 text-left text-sm">Calls Used</th>
              <th class="py-2 px-3 text-left text-sm">Calls Left</th>
              <th class="py-2 px-3 text-left text-sm">Available Calls</th>
              <th class="py-2 px-3 text-left text-sm">Calls Reset</th>
              <th class="py-2 px-3 text-left text-sm">Cost</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in group.endpoints" :key="row.endpoint" :class="{ 'text-gray-medium': !row.included }">
              <td class="py-2 px-3">{{ row.name }}</td>

              <template v-if="row.included">
                <td class="py-2 px-3">{{ format(row.used) }}</td>
                <td class="py-2 px-3">{{ format(row.remaining) }}</td>
                <td class="py-2 px-3">{{ format(row.limit) }}</td>
                <td class="py-2 px-3">{{ row.resets_at || 'Not started' }}</td>
                <td class="py-2 px-3">{{ row.cost_usd > 0 ? formatCost(row.cost_usd) : '—' }}</td>
              </template>

              <td v-else class="py-2 px-3" colspan="5">Not included in your plan</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script>
import { formatNumber, formatBytes, formatDuration, formatCost } from '../../utils/apiFormat';

export default {
  name: 'ApiUsageTable',
  components: {
  },
  props: {
    usage: {
      type: Array,
      default: () => [],
    },
    // Short list of what has actually been used, for the account page.
    compact: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    activeRows(){
      return this.usage
        .flatMap(group => group.endpoints)
        .filter(row => row.used > 0)
        .sort((a, b) => b.used - a.used)
        .slice(0, 5);
    },
    // Every row, not just the five the compact view lists, so the figure means the
    // window rather than the busiest handful of it.
    totals(){
      return this.usage
        .flatMap(group => group.endpoints)
        .reduce((carry, row) => ({
          bytes: carry.bytes + (row.egress_bytes || 0),
          computeMs: carry.computeMs + (row.compute_ms || 0),
          cost: carry.cost + (row.cost_usd || 0),
        }), { bytes: 0, computeMs: 0, cost: 0 });
    },
  },
  methods: {
    format: formatNumber,
    formatBytes,
    formatDuration,
    formatCost,
  },
}
</script>
