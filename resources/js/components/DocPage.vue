<template>
  <!-- A long text page (the legal pages, the streamer code of conduct): an "On this page" card beside numbered sections, each section's text in the slot named by its id -->
  <div>
    <page-heading :heading="heading" :subheading="subheading"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 pb-10 grid md:grid-cols-[18rem_minmax(0,1fr)] xl:grid-cols-[20rem_minmax(0,1fr)] gap-6 items-start">
      <nav class="md:sticky md:top-4 rounded-panel bg-surface p-4 flex flex-col gap-1 text-sm">
        <span class="px-2 pb-2 text-[0.6875rem] font-bold uppercase tracking-wider text-subtext">{{ $t('On this page') }}</span>
        <a v-for="(section, index) in sections" :key="section.id" :href="'#' + section.id" :class="['flex gap-3 rounded px-2 py-2 transition-colors', active === section.id ? 'bg-teal/15 text-lteal' : 'hover:bg-surface-1']">
          <span :class="['text-xs tabular-nums pt-px', active === section.id ? '' : 'text-subtext']">{{ String(index + 1).padStart(2, '0') }}</span>
          <span>{{ sectionTitle(section) }}</span>
        </a>
        <template v-if="links.length">
          <div class="h-px bg-gray-dark my-2"></div>
          <a v-for="link in links" :key="link.href" :href="link.href" class="flex items-center justify-between gap-3 rounded px-2 py-2 hover:bg-surface-1 transition-colors">
            <span>{{ link.text }}</span><basil-icon name="arrow-small" class="text-xs shrink-0"></basil-icon>
          </a>
        </template>
      </nav>

      <div class="flex flex-col gap-6 min-w-0">
        <slot name="notice"></slot>
        <slot name="intro"></slot>
        <!-- cards: each section its own panel, numbered only where it has a step, with an optional pill on the right -->
        <template v-if="cards">
          <section v-for="section in sections" :id="section.id" :key="section.id" ref="sections" class="scroll-mt-4 rounded-panel bg-surface p-5 md:p-8 flex flex-col gap-4 text-subtext">
            <div class="flex items-center gap-4">
              <span v-if="section.step" class="shrink-0 w-8 h-8 rounded bg-teal/15 text-lteal flex items-center justify-center text-xs font-bold">{{ section.step }}</span>
              <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold text-white">{{ sectionTitle(section) }}</h2>
                <p v-if="section.subtitle" class="text-sm mt-1">{{ section.subtitle }}</p>
              </div>
              <span v-if="section.badge" class="shrink-0 rounded-lg bg-surface-3 px-2.5 py-1 text-xs">{{ section.badge }}</span>
            </div>
            <slot :name="section.id"></slot>
          </section>
        </template>
        <div v-else class="rounded-panel bg-surface p-5 md:p-8 flex flex-col divide-y divide-gray-dark">
          <section v-for="(section, index) in sections" :id="section.id" :key="section.id" ref="sections" class="scroll-mt-4 flex gap-4 py-6 first:pt-0 last:pb-0">
            <span class="shrink-0 w-8 h-8 rounded bg-teal/15 text-lteal flex items-center justify-center text-xs font-bold">{{ index + 1 }}</span>
            <div class="flex-1 min-w-0 flex flex-col gap-3 text-subtext">
              <h2 class="text-xl font-bold text-white">{{ sectionTitle(section) }}</h2>
              <slot :name="section.id"></slot>
            </div>
          </section>
        </div>
        <p v-if="footer" class="text-xs text-subtext">{{ footer }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DocPage',
  props: {
    heading: String,
    subheading: String,
    // [{ id, title }]; a leading "1. " in a title is dropped, the number shows beside it
    sections: { type: Array, required: true },
    // One panel per section; sections may then add step (its number), subtitle and badge (a pill such as Optional)
    cards: Boolean,
    // Related pages under the section links: [{ text, href }]
    links: { type: Array, default: () => [] },
    footer: String,
  },
  data() {
    return {
      // The section being read, highlighted in the side card
      active: null,
    };
  },
  mounted() {
    this.active = this.sections[0]?.id ?? null;
    window.addEventListener('scroll', this.trackActive, { passive: true });
    this.trackActive();
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.trackActive);
  },
  methods: {
    sectionTitle(section) {
      return String(section.title).replace(/^\d+\.\s*/, '');
    },
    // The last section whose top has passed the upper part of the window; the last one once the page bottoms out
    trackActive() {
      const sections = this.$refs.sections || [];
      if (!sections.length) return;
      if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
        this.active = this.sections[this.sections.length - 1].id;
        return;
      }
      let current = this.sections[0].id;
      sections.forEach((element, index) => {
        if (element.getBoundingClientRect().top <= window.innerHeight * 0.3) {
          current = this.sections[index].id;
        }
      });
      this.active = current;
    },
  },
};
</script>
