<template>
  <div>
    <page-heading :heading="'Auto Battler'" :subheading="'Set up a 5v5 auto battle'"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <auto-battler-setup-guide></auto-battler-setup-guide>

      <!-- How many players are ready, a segment each in the team's colour, and the download -->
      <div id="auto-battler" class="bg-surface rounded p-4 md:px-6 flex flex-wrap items-center gap-4 scroll-mt-4">
        <div class="flex-1 min-w-[16rem] flex flex-col gap-2">
          <div class="flex flex-wrap justify-between gap-2 text-xs">
            <span class="font-semibold">{{ $t(':completedslotcount / 10 players ready', { completedslotcount: completedSlotCount }) }}</span>
            <span class="text-subtext">{{ allSlotsComplete ? $t('Everyone is locked in') : $t('Pick a hero and lock in talents for each player') }}</span>
          </div>
          <div class="grid grid-cols-10 gap-1">
            <span v-for="segment in segments" :key="segment.key" :class="['h-1 rounded-full', segment.ready ? (segment.team === 1 ? 'bg-teal' : 'bg-red') : 'bg-surface-3']"></span>
          </div>
        </div>
        <custom-button color="teal" class="font-semibold" :ignoreclick="true" :disabled="!allSlotsComplete" :alt="'Download files'" @click="downloadFiles">
          <basil-icon name="download" class="mr-2"></basil-icon>{{ $t('Download files') }}
        </custom-button>
      </div>

      <div class="grid xl:grid-cols-[320fr_1220fr_320fr] gap-6 items-start">
        <!-- Team 1, then the hero board or talents, then Team 2 -->
        <div v-for="team in [1, 2]" :key="team" :class="['bg-surface rounded p-4 md:p-6 flex flex-col gap-4 min-w-0', team === 1 ? 'xl:order-1' : 'xl:order-3']">
          <h3 class="flex items-center gap-2.5 text-xl font-bold">
            <span :class="['w-1 h-5 rounded-full', team === 1 ? 'bg-teal' : 'bg-red']"></span>{{ $t('Team :team', { team: team }) }}
          </h3>
          <div class="flex flex-col gap-2">
            <team-slot v-for="index in 5" :key="index" class="cursor-pointer hover:bg-surface-3"
              :color="team === 1 ? 'teal' : 'red'"
              :hero="slots[team][index].hero"
              :active="mode !== null && isActiveSlot(team, index)"
              :title="$t('Player :number', { number: index })"
              :status="slotStatus(team, index)"
              :status-highlight="mode !== null && isActiveSlot(team, index) && !slots[team][index].talents"
              @click="selectSlot(team, index)">
              <span v-if="slots[team][index].talents" class="w-4 h-4 rounded-[0.25rem] bg-teal flex items-center justify-center text-[0.625rem]" :title="$t('Talents locked in')"><basil-icon name="check"></basil-icon></span>
            </team-slot>
          </div>
        </div>

        <hero-board v-if="mode === 'hero'" class="xl:order-2" :heroes="heroes" @pick="clickedHero">
          <template #header>
            <div class="flex flex-col gap-1">
              <h2 class="text-xl font-bold">{{ $t('Choose a hero for Player :number', { number: activeSlot.index }) }}</h2>
              <span class="text-sm text-subtext">{{ $t('Team :team · then pick talents and lock in.', { team: activeSlot.team }) }}</span>
            </div>
          </template>
        </hero-board>

        <div v-else-if="mode === 'talents'" class="bg-surface rounded p-4 md:p-6 min-w-0 xl:order-2">
          <loading-component v-if="loadingTalents" :compact="true" :textoverride="true">{{ $t('Loading talents...') }}</loading-component>
          <div v-else-if="talentError" class="text-center text-red py-10">{{ $t(talentError) }}</div>
          <auto-battler-talent-selection v-else
            :key="activeSlot.team + '-' + activeSlot.index + '-' + activeHero.name"
            :hero="activeHero"
            :talents="talentCache[activeHero.name]"
            :initial="slots[activeSlot.team][activeSlot.index].talents"
            :layout="effectiveTalentStyle"
            :isMobile="isMobile"
            :label="$t('Team :team', { team: activeSlot.team }) + ' · ' + $t('Player :number', { number: activeSlot.index })"
            @layout-change="talentStyle = $event"
            @locked="lockTalents"
            @change-hero="mode = 'hero'"
          ></auto-battler-talent-selection>
        </div>

        <!-- Everyone locked in -->
        <div v-else class="bg-surface rounded p-6 min-w-0 xl:order-2 xl:self-stretch flex flex-col items-center justify-center gap-4 text-center py-16">
          <span class="w-16 h-16 rounded-full bg-teal/20 flex items-center justify-center text-2xl"><basil-icon name="check"></basil-icon></span>
          <h2 class="text-2xl font-bold">{{ $t('Everyone is locked in') }}</h2>
          <p class="text-sm text-subtext max-w-[28rem]">{{ $t('All 10 players picked a hero and talents. Download the files and follow "How to set this up" to start the battle.') }}</p>
          <div class="flex flex-wrap justify-center gap-3">
            <custom-button color="teal" class="font-semibold" :ignoreclick="true" :alt="'Download files'" @click="downloadFiles"><basil-icon name="download" class="mr-2"></basil-icon>{{ $t('Download files') }}</custom-button>
            <custom-button class="font-semibold" :ignoreclick="true" :alt="'Edit a player'" @click="selectSlot(1, 1)">{{ $t('Edit a player') }}</custom-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const TALENT_LEVELS = [1, 4, 7, 10, 13, 16, 20];

