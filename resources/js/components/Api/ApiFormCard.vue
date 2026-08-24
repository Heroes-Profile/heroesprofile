<template>
  <div>
    <page-heading :infoText1="infoText1" :heading="heading"></page-heading>

    <div class="mx-auto max-w-[480px] p-4">
      <div v-if="status" class="bg-teal p-3 text-center mb-4">
        {{ status }}
      </div>

      <div v-if="errors.length" class="bg-red p-3 mb-4">
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
        </ul>
      </div>

      <div class="bg-lighten p-6">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiFormCard',
  components: {
  },
  props: {
    heading: String,
    infoText1: String,
    errors: {
      type: Array,
      default: () => [],
    },
    status: String,
  },
}
</script>
