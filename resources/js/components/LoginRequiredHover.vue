<template>
  <!-- A locked control: hovering (or tapping) it says why, with a button into the login popup -->
  <icon-with-hover popupsize="fit">
    <template #trigger><slot></slot></template>
    <div class="flex flex-col items-start gap-3">
      <p class="font-bold text-white">{{ $t('Login required') }}</p>
      <p>{{ $t(message) }}</p>
      <custom-button :href="'/Authenticate/Battlenet'" :text="'Log in with Battle.net'" :alt="'Log in with Battle.net'" :color="'blue'" :size="'small'"></custom-button>
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
