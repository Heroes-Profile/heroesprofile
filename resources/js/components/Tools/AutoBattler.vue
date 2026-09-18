<template>
  <div>
    <page-heading :infoText1="infoText" :heading="'Auto Battler'"></page-heading>

    <div class="max-w-[1500px] mx-auto px-4 py-6">
      <auto-battler-setup-guide></auto-battler-setup-guide>

      <div class="flex justify-center items-center gap-4 mt-6 min-h-[3rem]">
        <span class="text-gray-medium">{{ completedSlotCount }} / 10 players ready</span>
        <button
          v-if="allSlotsComplete"
          @click="downloadFiles"
          class="bg-teal hover:bg-lteal text-white font-bold px-6 py-2 rounded"
        >
          <i class="fa-solid fa-download mr-2"></i>Download Files
        </button>
      </div>

      <div class="flex gap-4 mt-4 max-md:flex-col">
        <div v-for="team in [1, 2]" :key="team" :class="['flex md:flex-col items-center max-md:justify-center gap-1 shrink-0', team === 1 ? 'order-1' : 'order-3']">
          <span class="text-sm uppercase max-md:hidden">Team {{ team }}</span>
          <div
            v-for="index in 5"
            :key="index"
            @click="selectSlot(team, index)"
            :title="slots[team][index].hero ? slots[team][index].hero.name : 'Player ' + playerNumber(team, index)"
            :class="[
              'relative group w-20 h-20 max-md:w-14 max-md:h-14 m-2 max-md:m-1 rounded-2xl border bg-cover cursor-pointer',
              team === 1 ? 'bg-[rgba(33,61,122,0.2)]' : 'bg-[rgba(179,54,22,0.2)]',
              isActiveSlot(team, index) ? 'shadow-[0_0_0.5em_#ffd051] border-[#ffd051]' : 'border-[gray]',
              !slots[team][index].hero && !isActiveSlot(team, index) ? 'opacity-40 hover:opacity-100' : '',
            ]"
            :style="slots[team][index].hero ? { backgroundImage: 'url(' + heroImage(slots[team][index].hero) + ')' } : {}"
          >
            <div
              v-if="slots[team][index].hero"
              @click.stop="clearSlot(team, index)"
              class="absolute top-0 right-0 hidden group-hover:flex items-center justify-center w-6 h-6 rounded-tr-2xl bg-darken hover:bg-red text-white text-sm"
              title="Clear"
            >
              <i class="fa-solid fa-xmark"></i>
            </div>
            <div
              v-if="slots[team][index].talents"
              class="absolute bottom-0 right-0 flex items-center justify-center w-6 h-6 rounded-br-2xl bg-teal text-white text-xs"
              title="Talents locked in"
            >
              <i class="fa-solid fa-check"></i>
            </div>
          </div>
        </div>

        <div class="flex-1 min-w-0 order-2">
          <div v-if="mode === 'hero'">
            <div class="flex flex-col items-center gap-2 mb-4">
              <input
                type="text"
                v-model="searchQuery"
                class="w-60 border rounded p-2 text-black"
                placeholder="Search Heroes"
              />
              <div class="flex justify-center">
                <img
                  v-for="role in roles"
                  :key="role"
                  :src="'/images/roles/' + role.toLowerCase() + (selectedRole === role ? '-highlighted' : '') + '.PNG'"
                  :title="role"
                  :alt="role"
                  @click="selectedRole = selectedRole === role ? null : role"
                  class="w-12 cursor-pointer"
                />
              </div>
            </div>

            <div class="flex flex-wrap justify-center">
              <img
                v-for="hero in filteredHeroes"
                :key="hero.id"
                :src="heroImage(hero)"
                :title="hero.name"
                :alt="hero.name"
                @click="clickedHero(hero)"
                class="w-16 h-16 m-2 rounded-full cursor-pointer hover:shadow-[0.2em_0.1em_0.5em_black] hover:brightness-125"
              />
            </div>
          </div>

          <div v-else-if="mode === 'talents'">
            <div v-if="loadingTalents">
              <loading-component></loading-component>
            </div>
            <div v-else-if="talentError" class="text-center text-red py-10">{{ talentError }}</div>
            <auto-battler-talent-selection
              v-else
              :key="activeSlot.team + '-' + activeSlot.index + '-' + activeHero.name"
              :hero="activeHero"
              :talents="talentCache[activeHero.name]"
              :initial="slots[activeSlot.team][activeSlot.index].talents"
              :layout="effectiveTalentStyle"
              :isMobile="isMobile"
              @layout-change="talentStyle = $event"
              @locked="lockTalents"
              @change-hero="mode = 'hero'"
            ></auto-battler-talent-selection>
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
      infoText: 'Build two AI teams of five heroes with full talent builds, then download the AI build files and watch them battle it out in the Veteran Introduction.',
      roles: ['Tank', 'Bruiser', 'Healer', 'Support', 'Melee Assassin', 'Ranged Assassin'],
      slots,
      activeSlot: { team: 1, index: 1 },
      mode: 'hero',
      searchQuery: '',
      selectedRole: null,
      talentCache: {},
      loadingTalents: false,
      talentError: null,
      talentStyle: this.talentbuilderstyle || 'vertical',
      isMobile: window.innerWidth < 768,
    };
  },
  mounted() {
    this._onResize = () => { this.isMobile = window.innerWidth < 768; };
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
    filteredHeroes() {
      const query = this.normalizeString(this.searchQuery);
      return this.heroes.filter(hero => {
        if (this.selectedRole && hero.new_role !== this.selectedRole) return false;
        return !query || this.normalizeString(hero.name).includes(query);
      });
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
    normalizeString(input) {
      return (input || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },
    heroImage(hero) {
      return '/images/heroes/' + hero.short_name + '.png';
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
    clearSlot(team, index) {
      this.slots[team][index] = { hero: null, talents: null };
      this.selectSlot(team, index);
    },
    clickedHero(hero) {
      const slot = this.slots[this.activeSlot.team][this.activeSlot.index];
      if (!slot.hero || slot.hero.name !== hero.name) {
        slot.talents = null;
      }
      slot.hero = hero;
      this.searchQuery = '';
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
