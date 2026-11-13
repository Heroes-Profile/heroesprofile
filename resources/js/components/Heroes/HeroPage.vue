<template>
  <div>
    <page-heading :heading="$heroName(hero)">
      <hero-image-wrapper :hero="hero" size="big" :excludehover="true"></hero-image-wrapper>
      <template v-if="metadata" #aboveHeading>
        <div class="flex items-center gap-2 text-base text-gray-medium max-md:justify-center">
          <round-image v-if="universeIcon" :image="universeIcon" size="small" :title="metadata.franchise">
            <image-hover-box :title="metadata.franchise"></image-hover-box>
          </round-image>
          {{ [$t(hero.new_role), metadata.difficulty ? $t('Difficulty: :difficulty', { difficulty: $t(metadata.difficulty) }) : null].filter(v => v).join(' | ') }}
        </div>
      </template>
    </page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <div class="flex flex-wrap items-center gap-x-8 gap-y-4 max-md:justify-center">
        <multi-tab-button :options="tabs" v-model="activeTab"></multi-tab-button>
        <level-slider v-if="metadata" class="w-full md:max-w-md" v-model="level"></level-slider>
      </div>
      <div class="flex flex-wrap items-center gap-2 max-md:justify-center">
        <custom-button v-for="link in statLinks" :key="link.href" :href="link.href" :text="link.text" :alt="link.text" size="small" :targetblank="true"></custom-button>
      </div>

      <panel-box v-if="!metadata">
        <p class="text-center">{{ $t("Game data for :hero hasn't been loaded yet.", { hero: $heroName(hero) }) }}</p>
      </panel-box>

      <div v-else class="flex gap-6">
      <div class="flex-1 min-w-0 flex flex-col gap-6">

      <!-- About -->
      <template v-if="activeTab === 'about'">
        <panel-box :title="metadata.title || 'About'">
          <p v-if="metadata.description">{{ metadata.description }}</p>
          <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 items-center max-w-lg">
            <template v-for="rating in ratings" :key="rating.label">
              <span>{{ $t(rating.label) }}</span>
              <stat-bar :value="rating.value * 10" :display-text="rating.value" color="teal"></stat-bar>
            </template>
          </div>
        </panel-box>

        <panel-box v-for="unit in units" :key="unit.unit_id" :title="units.length > 1 ? $heroName(unit.name) : $t('Base Stats')">
          <template #title><span class="text-sm font-normal text-gray-medium">{{ $t('at level :level', { level }) }}</span></template>
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th>{{ $t('Stat') }}</th>
                <th>{{ $t('Value') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in unitStats(unit)" :key="row.label">
                <td>{{ $t(row.label) }}</td>
                <td>{{ row.value }}<span v-if="row.rate" class="text-gray-medium"> ({{ $t('+:rate% per level', { rate: row.rate }) }})</span></td>
              </tr>
            </tbody>
          </table>
        </panel-box>

        <panel-box v-if="metadata.lore" :title="$t('Lore')">
          <p class="whitespace-pre-line">{{ metadata.lore }}</p>
        </panel-box>
      </template>

      <!-- Abilities -->
      <template v-if="activeTab === 'abilities'">
        <panel-box v-for="group in abilityGroups" :key="group.key" :title="group.label || 'Abilities'">
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
      <panel-box v-if="activeTab === 'talents'" :title="$t('Talents')">
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
      <img :src="`/images/heroes_rectangle_large/${hero.short_name}.jpg`" :alt="hero.name" class="max-md:hidden md:w-[40%] aspect-[800/778] object-cover object-top rounded self-start">
      </div>
    </div>
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
  },
  data() {
    return {
      activeTab: 'about',
      level: 1,
      selectedTier: null,
      tabs: [
        { key: 'about', label: 'About' },
        { key: 'abilities', label: 'Abilities' },
        { key: 'talents', label: 'Talents' },
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
