<template>
  <div>
    <page-heading heading="Endpoint Limits" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1200px] px-4 mt-6 pb-16">

      <div class="bg-lighten border-l-4 border-teal p-4 mb-8">
        <p class="text-sm">
          Allowances are per endpoint, per week, on a rolling seven-day window that starts
          at your first call to that endpoint rather than on a fixed calendar day.
          <strong>Not included</strong> means a call to that endpoint returns
          <code class="text-lteal">403</code> on that tier.
        </p>
        <p class="text-sm mt-2">
          Every tier also allows 60 requests a minute, or 120 on Developer. A few endpoints
          differ from that, noted against them below — the ones answering a single replay
          allow far more, and the ones that fan a single call out into many queries allow
          far fewer.
        </p>
        <p class="text-sm mt-2">
          Responses carry
          <code class="text-lteal">X-HP-Quota-Limit</code>,
          <code class="text-lteal">X-HP-Quota-Remaining</code> and
          <code class="text-lteal">X-HP-Quota-Reset</code> for the weekly allowance, and
          <code class="text-lteal">X-RateLimit-*</code> for the per-minute one, so you can
          track both without guessing.
        </p>
      </div>

      <div v-for="group in groups" :key="group.title" class="mb-10">
        <h2 class="text-2xl mb-3">{{ group.title }}</h2>

        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="text-left">Endpoint</th>
              <th v-for="plan in plans" :key="plan.key" class="text-center">{{ plan.name }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="endpoint in group.endpoints" :key="endpoint.endpoint">
              <td>
                {{ endpoint.name }}
                <span class="block text-xs text-gray-medium">{{ endpoint.endpoint }}</span>
                <span v-if="endpoint.rate_note" class="block text-xs text-blue">{{ endpoint.rate_note }}</span>
              </td>
              <td v-for="plan in plans" :key="plan.key" class="text-center">
                {{ endpoint.limits[plan.key] }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="text-sm text-gray-medium">
        Esports endpoints are granted per organisation rather than sold, so they are not listed here.
        <a class="link" href="/Api">Back to plans</a>.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiEndpointLimits',
  props: {
    plans: {
      type: Array,
      default: () => [],
    },
    groups: {
      type: Array,
      default: () => [],
    },
  },
  data(){
    return {
      infoText: "What each subscription tier allows, endpoint by endpoint.",
    }
  },
}
</script>
