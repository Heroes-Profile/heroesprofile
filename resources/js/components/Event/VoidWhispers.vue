<template>
  <transition name="void-whisper">
    <div v-if="current" class="void-whisper rounded text-sm italic md:absolute md:left-[calc(50%+1rem)] md:top-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:z-40 md:px-4 md:py-1 md:whitespace-nowrap max-md:fixed max-md:bottom-6 max-md:left-6 max-md:z-50 max-md:max-w-xs max-md:px-4 max-md:py-3">
      {{ current }}
    </div>
  </transition>
</template>

<script>
const WHISPERS = [
  'You check these numbers as if they could save you.',
  'Every replay you upload feeds the Void. Thank you.',
  'Such careful statistics. Such fragile heroes.',
  'Your win rate is showing. How embarrassing.',
  'I have seen empires fall. Your ladder is nothing.',
  'Refresh the page. I will still be here.',
  'The Nexus bends. Soon it breaks.',
  'You call it a losing streak. I call it tribute.',
  'Keep searching for your battletag. The Void already knows it.',
  'Balance patches cannot seal what has already awakened.',
  'Pick a counter if you like. It amuses me.',
  'Your talents are adequate. Your fate is not.',
  'Another loss uploaded. The Void savors every one.',
  'You queued again. Of course you did.',
  'Your MMR rises and falls. The Void only rises.',
  'Check your match history. I was there for all of it.',
  'Stealth will not hide you from what watches between the stars.',
  'Every core that falls makes my voice louder.',
  'You ban me, and still I spread. Delightful.',
  'Your party of five cannot outvote eternity.',
  'I do not need a meta. I am the end of all metas.',
  'Leaderboards are just lists of whom I devour first.',
  'The dagger is empty now. Look behind you.',
  'Such a lovely little Nexus. It will shatter beautifully.',
  'Uploading the same win twice? The Void counts only once.',
  'Your alt account climbs. The Void sees one soul.',
  'Trading wins with a friend? Cute. I trade in worlds.',
  'Edit the replay if you like. Truth leaks through the cracks.',
  'You cheated your way up the ladder. Now climb out of the Void.',
  'Hidden accounts, borrowed rank. I see every mask you wear.',
  'Dodge the queue all you want. You cannot dodge me.',
  'A rank you did not earn is the easiest thing to take.',
];

export default {
  name: 'VoidWhispers',
  data() {
    return {
      current: null,
      timers: [],
    };
  },
  mounted() {
    this.schedule(10000);
  },
  beforeUnmount() {
    this.timers.forEach(clearTimeout);
  },
  methods: {
    schedule(delay) {
      this.timers.push(setTimeout(() => {
        this.current = WHISPERS[Math.floor(Math.random() * WHISPERS.length)];
        // VoidGlitch syncs the nav to these.
        window.dispatchEvent(new CustomEvent('void-whisper-show'));
        this.timers.push(setTimeout(() => {
          this.current = null;
          window.dispatchEvent(new CustomEvent('void-whisper-hide'));
          this.schedule(10000);
        }, 5000));
      }, delay));
    },
  },
};
</script>
