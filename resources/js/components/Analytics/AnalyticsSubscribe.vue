<template>
  <div class="text-center">
    <p class="text-lg font-bold mb-2">{{ title }}</p>
    <p class="mb-4 max-w-[40em] mx-auto">{{ text }}</p>
    <div class="flex flex-wrap justify-center gap-2">
      <custom-button
        v-if="trialAvailable"
        :text="'Start my ' + trialDays + '-day free trial'"
        :alt="'Start your free trial of Player Analytics'"
        :color="'teal'"
        :loading="starting"
        :disabled="starting"
        :ignoreclick="true"
        @click="startTrial"
      ></custom-button>
      <template v-else>
        <custom-button :href="'https://www.patreon.com/heroesprofile'" :text="'Subscribe on Patreon'" :alt="'Subscribe on Patreon'" :color="'teal'" :targetblank="true"></custom-button>
        <custom-button v-if="!patreonLinked" :href="'/authenticate/patreon'" :text="'Link Patreon'" :alt="'Link Patreon'"></custom-button>
      </template>
    </div>
    <p v-if="!trialAvailable" class="text-sm mt-3">
      {{ $t('Or with any paid') }} <a class="link" href="/Api">{{ $t('Heroes Profile API') }}</a> plan: link your Battle.net account on your
      <a class="link" href="/Api/Account#analytics">{{ $t('API account page') }}</a>.
    </p>
    <p v-if="!trialAvailable" class="text-sm italic mt-3">{{ $t('For new subscribers, it may take 10-15 minutes for the site to recognize your subscription.') }}</p>
    <p v-if="error" class="text-red mt-3">{{ error }}</p>
  </div>
</template>

<script>
// The ways into Player Analytics for someone without access: their free trial if they haven't
// used it, otherwise Patreon or a paid API plan. Starting the trial reloads the page so everything unlocks.
export default {
  name: 'AnalyticsSubscribe',
  props: {
    title: String,
    text: String,
    trialAvailable: Boolean,
    trialDays: { type: Number, default: 7 },
    patreonLinked: Boolean,
  },
  data() {
    return {
      starting: false,
      error: '',
    };
  },
  methods: {
    async startTrial() {
      this.starting = true;
      this.error = '';
      try {
        await this.$axios.post('/Analytics/Trial');
        window.location.reload();
      } catch (e) {
        this.error = 'Could not start your trial. Please try again in a minute.';
        this.starting = false;
      }
    },
  },
};
</script>
