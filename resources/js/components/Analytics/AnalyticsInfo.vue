<template>
  <div>
    <page-heading
      :heading="'Analytics (Pre-Alpha)'"
      :infoText1="'Deep replay analysis of your own season games.'"
    ></page-heading>

    <div class="max-w-[900px] mx-auto px-4 mt-4 mb-10">
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
        While we test it, Player Analytics is only open to Patreon supporters. To check it out, subscribe on Patreon at any tier, then link your Patreon account to your Heroes Profile account.
      </p>
      <p v-if="patreonLinked" class="mb-4">
        Your Patreon account is linked, but we don't see an active subscription on it yet.
      </p>

      <div class="flex flex-col md:flex-row gap-4 justify-center mt-6">
        <custom-button :href="'https://www.patreon.com/heroesprofile'" :text="'Subscribe on Patreon'" :alt="'Subscribe on Patreon'" :size="'big'" :color="'teal'" :targetblank="true"></custom-button>
        <custom-button v-if="!patreonLinked" :href="'/authenticate/patreon'" :text="loggedIn ? 'Link Patreon' : 'Log in and link Patreon'" :alt="'Link Patreon'" :size="'big'"></custom-button>
      </div>
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
  },
}
</script>
