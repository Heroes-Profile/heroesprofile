<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="'Deep replay analysis of your own season games.'" -->
    <page-heading
      :heading="'Analytics (Pre-Alpha)'"
    ></page-heading>

    <div class="max-w-[56.25rem] mx-auto px-4 mt-4 mb-10">
      <p class="mb-4">
        Player Analytics breaks down your own season games (from Oct 2, 2026) from their replays and compares you with a sample of all games:
      </p>
      <ul class="list-disc list-inside mb-6 space-y-1">
        <li>Where and how you die</li>
        <li>Where you spend your time on each map</li>
        <li>How you do when your team is ahead or behind</li>
        <li>Coaching on your habits</li>
        <li>A replay viewer for every game</li>
      </ul>

      <p class="mb-4">
        See what it looks like on a sample of Zemill's games.
      </p>
      <div class="flex justify-center mb-8">
        <custom-button :href="'/Analytics/Sample'" :text="'See a sample'" :alt="'See a sample of Player Analytics'" :size="'big'"></custom-button>
      </div>

      <!-- The free trial: once per Battle.net account, started by the player -->
      <div v-if="!trialEnded" class="bg-darken rounded p-4 mb-8 text-center">
        <p class="text-lg font-bold mb-2">{{ trialStage }} free trial: try it free for {{ trialDays }} days</p>
        <p class="mb-4">
          Player Analytics is in {{ (trialStage || '').toLowerCase() }}, and every Battle.net account gets a free {{ trialDays }}-day trial of it. The trial starts when you press the button, not before, and processes your newest {{ freeGames }} games. Each new stage of development (alpha, beta and on) comes with a new free trial, so you can come back and see what changed.
        </p>
        <custom-button
          v-if="loggedIn"
          :text="'Start my ' + trialDays + '-day free trial'"
          :alt="'Start your free trial of Player Analytics'"
          :size="'big'"
          :color="'teal'"
          :loading="starting"
          :disabled="starting"
          :ignoreclick="true"
          @click="startTrial"
        ></custom-button>
        <custom-button v-else :href="'/Authenticate/Battlenet'" :text="'Log in to start your free trial'" :alt="'Log in with Battle.net'" :size="'big'" :color="'teal'"></custom-button>
        <p v-if="error" class="text-red mt-3">{{ error }}</p>
      </div>
      <p v-else class="bg-darken rounded p-4 mb-8 text-center">
        Your {{ (trialStage || '').toLowerCase() }} free trial has ended. Your processed games are kept, so everything is there again as soon as you subscribe, and the next stage of development will come with a new free trial.
      </p>

      <p class="mb-4">
        {{ trialEnded ? 'To keep using Player Analytics' : 'After the trial, to keep using it' }}, subscribe on Patreon at any tier, then link your Patreon account to your Heroes Profile account.
      </p>
      <p v-if="patreonLinked" class="mb-4">
        Your Patreon account is linked, but we don't see an active subscription on it yet.
      </p>

      <div class="flex flex-col md:flex-row gap-4 justify-center mt-6">
        <custom-button :href="'https://www.patreon.com/heroesprofile'" :text="'Subscribe on Patreon'" :alt="'Subscribe on Patreon'" :size="'big'" :color="'teal'" :targetblank="true"></custom-button>
        <custom-button v-if="!patreonLinked" :href="'/authenticate/patreon'" :text="loggedIn ? 'Link Patreon' : 'Log in and link Patreon'" :alt="'Link Patreon'" :size="'big'"></custom-button>
      </div>
      <p class="text-center mt-6">
        Building something with our data? Any paid <a class="link" href="/Api">Heroes Profile API</a> plan includes
        Player Analytics too: link your Battle.net account on your <a class="link" href="/Api/Account#analytics">API account page</a>.
      </p>
      <p class="text-sm italic text-center mt-4">
        For new subscribers, it may take 10-15 minutes for the site to recognize your subscription.
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AnalyticsInfo',
  props: {
    loggedIn: Boolean,
    patreonLinked: Boolean,
    // The account hasn't started its one free trial yet / started it and it has run out
    trialAvailable: Boolean,
    trialEnded: Boolean,
    trialDays: { type: Number, default: 7 },
    // Games processed free per stage (config analytics.free_games)
    freeGames: { type: Number, default: 100 },
    // The development stage the trial is for (Pre-alpha, Alpha...); each stage has its own trial
    trialStage: String,
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
}
</script>
