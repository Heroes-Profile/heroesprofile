<template>
  <div>
    <button
      v-if="!found"
      type="button"
      class="void-hidden-eye absolute z-30"
      :style="{ top: spot.top + '%', left: spot.left + '%' }"
      aria-label="A strange eye"
      @click="claim"
    >
      <img src="/images/event/xalatath/void-eye.svg" alt="" class="w-12 h-12 block" />
    </button>
    <void-eye-found v-if="result" :status="result" @closed="result = null"></void-eye-found>
  </div>
</template>

<script>
export default {
  name: 'VoidHiddenEye',
  props: {
    spot: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      found: false,
      result: null,
      claiming: false,
    };
  },
  methods: {
    async claim() {
      if (this.claiming) {
        return;
      }
      this.claiming = true;
      try {
        const response = await this.$axios.post('/Event/Xalatath/Eye', { token: this.spot.token });
        this.found = true;
        this.result = response.data.status;
      } catch (error) {
        // Expired or already used; let it vanish quietly.
        this.found = true;
      } finally {
        this.claiming = false;
      }
    },
  },
};
</script>
