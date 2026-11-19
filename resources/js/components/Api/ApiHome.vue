<template>
  <!-- 60px between the header and the title, as in the design -->
  <div class="pt-[3.75rem]">
    <page-heading heading="Heroes Profile API"></page-heading>
    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 -mt-2 mb-8">
      <span class="uppercase text-gray-medium">The same data that powers heroesprofile.com</span>
    </div>

    <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-8">
      <p class="bg-surface rounded px-4 py-5 md:px-8 md:py-7 text-subtext leading-relaxed">
        Hero statistics, talent builds, match data, player profiles and MMR. We source original replay data from our own
        <a href="/Upload" class="underline hover:text-lteal">Uploader</a>, then parse it to provide up-to-date MMR, win rates and more.
        Your subscription helps us build new features for
        <a href="https://www.heroesprofile.com/" class="underline hover:text-lteal">Heroes Profile</a>.
        View the <a href="/Api/Docs" class="underline hover:text-lteal">Documentation</a> or
        <a href="/Api/Register" class="underline hover:text-lteal">Register</a> to get started.
      </p>

      <!-- One card per plan, edged in its colour -->
      <div class="grid gap-8 md:grid-cols-3">
        <div v-for="plan in plans" :key="plan.key" :class="['bg-surface rounded border-t-[0.375rem] px-4 md:px-8 pt-6 pb-8 flex flex-col gap-5', borderClass(plan.key)]">
          <div>
            <h2 class="text-2xl font-bold">{{ plan.name }}</h2>
            <p class="text-sm text-subtext mt-1.5">{{ taglines[plan.key] }}</p>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-5xl font-bold leading-none">${{ plan.price }}</span>
            <span class="text-sm text-subtext">/ month</span>
          </div>
          <div class="h-px bg-gray-dark"></div>

          <ul class="flex flex-col gap-3.5 flex-grow">
            <li v-for="(feature, index) in features[plan.key]" :key="index" :class="['flex items-center gap-3', { 'text-subtext': !feature.included }]">
              <span v-if="feature.included" class="w-5 h-5 rounded-[0.25rem] bg-teal flex items-center justify-center shrink-0"><basil-icon name="check" class="text-base text-white"></basil-icon></span>
              <span v-else class="w-5 h-5 rounded-[0.25rem] bg-surface-3 flex items-center justify-center shrink-0"><basil-icon name="cross" class="text-base"></basil-icon></span>
              <span>{{ feature.text }}</span>
              <round-image v-if="feature.text.startsWith('Direct endpoint')" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                <slot><p class="text-sm p-2 text-left">{{ directIntegrationInfo }}</p></slot>
              </round-image>
            </li>
          </ul>

          <custom-button
            :href="ctaHref(plan.key)"
            :text="ctaText(plan.key)"
            :alt="plan.name"
            :color="colorFor(plan.key)"
            class="font-semibold"
          ></custom-button>
        </div>
      </div>

      <panel-box class="md:!p-8 !gap-6" :divider="false" title="Compare plans" subtitle="Limits per plan. Calls per week are counted by endpoint category." link-text="Detailed endpoint limits" link-href="/Api/EndpointLimits">
        <div class="overflow-clip">
          <table class="min-w-0 w-full responsive-table rounded-table table-fixed">
            <colgroup>
              <col class="w-[51.5%]">
              <col v-for="plan in plans" :key="plan.key">
            </colgroup>
            <thead>
              <tr>
                <th class="py-2 px-6 text-left text-sm">Feature</th>
                <th v-for="plan in plans" :key="plan.key" class="py-2 px-3 text-center text-sm">{{ plan.name }}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="h-14 py-2 px-6">Return data in JSON or CSV format</td>
                <td v-for="plan in plans" :key="plan.key" class="h-14 py-2 px-3 text-center"><basil-icon name="check" class="text-teal text-2xl"></basil-icon></td>
              </tr>
              <tr>
                <td class="h-14 py-2 px-6">
                  <span class="inline-flex items-center gap-2">
                    Direct endpoint integration
                    <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                      <slot><p class="text-sm p-2 text-left">{{ directIntegrationInfo }}</p></slot>
                    </round-image>
                  </span>
                </td>
                <td v-for="plan in plans" :key="plan.key" class="h-14 py-2 px-3 text-center">
                  <basil-icon v-if="plan.key === 'developer'" name="check" class="text-teal"></basil-icon>
                  <basil-icon v-else name="cross" class="text-subtext text-xl"></basil-icon>
                </td>
              </tr>
              <tr>
                <td class="h-14 py-2 px-6">Calls per minute</td>
                <td class="h-14 py-2 px-3 text-center">60</td>
                <td class="h-14 py-2 px-3 text-center">60</td>
                <td class="h-14 py-2 px-3 text-center">120</td>
              </tr>
              <tr>
                <td class="h-14 py-2 px-6">
                  <span class="inline-flex items-center gap-2">
                    Active jobs
                    <round-image size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true">
                      <slot><p class="text-sm p-2 text-left">{{ activeJobsInfo }}</p></slot>
                    </round-image>
                  </span>
                </td>
                <td class="h-14 py-2 px-3 text-center">3</td>
                <td class="h-14 py-2 px-3 text-center">5</td>
                <td class="h-14 py-2 px-3 text-center">10</td>
              </tr>
              <tr>
                <td :colspan="plans.length + 1" class="py-2 px-6 text-xs font-bold uppercase text-subtext">Calls per week by endpoint category</td>
              </tr>
              <tr v-for="row in pricingData" :key="row.title">
                <td class="h-14 py-2 px-6">{{ row.title }}</td>
                <td class="h-14 py-2 px-3 text-center">{{ row.basic }}</td>
                <td class="h-14 py-2 px-3 text-center">{{ row.intermediate }}</td>
                <td class="h-14 py-2 px-3 text-center">{{ row.developer }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </panel-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiHome',
  components: {
  },
  props: {
    authenticated: Boolean,
    plans: {
      type: Array,
      default: () => [],
    },
    pricingData: {
      type: Array,
      default: () => [],
    },
  },
  data(){
    return {
      directIntegrationInfo: "Calling the API live each time one of your users loads a page or clicks a button. Without it, your project must store what it retrieves and serve its users from that copy, so their activity never reaches our API.",
      activeJobsInfo: "A call that isn't cached starts a job and answers 202. That job is active until it finishes, and this is how many your account can have active at once. Cached answers come straight back and don't count.",
      taglines: {
        basic: 'For one player',
        intermediate: 'For small apps',
        developer: 'For apps & websites',
      },
      features: {
        basic: [
          { text: 'Ideal for keeping track of your data in a spreadsheet', included: true },
          { text: 'Limited Global and player stats calls per week', included: true },
          { text: 'Results in JSON or CSV', included: true },
          { text: 'Direct endpoint integration', included: false },
          { text: 'Player Analytics for your Battle.net account', included: true },
        ],
        intermediate: [
          { text: 'Ideal for small applications', included: true },
          { text: 'More Global and player stats calls per week', included: true },
          { text: 'Results in JSON or CSV', included: true },
          { text: 'Direct endpoint integration', included: false },
          { text: 'Player Analytics for your Battle.net account', included: true },
        ],
        developer: [
          { text: 'Ideal for building an app or website', included: true },
          { text: 'Most calls per week for consistent data', included: true },
          { text: 'Results in JSON or CSV', included: true },
          { text: 'Direct endpoint integration', included: true },
          { text: 'Player Analytics for your Battle.net account', included: true },
        ],
      },
    }
  },
  methods: {
    ctaHref(key){
      if(key === 'developer'){
        return '/Api/DeveloperTier';
      }
      return this.authenticated ? '/Api/Account' : '/Api/Register';
    },
    ctaText(key){
      if(key === 'developer'){
        return 'Contact Us';
      }
      return this.authenticated ? 'Go To Your Account' : 'Get Started';
    },
    colorFor(key){
      return { basic: 'teal', intermediate: 'blue', developer: 'red' }[key];
    },
    borderClass(key){
      return { basic: 'border-teal', intermediate: 'border-blue', developer: 'border-red' }[key];
    },
  },
}
</script>
