<template>
  <!-- The methods paper for the open tab: abstract always shown, the full paper on request -->
  <article class="max-w-[1100px] mx-auto mt-16 mb-10 px-4">
    <header class="border-t border-gray-light pt-6 mb-4">
      <p class="text-xs uppercase text-gray-light mb-1">Methods paper</p>
      <h2 class="text-2xl font-bold mb-1">{{ paper.title }}</h2>
      <p class="text-sm text-gray-light">Heroes Profile Analytics &middot; {{ paper.version }}</p>
    </header>

    <section class="mb-4">
      <h3 class="text-lg font-bold mb-1">Abstract</h3>
      <p class="text-sm leading-relaxed">{{ paper.abstract }}</p>
    </section>

    <custom-button
      @click="open = !open"
      :text="open ? 'Hide the full paper' : 'Read the full paper'"
      :alt="open ? 'Hide the full paper' : 'Read the full paper'"
      size="small"
      :ignoreclick="true"
    ></custom-button>

    <div v-if="open" class="mt-6">
      <section v-for="(section, index) in sections" :key="index" class="mb-6">
        <h3 class="text-lg font-bold mb-2">{{ index + 1 }}. {{ section.heading }}</h3>
        <template v-for="(block, b) in section.body" :key="b">
          <p v-if="typeof block === 'string'" class="text-sm leading-relaxed mb-2">{{ block }}</p>
          <p v-else-if="block.formula" class="text-sm font-mono bg-darken rounded px-3 py-2 mb-2 overflow-x-auto">{{ block.formula }}</p>
          <ul v-else-if="block.list" class="list-disc ml-6 text-sm leading-relaxed mb-2">
            <li v-for="(item, i) in block.list" :key="i">{{ item }}</li>
          </ul>
        </template>
      </section>
    </div>
  </article>
</template>

<script>
import { SHARED_DATA } from '../../utils/analyticsPapers.js';

export default {
  name: 'AnalyticsPaper',
  props: {
    // { title, version, abstract, sections: [{ heading, body: [string | { formula } | { list }] }] }
    paper: Object,
  },
  data() {
    return {
      open: false,
    };
  },
  computed: {
    // Every paper's Data section starts with how the games are collected and sampled
    sections() {
      return this.paper.sections.map(section => (section.shared ? { heading: section.heading, body: [...SHARED_DATA, ...section.body] } : section));
    },
  },
};
</script>