export default {
  name: 'AutoBattler',
  props: {
    heroes: {
      type: Array,
      required: true,
    },
    patreonUser: {
      type: Boolean,
      default: false,
    },
    talentbuilderstyle: {
      type: String,
      default: 'vertical',
    },
  },
  data() {
    const slots = {};
    for (const team of [1, 2]) {
      slots[team] = {};
      for (let index = 1; index <= 5; index++) {
        slots[team][index] = { hero: null, talents: null };
      }
    }
    return {
      slots,
      activeSlot: { team: 1, index: 1 },
      mode: 'hero',
      talentCache: {},
      loadingTalents: false,
      talentError: null,
      talentStyle: this.talentbuilderstyle || 'vertical',
      isMobile: window.innerWidth < 1024,
    };
  },
  mounted() {
    this._onResize = () => { this.isMobile = window.innerWidth < 1024; };
    window.addEventListener('resize', this._onResize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this._onResize);
  },
  computed: {
    // Same rule as the Talent Builder: mobile is always vertical
    effectiveTalentStyle() {
      return this.isMobile ? 'vertical' : this.talentStyle;
    },
    activeHero() {
      return this.slots[this.activeSlot.team][this.activeSlot.index].hero;
    },
    segments() {
      return [1, 2].flatMap(team => [1, 2, 3, 4, 5].map(index => ({ key: team + '-' + index, team, ready: !!(this.slots[team][index].hero && this.slots[team][index].talents) })));
    },
    completedSlotCount() {
      let count = 0;
      for (const team of [1, 2]) {
        for (let index = 1; index <= 5; index++) {
          if (this.slots[team][index].hero && this.slots[team][index].talents) count++;
        }
      }
      return count;
    },
    allSlotsComplete() {
      return this.completedSlotCount === 10;
    },
  },
  methods: {
    slotStatus(team, index) {
      const slot = this.slots[team][index];
      if (slot.talents) return this.$t('Ready');
      const active = this.mode !== null && this.isActiveSlot(team, index);
      if (!slot.hero) return active ? this.$t('Choosing a hero...') : this.$t('Waiting');
      return this.$heroName(slot.hero) + ' · ' + (active ? this.$t('picking talents') : this.$t('talents not locked'));
    },
    playerNumber(team, index) {
      return team === 1 ? index : index + 5;
    },
    isActiveSlot(team, index) {
      return this.activeSlot.team === team && this.activeSlot.index === index;
    },
    selectSlot(team, index) {
      this.activeSlot = { team, index };
      if (this.slots[team][index].hero) {
        this.mode = 'talents';
        this.loadTalents(this.slots[team][index].hero);
      } else {
        this.mode = 'hero';
      }
    },
    clickedHero(hero) {
      const slot = this.slots[this.activeSlot.team][this.activeSlot.index];
      if (!slot.hero || slot.hero.name !== hero.name) {
        slot.talents = null;
      }
      slot.hero = hero;
      this.mode = 'talents';
      this.loadTalents(hero);
    },
    loadTalents(hero) {
      this.talentError = null;
      if (this.talentCache[hero.name]) return;

      this.loadingTalents = true;
      this.$axios.post('/api/v1/tools/auto-battler/talents', { hero: hero.name })
        .then(response => {
          this.talentCache[hero.name] = response.data.talents;
        })
        .catch(() => {
          this.talentError = 'Failed to load talents. Please try again.';
        })
        .finally(() => {
          this.loadingTalents = false;
        });
    },
    lockTalents(talents) {
      this.slots[this.activeSlot.team][this.activeSlot.index].talents = talents;
      this.advanceToNextEmptySlot();
    },
    // Mirrors the drafter: after a pick, move the highlight to the next open slot
    advanceToNextEmptySlot() {
      for (const team of [1, 2]) {
        for (let index = 1; index <= 5; index++) {
          if (!this.slots[team][index].hero) {
            this.selectSlot(team, index);
            return;
          }
        }
      }
      for (const team of [1, 2]) {
        for (let index = 1; index <= 5; index++) {
          if (!this.slots[team][index].talents) {
            this.selectSlot(team, index);
            return;
          }
        }
      }
      this.mode = null;
    },
    buildPlayerXml(playerNumber, hero, talents) {
      let heroName = hero.alt_name ? hero.alt_name : hero.name;
      if (heroName === 'Lúcio') heroName = 'Lucio';

      let xml = '<?xml version="1.0" encoding="us-ascii"?>';
      xml += '<Catalog>';
      xml += '<CButton id="Player' + playerNumber + 'Hero">';
      xml += '<Icon value="' + heroName + '"/>';
      xml += '</CButton>';
      xml += '<CHero id="' + heroName + '">';
      xml += '<TalentAIBuildsArray index="0" ChanceToPick="100">';
      TALENT_LEVELS.forEach((level, i) => {
        xml += '<TalentsArray index="' + i + '" value="' + talents[level].talent_name + '"/>';
      });
      xml += '</TalentAIBuildsArray>';
      for (let i = 1; i <= 5; i++) {
        xml += '<TalentAIBuildsArray index="' + i + '" ChanceToPick="0"/>';
      }
      xml += '</CHero>';
      xml += '</Catalog>';
      return xml;
    },
    downloadFiles() {
      // The button's click still arrives while it's disabled
      if (!this.allSlotsComplete) return;
      for (const team of [1, 2]) {
        for (let index = 1; index <= 5; index++) {
          const slot = this.slots[team][index];
          const playerNumber = this.playerNumber(team, index);
          const xml = this.buildPlayerXml(playerNumber, slot.hero, slot.talents);

          const link = document.createElement('a');
          link.href = window.URL.createObjectURL(new Blob([xml], { type: 'text/plain' }));
          link.download = 'Player' + playerNumber + 'Data.xml';
          link.click();
          setTimeout(() => window.URL.revokeObjectURL(link.href), 1000);
        }
      }
    },
  },
};
</script>
