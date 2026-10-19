<template>
  <div class="mb-3">
    <h2 class="text-xl font-bold text-teal">
      <span class="inline-flex items-center gap-1">
        {{ title }}
        <round-image v-if="$slots.default" size="small" icon="fas fa-info" title="info" popupsize="large" mobileClick="true" :hidedelay="1000">
          <div class="text-sm p-2 text-left"><slot></slot></div>
        </round-image>
      </span>
    </h2>
    <!-- The answer, before any detail -->
    <p v-if="takeaway" class="text-base mt-1">{{ takeaway }}</p>
  </div>
</template>

<script>
// A section heading for the analytics tabs: the title, how it's worked out behind the info icon, and a
// one-line answer under it so players don't have to read to get to it.
export default {
  name: 'AnalyticsHeading',
  props: {
    title: String,
    takeaway: String,
  },
};
</script>
