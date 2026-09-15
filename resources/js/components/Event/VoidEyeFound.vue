<template>
  <transition name="void-eye-found" appear>
    <div v-if="visible" class="void-stage-up fixed inset-0 z-[9999] flex items-center justify-center px-6">
      <div class="void-stage-up-card relative text-center text-white rounded-lg px-8 py-8 max-w-lg w-full">
        <img src="/images/event/xalatath/void-eye.svg" alt="" :class="['w-28 h-28 mx-auto mb-4 block', { 'void-eye-claiming': status === 'claiming' }]" />

        <template v-if="status === 'claiming'">
          <h2 class="font-logo text-3xl md:text-4xl mb-3">The Eye Opens…</h2>
          <p class="opacity-75">Xal'atath is looking at you.</p>
        </template>

        <template v-else-if="status === 'claimed'">
          <h2 class="font-logo text-3xl md:text-4xl mb-3">You Have Been Marked</h2>
          <p class="mb-6">You found Xal'atath's eye. The Eye of the Void now shows beside your battletag, and Heroes Profile is ad-free for you for the next 3 months.</p>
          <custom-button :text="'Continue'" :size="'small'" :ignoreclick="true" class="px-8" @click="close"></custom-button>
        </template>

        <template v-else>
          <h2 class="font-logo text-3xl md:text-4xl mb-3">She Sees You</h2>
          <p class="mb-6">You found Xal'atath's eye. Log in with Battle.net to claim the Eye of the Void flair beside your battletag and 3 months of ad-free Heroes Profile.</p>
          <div class="flex gap-4 justify-center">
            <custom-button :href="'/Authenticate/Battlenet'" :text="'Log in with Battle.net'" :size="'small'" class="px-6"></custom-button>
            <custom-button :text="'Later'" :size="'small'" :ignoreclick="true" color="teal" class="px-6" @click="close"></custom-button>
          </div>
          <p class="text-xs opacity-75 mt-4">Log in during this visit and your find is claimed automatically.</p>
        </template>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'VoidEyeFound',
  props: {
    status: {
      type: String,
      default: 'claimed',
    },
  },
  emits: ['closed'],
  data() {
    return {
      visible: true,
    };
  },
  methods: {
    close() {
      this.visible = false;
      this.$emit('closed');
    },
  },
};
</script>
