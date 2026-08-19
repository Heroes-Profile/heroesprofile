<template>
  <div>
    <page-heading :infoText1="infoText" heading="Account"></page-heading>

    <div class="mx-auto max-w-[900px] p-4">
      <div v-if="!account.migrated" class="bg-lighten border-l-4 border-yellow p-4 mb-8">
        <h2 class="text-lg mb-2">You Are Receiving Test Data</h2>
        <p class="text-sm mb-2">
          Calls to the new API return fixed example responses with the correct shape but
          placeholder values. They do not count against your quota, so you can build and
          test freely.
        </p>
        <p class="text-sm text-gray-medium">
          Activating live data will expire your existing API key on the old site. Do that
          once your integration is ready.
        </p>
      </div>

      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-2">API Keys</h2>
        <p class="text-sm text-gray-medium">Key management arrives with the next release.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiAccount',
  components: {
  },
  props: {
    account: {
      type: Object,
      required: true
    },
  },
  data(){
    return {
      infoText: this.account.email,
    }
  },
}
</script>
