<template>
  <!-- Shown until acknowledged in this browser -->
  <modal-box title="Analytics is in pre-alpha" width="max-w-[640px]">
    <p class="mb-3">This is an early, experimental feature. Please keep in mind:</p>
    <ul class="list-disc ml-8 space-y-2">
      <li>The data may not be 100% accurate. Much of it is estimated from replays (for example, hero positions are mostly worked out from where players click).</li>
      <li>The design is not finished. For now the goal is just getting the information on screen so we can work with it.</li>
      <li>
        Not every metric that can be tracked is tracked yet. For more data, contact us on
        <a href="https://www.patreon.com/c/heroesprofile" target="_blank" class="underline">Patreon</a>, at
        <a href="mailto:zemill@heroesprofile.com" class="underline">zemill@heroesprofile.com</a>, or in
        <a href="https://github.com/Heroes-Profile/heroesprofile/discussions/1348" target="_blank" class="underline">this GitHub discussion</a>.
      </li>
      <li>Numbers, views and features will change as we improve it, and some may be wrong or missing along the way.</li>
      <li>All recommendations are experimental. They come from patterns in games, not guarantees, and Heroes Profile takes no responsibility if you lose following them. :)</li>
    </ul>
    <p class="mt-3">Each tab ends with a methodology section giving a brief explanation of how its numbers are calculated.</p>

    <template #footer>
      <custom-button class="ml-auto" color="teal" text="Got it" :ignoreclick="true" @click="closePopup"></custom-button>
    </template>
  </modal-box>
</template>

<script>
// Bump the key when the wording changes enough that everyone should see it again
export const ANALYTICS_DISCLAIMER_KEY = 'analyticsDisclaimerV1';

export default {
  name: 'AnalyticsDisclaimerPopup',
  emits: ['popupclosed'],
  methods: {
    closePopup() {
      try {
        localStorage.setItem(ANALYTICS_DISCLAIMER_KEY, 'true');
      } catch (e) {
        // Storage blocked: it just shows again next visit
      }
      this.$emit('popupclosed');
    },
  },
};
</script>
