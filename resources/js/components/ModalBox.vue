<template>
  <!-- A full-screen popup: header and footer stay on screen, only the body scrolls -->
  <div class="modal-mask">
    <div class="modal-wrapper">
      <div :class="['modal-container bg-surface-4 rounded-2xl p-10 max-md:p-4 w-[90%]', width]">
        <div class="modal-header">
          <h3 v-if="title" class="text-3xl max-md:text-2xl font-bold text-center mb-6">{{ title }}</h3>
          <slot name="header"></slot>
        </div>

        <div class="modal-body">
          <slot></slot>
        </div>

        <div class="modal-footer flex flex-col gap-6">
          <slot name="footer"></slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalBox',
  props: {
    title: String,
    width: { type: String, default: 'max-w-[59rem]' },
  },
  mounted() {
    document.body.style.overflow = 'hidden';
  },
  beforeUnmount() {
    document.body.style.overflow = '';
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
  height: 100vh;
  /* Phones: the visible height, not the height behind the address bar */
  height: 100dvh;
  background-color: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow-y: auto;
}

.modal-wrapper {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
  min-height: 100%;
}

.modal-container {
  margin: 0 auto;
  max-height: calc(100vh - 2.5rem);
  max-height: calc(100dvh - 2.5rem);
  display: flex;
  flex-direction: column;
  box-shadow: 0 2px 0.5rem rgba(0, 0, 0, 0.33);
}

.modal-body {
  margin: 1.25rem 0;
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
}
</style>
