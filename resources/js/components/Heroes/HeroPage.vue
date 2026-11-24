<template>
  <div>
    <player-page-top>
      <template #heading>
        <page-heading :heading="$heroName(hero)" :portrait-desktop-only="true">
          <hero-image-wrapper :hero="hero" size="heading" :excludehover="true"></hero-image-wrapper>
          <!-- The hero name is the hero picker, as on the Talents page -->
          <template #headingText>
            <hero-heading-select :heroes="allHeroes" :value="hero.id" textclass="" @hero-changed="goToHero"></hero-heading-select>
          </template>
          <template #aboveHeading>
            <a href="/Heroes" class="inline-flex max-md:flex max-md:w-fit items-center gap-1.5 text-sm font-normal text-lteal hover:underline mb-1"><basil-icon name="arrow-small" class="rotate-180 text-xs text-white"></basil-icon>{{ $t('All heroes') }}</a>
          </template>
          <template v-if="metadata" #subheading>
            <span class="inline-flex items-center gap-2">
              <round-image v-if="universeIcon" :image="universeIcon" size="small" :title="metadata.franchise">
                <image-hover-box :title="metadata.franchise"></image-hover-box>
              </round-image>
              {{ [$t(hero.new_role), metadata.difficulty ? $t(metadata.difficulty) : null].filter(v => v).join(' · ') }}
            </span>
          </template>
        </page-heading>
      </template>
      <!-- The hero's stat pages -->
      <div class="flex md:justify-end px-4 md:px-8">
        <div class="rounded bg-surface p-4 flex flex-wrap gap-2 max-md:justify-center">
          <custom-button v-for="link in statLinks" :key="link.href" :href="link.href" :text="link.text" :alt="link.text" color="blue" :targetblank="true"></custom-button>
        </div>
      </div>
    </player-page-top>
    <dynamic-banner-ad :patreon-user="patreonUser" :index="3" :mobile-override="false"></dynamic-banner-ad>


    <div :class="['max-w-[120rem] mx-auto px-4 md:px-8 mt-6 grid xl:grid-cols-2 gap-6', activeTab === 'about' ? 'items-stretch' : 'items-start']">
      <div class="flex flex-col gap-6 min-w-0">
        <!-- The page's sections, like the esports pages' tabs; the art beside starts level with them -->
        <tab-button v-if="metadata" :tabs="tabs" :stretch="true" :ignoreclick="true" :overridedefaultside="activeTab" @tab-click="activeTab = $event"></tab-button>
        <panel-box v-if="!metadata">
          <p class="text-center">{{ $t("Game data for :hero hasn't been loaded yet.", { hero: $heroName(hero) }) }}</p>
        </panel-box>

        <!-- About -->
        <template v-else-if="activeTab === 'about'">
          <panel-box :title="metadata.title || 'About'" :subtitle="metadata.description" :divider="false">
            <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] gap-x-6 gap-y-2 items-center">
              <template v-for="rating in ratings" :key="rating.label">
                <span class="text-sm font-semibold">{{ $t(rating.label) }}</span>
                <stat-bar :value="rating.value * 10" :hide-value="true" color="teal"></stat-bar>
                <span class="text-sm font-semibold tabular-nums">{{ rating.value }} / 10</span>
              </template>
            </div>
          </panel-box>

          <!-- The hero first (with the level slider), then each summoned unit -->
          <panel-box v-for="(unit, index) in units" :key="unit.unit_id" :title="$heroName(unit.name)"
            :subtitle="index === 0 ? $t('Base stats at level :level · drag the slider to change the level', { level }) : $t('Summoned unit stats at level :level', { level })" :divider="false">
            <template v-if="index === 0" #right>
              <level-slider class="w-full md:w-72" v-model="level"></level-slider>
            </template>
            <table class="w-full rounded-table">
              <thead>
                <tr>
                  <!-- Half each, so every unit's table lines up -->
                  <th class="w-1/2 py-2 px-6 text-left font-medium bg-teal text-white">{{ $t('Stat') }}</th>
                  <th class="w-1/2 py-2 px-6 text-center font-medium bg-teal text-white">{{ $t('Value') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in unitStats(unit)" :key="row.label">
                  <td class="py-4 px-6 text-left text-lg font-bold">{{ $t(row.label) }}</td>
                  <td class="py-4 px-6 text-center">{{ row.value }}<span v-if="row.rate" class="text-subtext text-sm"> ({{ $t('+:rate% per level', { rate: row.rate }) }})</span></td>
                </tr>
              </tbody>
            </table>
          </panel-box>
        </template>

        <!-- Abilities -->
        <template v-else-if="activeTab === 'abilities'">
          <panel-box v-for="(group, index) in abilityGroups" :key="group.key" :title="group.label || 'Abilities'" :divider="false">
            <template v-if="index === 0" #right>
              <level-slider class="w-full md:w-72" v-model="level"></level-slider>
            </template>
            <div v-for="ability in group.abilities" :key="ability.link_id" class="flex items-center">
              <talent-image-wrapper class="shrink-0" :talent="ability" :level="level">
                <p v-if="abilityCosts(ability).length" class="mt-2.5 text-gray-medium">{{ abilityCosts(ability).join(' · ') }}</p>
                <div v-for="sub in childAbilities(ability.link_id)" :key="sub.link_id" class="mt-2.5">
                  <h2 class="text-base font-bold text-white">{{ $talentName(sub) }}</h2>
                  <game-tooltip class="mt-2.5" :text="$gameText(sub, 'raw_description')" :level="level"></game-tooltip>
                </div>
              </talent-image-wrapper>
              <div class="min-w-0 px-3">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="font-bold">{{ $talentName(ability) }}</span>
                  <span v-if="hotkeyLabel(ability)" :class="['px-1 text-sm border rounded', isHeroicOrTrait(ability) ? 'text-yellow border-yellow' : 'border-gray-medium']">{{ hotkeyLabel(ability) }}</span>
                </div>
                <p v-if="ability.short_description" class="text-sm text-gray-medium">{{ $gameText(ability, 'short_description') }}</p>
              </div>
            </div>
          </panel-box>
        </template>

        <!-- Talents -->
        <panel-box v-else-if="activeTab === 'talents'" :title="$t('Talents')" :divider="false">
          <template #right>
            <level-slider class="w-full md:w-72" v-model="level"></level-slider>
          </template>
          <div class="flex gap-8 max-md:flex-col">
          <multi-tab-button class="md:w-20 text-xl" :vertical="true" :options="tierOptions" v-model="selectedTier"></multi-tab-button>
          <div class="flex-1 min-w-0">
          <div v-for="tier in talentTiers" v-show="tier.level === selectedTier" :key="tier.level" class="space-y-3">
            <div v-for="talent in tier.talents" :key="talent.talent_id" class="flex items-center">
              <talent-image-wrapper class="shrink-0" :talent="talent" :level="level">
                <p v-if="talent.cooldown && talent.cooldown !== '0'" class="mt-2.5 text-gray-medium">{{ $t('Cooldown: :cooldown', { cooldown: talent.cooldown }) }}</p>
                <div v-for="sub in childAbilities(talent.link_id)" :key="sub.link_id" class="mt-2.5">
                  <h2 class="text-base font-bold text-white">{{ $talentName(sub) }}</h2>
                  <game-tooltip class="mt-2.5" :text="$gameText(sub, 'raw_description')" :level="level"></game-tooltip>
                </div>
              </talent-image-wrapper>
              <div class="flex-1 min-w-0 px-3">
                <span class="font-bold">{{ $talentName(talent) }}</span>
                <game-tooltip v-if="talent.short_description" class="text-sm text-gray-medium" :text="$gameText(talent, 'short_description')" :level="level"></game-tooltip>
              </div>
              <span v-if="talentBadge(talent)" :class="['flex items-center gap-1 px-1 text-sm border rounded self-start whitespace-nowrap', talentBadge(talent).class]">
                <img v-if="talent.is_quest" class="w-4 h-4" src="/images/miscellaneous/quest-icon.png" alt="Quest">
                {{ talentBadge(talent).label }}
              </span>
            </div>
          </div>
          </div>
          </div>
        </panel-box>
      </div>

      <!-- The hero's art, the same size on every tab, then the lore. On About the lore card takes up what's
           left of the left column's height, so both columns end level -->
      <div class="flex flex-col gap-6 min-w-0">
        <div class="relative rounded overflow-hidden bg-surface">
          <img :src="`/images/heroes_rectangle_large/${hero.short_name}.jpg`" :alt="hero.name" class="w-full aspect-[800/778] object-cover object-top">
        </div>
        <panel-box v-if="metadata && metadata.lore && activeTab === 'about'" class="xl:flex-1" :title="$t('Lore')" :divider="false">
          <p class="text-sm text-subtext whitespace-pre-line">{{ metadata.lore }}</p>
        </panel-box>
      </div>
    </div>

    <dynamic-banner-ad class="mt-6" :patreon-user="patreonUser" :index="4" :mobile-override="false"></dynamic-banner-ad>
  </div>
</template>

<script>
import { scaleValue } from '../../utils/gameTooltip';

// Same order as the in-game collection: basics, actives, trait, heroics
const ABILITY_ORDER = ['Q', 'W', 'E', 'Active', 'Trait', 'Heroic', 'Z'];

const UNIVERSE_ICONS = {
  Warcraft: 'wow',
  Starcraft: 'sc2',
  Diablo: 'd3',
  Overwatch: 'ow',
  Classic: 'nexus',
};

export default {
  name: 'HeroPage',
  props: {
    herodata: {
      type: Object,
      required: true,
    },
    urlparameters: Object,
    patreonUser: Boolean,
  },
  data() {
    return {
      activeTab: 'about',
      level: 1,
      selectedTier: null,
      tabs: [
        { key: 'about', text: 'About' },
        { key: 'abilities', text: 'Abilities' },
        { key: 'talents', text: 'Talents' },
      ],
    };
  },
  created() {
    const tab = this.urlparameters && this.urlparameters.tab;
    if (this.tabs.some(option => option.key === tab)) {
      this.activeTab = tab;
    }
    this.selectedTier = this.talentTiers.length ? this.talentTiers[0].level : null;
  },
  watch: {
    activeTab(tab) {
      const url = new URL(window.location.href);
      if (tab === 'about') {
        url.searchParams.delete('tab');
      } else {
        url.searchParams.set('tab', tab);
      }
      history.replaceState(null, '', url);
    },
  },
  computed: {
    // Every hero, from the list the layout puts on each page
    allHeroes() {
      return (window.navLists && window.navLists.heroes) || [];
    },
    hero() {
      return this.herodata.hero;
    },
    metadata() {
      const metadata = this.herodata.metadata;
      const localized = this.herodata.localized || {};
      return metadata && {
        ...metadata,
        title: localized.title || metadata.title,
        description: localized.description || metadata.description,
        lore: localized.lore || metadata.lore,
      };
    },
    units() {
      return this.herodata.units || [];
    },
    ratings() {
      return [
        { label: 'Damage', value: this.metadata.rating_damage },
        { label: 'Utility', value: this.metadata.rating_utility },
        { label: 'Survivability', value: this.metadata.rating_survivability },
        { label: 'Complexity', value: this.metadata.rating_complexity },
      ].filter(rating => rating.value !== null);
    },
    enabledWeapons() {
      return (this.herodata.weapons || []).filter(weapon => !weapon.is_disabled);
    },
    abilityGroups() {
      const abilities = this.herodata.abilities || [];
      const rank = a => ABILITY_ORDER.indexOf(a.ability_type);
      // Main unit first, then each extra unit under its own name
      return this.units.map(unit => ({
        key: unit.unit_id,
        label: unit.is_main ? null : unit.name,
        abilities: abilities
          .filter(a => !a.parent_link_id && (unit.is_main ? !a.unit_id : a.unit_id === unit.unit_id))
          .filter(a => rank(a) !== -1 && !this.isGenericMount(a))
          .sort((a, b) => rank(a) - rank(b) || a.sort - b.sort),
      })).filter(group => group.abilities.length);
    },
    universeIcon() {
      // The game gives The Lost Vikings the retro emblem; the other Classic heroes use the Nexus one
      const icon = this.hero.short_name === 'thelostvikings' ? 'retro' : UNIVERSE_ICONS[this.metadata.franchise];
      return icon ? `/images/universes/${icon}.png` : null;
    },
    talentTiers() {
      const tiers = {};
      for (const talent of this.herodata.talents || []) {
        (tiers[talent.level] = tiers[talent.level] || []).push(talent);
      }
      return Object.keys(tiers).map(Number).sort((a, b) => a - b).map(level => ({ level, talents: tiers[level] }));
    },
    tierOptions() {
      return this.talentTiers.map(tier => ({ key: tier.level, label: tier.level }));
    },
    statLinks() {
      const name = encodeURIComponent(this.hero.name);
      // Same order and labels as the nav's Global Hero Stats menu, then Tools
      return [
        { href: `/Global/Hero?hero=${name}`, text: 'Hero Stats' },
        { href: `/Global/Talents/${name}`, text: 'Talent Stats' },
        { href: `/Global/Hero/Maps/${name}`, text: 'Map Stats' },
        { href: `/Global/Matchups/${name}`, text: 'Matchup Stats' },
        { href: `/Global/Draft/${name}`, text: 'Draft Stats' },
        { href: `/Global/Talents/Builder/${name}`, text: 'Talent Builder' },
      ];
    },
  },
  methods: {
    // Another hero's page, on the same tab
    goToHero(id) {
      const hero = this.allHeroes.find(item => item.id == id);
      if (hero) {
        window.location.href = `/Heroes/${encodeURIComponent(hero.name)}${window.location.search}`;
      }
    },
    num(value) {
      return value === null || value === undefined ? null : parseFloat(value);
    },
    scaled(base, rate, decimals) {
      const value = rate ? scaleValue(base, rate, this.level) : base;
      return decimals ? value.toFixed(decimals) : Math.floor(value + 0.5).toString();
    },
    ratePercent(rate) {
      return rate ? parseFloat((rate * 100).toFixed(2)) : null;
    },
    unitStats(unit) {
      const rows = [];
      const life = this.num(unit.life);
      if (life) {
        rows.push({ label: 'Health', value: this.scaled(life, this.num(unit.life_scale)), rate: this.ratePercent(this.num(unit.life_scale)) });
      }
      const regen = this.num(unit.life_regen);
      if (regen) {
        rows.push({ label: 'Health Regen', value: this.$t(':value / sec', { value: this.scaled(regen, this.num(unit.life_regen_scale), 2) }), rate: this.ratePercent(this.num(unit.life_regen_scale)) });
      }
      const shield = this.num(unit.shield);
      if (shield) {
        rows.push({ label: 'Shields', value: this.scaled(shield, this.num(unit.shield_scale)), rate: this.ratePercent(this.num(unit.shield_scale)) });
      }
      const energy = this.num(unit.energy);
      if (energy) {
        const label = unit.energy_type || 'Mana';
        rows.push({ label, value: this.scaled(energy) });
        if (this.num(unit.energy_regen)) {
          rows.push({ label: this.$t(':name Regen', { name: this.$t(label) }), value: this.$t(':value / sec', { value: this.num(unit.energy_regen).toFixed(2) }) });
        }
      }
      const weapons = this.enabledWeapons.filter(weapon => weapon.unit_id === unit.unit_id);
      for (const weapon of weapons) {
        const label = this.weaponLabel(weapon, unit, weapons.length);
        const damage = this.num(weapon.damage);
        const period = this.num(weapon.period);
        rows.push({ label: this.$t(':name Damage', { name: label }), value: this.scaled(damage, this.num(weapon.damage_scale)), rate: this.ratePercent(this.num(weapon.damage_scale)) });
        if (period) {
          rows.push({ label: this.$t(':name Speed', { name: label }), value: this.$t(':value / sec', { value: (1 / period).toFixed(2) }) });
        }
        if (this.num(weapon.range)) {
          rows.push({ label: this.$t(':name Range', { name: label }), value: parseFloat(this.num(weapon.range).toFixed(2)).toString() });
        }
      }
      if (this.num(unit.move_speed)) {
        rows.push({ label: 'Movement Speed', value: parseFloat(this.num(unit.move_speed).toFixed(4)).toString() });
      }
      return rows;
    },
    weaponLabel(weapon, unit, count) {
      if (count === 1) {
        return this.$t('Basic Attack');
      }
      const heroToken = (unit.unit_id || '').replace(/^Hero/, '');
      const tag = weapon.weapon_id
        .replace(heroToken, '')
        .replace(/Hero|Weapon|Attack/g, '')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .trim();
      return tag ? this.$t('Basic Attack (:tag)', { tag }) : this.$t('Basic Attack');
    },
    abilityCosts(ability) {
      const costs = [];
      if (ability.mana_cost && ability.mana_cost !== '0') {
        costs.push(`${(this.units.find(u => u.is_main) || {}).energy_type || 'Mana'}: ${ability.mana_cost}`);
      }
      if (ability.cooldown && ability.cooldown !== '0') {
        costs.push(`Cooldown: ${ability.cooldown}`);
      }
      if (ability.charges && ability.charges > 1 && ability.charges < 65535) {
        costs.push(`Charges: ${ability.charges}`);
      }
      return costs;
    },
    childAbilities(linkId) {
      if (!linkId) {
        return [];
      }
      return (this.herodata.abilities || []).filter(a => a.parent_link_id === linkId);
    },
    hotkeyLabel(ability) {
      const labels = { Trait: 'Trait (D)', Heroic: 'Heroic (R)', Z: 'Mount (Z)' };
      return labels[ability.ability_type] || ability.hotkey;
    },
    talentBadge(talent) {
      const badges = {
        Trait: { label: 'Trait (D)', class: 'text-yellow border-yellow' },
        Heroic: { label: 'Heroic (R)', class: 'text-yellow border-yellow' },
        Active: { label: 'Active', class: 'text-lred border-lred' },
        Passive: { label: 'Passive', class: 'border-gray-medium' },
      };
      if (badges[talent.hotkey]) {
        return badges[talent.hotkey];
      }
      return talent.hotkey ? { label: talent.hotkey, class: 'border-gray-medium' } : null;
    },
    isHeroicOrTrait(ability) {
      return ability.ability_type === 'Trait' || ability.ability_type === 'Heroic';
    },
    isGenericMount(ability) {
      return (ability.link_id || '').startsWith('Mount|SummonMount');
    },
  },
}
</script>
