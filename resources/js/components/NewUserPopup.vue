<template>
  <modal-box title="Heroes Profile Uploader">
    <template #header>
      <p>
        In order to view accurate data for your matches, regularly upload via the <a class="underline hover:text-header" href="/Upload" target="_blank">Heroes Profile Uploader</a>.
      </p>
    </template>

    <ul class="list-disc ml-8 space-y-1">
      <li>Automatically uploads replays</li>
      <li>Pre-match Game Analysis</li>
      <li>Post-match Game Analysis</li>
    </ul>
    <img src="/images/miscellaneous/uploader.png" alt="The Heroes Profile replay uploader" class="w-full mt-6 rounded-[0.5rem]" />

    <template #footer>
      <custom-button class="ml-auto" color="teal" text="Close" :ignoreclick="true" @click="closePopup"></custom-button>
    </template>
  </modal-box>
</template>

<script>
export default {
  name: 'NewUserPopup',
  emits: ['popupclosed'],
  methods: {
    closePopup() {
      localStorage.setItem('newUserPopup', 'true');
      this.$emit('popupclosed');
    },
  }
}
</script>
