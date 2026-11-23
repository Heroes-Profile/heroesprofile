<template>
  <!-- A locked control: hovering (or tapping) it says why, with a button into the login popup -->
  <icon-with-hover popupsize="fit" :popup-hoverable="true">
    <template #trigger><slot></slot></template>
    <div class="flex flex-col gap-3.5 text-base">
      <p class="text-lg font-bold text-white">{{ $t('Login required') }}</p>
      <p class="text-subtext">{{ $t(message) }}</p>
      <custom-button :href="'/Authenticate/Battlenet'" :text="'Log in with Battle.net'" :alt="'Log in with Battle.net'" :color="'blue'" class="w-full font-bold"></custom-button>
    </div>
  </icon-with-hover>
</template>

<script>
export default {
  name: 'LoginRequiredHover',
  props: {
    message: { type: String, required: true },
  },
};
</script>
