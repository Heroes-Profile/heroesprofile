<template>
  <!-- Same modal as NewUserPopup; shown until acknowledged in this browser -->
  <div class="modal-mask">
    <div class="modal-wrapper">
      <div class="modal-container bg-blue rounded-lg p-10 max-w-[520px] w-[90%] text-sm">
        <div class="modal-header">
          <h3 class="text-xl font-bold">Analytics is in pre-alpha</h3>
        </div>

        <div class="modal-body">
          <p class="mb-3">This is an early, experimental feature. Please keep in mind:</p>
          <ul class="list-disc ml-4 space-y-2">
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
        </div>

        <div class="modal-footer flex">
          <button class="bg-teal p-2 rounded-lg ml-auto" @click="closePopup">Got it</button>
        </div>
      </div>
    </div>
  </div>
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

<style scoped>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.9);
  display: table;
  transition: opacity 0.3s ease;
}

.modal-wrapper {
  display: table-cell;
  vertical-align: middle;
}

.modal-container {
  margin: 0px auto;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
  transition: all 0.3s ease;
}

.modal-body {
  margin: 20px 0;
}
</style>
