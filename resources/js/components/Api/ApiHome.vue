<template>
  <div>
    <page-heading :infoText1="infoText" heading="Heroes Profile API"></page-heading>

    <div class="mx-auto max-w-[1500px] p-4">
      <p class="mb-8">
        Heroes Profile API is a tool used to get Heroes of the Storm data parsed for
        <a href="https://www.heroesprofile.com/" class="underline hover:text-lteal">Heroes Profile</a>.
        We source original replay data from our own
        <a href="/upload" class="underline hover:text-lteal">Uploader</a>, then parse the data to
        provide you with up-to-date calculated MMR, win rates, and more. Your subscription helps us
        create new features for Heroes Profile. View our
        <a href="/Api/Docs" class="underline hover:text-lteal">Documentation</a> or
        <a href="/Api/Register" class="underline hover:text-lteal">Register</a> to get started.
      </p>

      <div class="grid gap-4 md:grid-cols-3 mb-10">
        <div v-for="plan in plans" :key="plan.key" class="bg-lighten flex flex-col">
          <div class="p-3 text-center text-lg" :class="headerClass(plan.key)">
            {{ plan.name }}
          </div>

          <div class="p-4 flex flex-col flex-grow text-center">
            <div class="text-3xl mb-4">
              ${{ plan.price }} <span class="text-sm text-gray-medium">/ mo</span>
            </div>

            <ul class="text-sm text-left space-y-2 mb-6 flex-grow">
              <li v-for="(feature, index) in features[plan.key]" :key="index">{{ feature }}</li>
            </ul>

            <custom-button
              :href="ctaHref(plan.key)"
              :text="ctaText(plan.key)"
              :alt="plan.name"
              :color="colorFor(plan.key)"
            ></custom-button>
          </div>
        </div>
      </div>

      <table class="responsive-table">
        <tbody>
          <tr>
            <td></td>
            <td v-for="plan in plans" :key="plan.key" class="text-center text-white" :class="headerClass(plan.key)">
              {{ plan.name }}
            </td>
          </tr>

          <tr>
            <td>Return data in JSON or CSV format</td>
            <td class="text-center">&#10004;</td>
            <td class="text-center">&#10004;</td>
            <td class="text-center">&#10004;</td>
          </tr>

          <tr>
            <td>Direct endpoint integration</td>
            <td class="text-center">&#10007;</td>
            <td class="text-center">&#10007;</td>
            <td class="text-center">&#10004;</td>
          </tr>

          <tr>
            <td>Calls per minute</td>
            <td class="text-center">60</td>
            <td class="text-center">60</td>
            <td class="text-center">120</td>
          </tr>

          <tr>
            <td colspan="4"><strong>Calls per week by endpoint category:</strong></td>
          </tr>

          <tr v-for="row in pricingData" :key="row.title">
            <td>{{ row.title }}</td>
            <td class="text-center">{{ row.basic }}</td>
            <td class="text-center">{{ row.intermediate }}</td>
            <td class="text-center">{{ row.developer }}</td>
          </tr>

          <tr>
            <td colspan="4" class="text-center">
              <a href="/Api/EndpointLimits" class="underline">View Detailed Endpoint Limits</a>
            </td>
          </tr>

          <tr>
            <td></td>
            <td v-for="plan in plans" :key="plan.key" class="text-center text-white" :class="headerClass(plan.key)">
              {{ plan.name }}
            </td>
          </tr>
        </tbody>
      </table>
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
      infoText: "Hero statistics, talent builds, match data, player profiles and MMR — the same data that powers www.heroesprofile.com.",
      features: {
        basic: [
          'Ideal for an individual player looking to keep track of data in an outside spreadsheet',
          'Limited amount of Global and individual player stats calls per week',
          'Return results in JSON or CSV format',
          'Direct endpoint integration unavailable',
        ],
        intermediate: [
          'Ideal for small use applications',
          'Limited amount of Global and individual player stats calls per week',
          'Return results in JSON or CSV format',
          'Direct endpoint integration unavailable',
        ],
        developer: [
          'Ideal for developers looking to build an app or website',
          'More calls per week, allowing consistent data return',
          'Direct endpoint available',
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
    headerClass(key){
      return { basic: 'bg-teal', intermediate: 'bg-blue', developer: 'bg-red' }[key];
    },
  },
}
</script>
