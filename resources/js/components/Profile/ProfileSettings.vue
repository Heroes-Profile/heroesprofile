<template>
  <div>
  <page-heading :heading="'Settings'"></page-heading>
  <!-- While a dropdown is open, a click anywhere just closes it (which saves) instead of opening another setting -->
  <div v-if="dropdownOpen" class="fixed inset-0 z-40"></div>
  <div class="max-w-[120rem] w-full mx-auto px-4 md:px-8 flex flex-col gap-6">
    <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
      <span class="uppercase text-gray-medium">{{ $t('Profile preferences') }}</span>
      <span class="flex items-center gap-2 text-sm text-subtext"><basil-icon name="check"></basil-icon>{{ $t('Changes are saved automatically') }}</span>
    </div>

    <div class="grid md:grid-cols-2 gap-6 [&>*]:min-w-0">
        <panel-box title="General" subtitle="Appearance, privacy and language." :divider="false" @click.capture="saveCard = 'general'">
          <template #right>
            <span v-if="saveState && saveCard === 'general'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              <basil-icon v-else name="check" class="text-teal"></basil-icon>
              {{ saveState === 'saving' ? $t('Saving...') : $t('Saved') }}
            </span>
          </template>
          <div class="flex flex-col mt-2 divide-y divide-field [&>*]:py-4 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Table Style') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Color theme of statistics tables.') }}</p>
              </div>
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Light" tab2text="Dark" :small="true" :ignoreclick="true" @tab-click="darkmodesetting" :overridedefaultside="darkmode"></tab-button>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Account Visibility') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Control whether your profile is visible to other users.') }}</p>
              </div>
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Public" tab2text="Private" :small="true" :ignoreclick="true" @tab-click="accountvisibilitysetting" :overridedefaultside="accountVisibility === 'true' ? 'right' : 'left'"></tab-button>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Language') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Interface language of the site.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <single-select-filter :compact="true" :hidelabel="true"
                  :values="localeOptions"
                  :text="'Language'"
                  @dropdown-closed="dropdownOpen = false; saveLocale()"
                  @input-changed="handleInputChange"
                  :defaultValue="userLocale"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></single-select-filter>
              </div>
            </div>
          </div>
        </panel-box>

        <panel-box title="Global Pages" subtitle="Defaults for hero, talent and global statistics pages." :divider="false" @click.capture="saveCard = 'global'">
          <template #right>
            <span v-if="saveState && saveCard === 'global'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              <basil-icon v-else name="check" class="text-teal"></basil-icon>
              {{ saveState === 'saving' ? $t('Saving...') : $t('Saved') }}
            </span>
          </template>
          <div class="flex flex-col mt-2 divide-y divide-field [&>*]:py-4 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Multi-Select Game Type') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Used on pages where several game types can be combined.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <multi-select-filter :compact="true" :hidelabel="true"
                  :values="filters.game_types_full"
                  :text="'Game Type'"
                  @dropdown-closed="dropdownOpen = false; saveSettings()"
                  @input-changed="handleInputChange"
                  :defaultValue="usermultigametype"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></multi-select-filter>
              </div>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Single Game Type') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Used on pages that show one game type at a time.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <single-select-filter :compact="true" :hidelabel="true"
                  :values="filters.game_types_full"
                  :text="'Game Type'"
                  @dropdown-closed="dropdownOpen = false; saveSettings()"
                  @input-changed="handleInputChange"
                  :defaultValue="usergametype"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></single-select-filter>
              </div>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Build Type') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Which talent builds are shown first.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <single-select-filter :compact="true" :hidelabel="true"
                  :values="filters.talent_build_types"
                  :text="'Talent Build Type'"
                  @dropdown-closed="dropdownOpen = false; saveSettings()"
                  @input-changed="handleInputChange"
                  :defaultValue="talentBuildType"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></single-select-filter>
              </div>
            </div>
            <label class="flex items-center justify-between gap-x-6 cursor-pointer">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Advanced Filtering') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Show advanced filter options by default.') }}</p>
              </div>
              <input type="checkbox" class="form-checkbox" :checked="advancedfiltering === 'true'" @change="advancedfiltering = $event.target.checked ? 'true' : 'false'; saveSettings()">
            </label>
          </div>
        </panel-box>

        <panel-box title="Player Pages" subtitle="Defaults for player profile and match history pages." :divider="false" @click.capture="saveCard = 'player'">
          <template #right>
            <span v-if="saveState && saveCard === 'player'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              <basil-icon v-else name="check" class="text-teal"></basil-icon>
              {{ saveState === 'saving' ? $t('Saving...') : $t('Saved') }}
            </span>
          </template>
          <div class="flex flex-col mt-2 divide-y divide-field [&>*]:py-4 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Match History Style') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Compact table or expanded game rows.') }}</p>
              </div>
              <!-- Labelled as on the Match History page -->
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Compact" tab2text="Expanded" :small="true" :ignoreclick="true" @tab-click="playermatchhistorystylesetting" :overridedefaultside="playerhistorytable"></tab-button>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Default Game Type') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Nothing selected shows every game type. HP MMR uses the first one selected, or Storm League.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <multi-select-filter :compact="true" :hidelabel="true" placeholder="All game types"
                  :values="filters.game_types_full"
                  :text="'Player Game Type'"
                  @dropdown-closed="dropdownOpen = false; saveSettings()"
                  @input-changed="handleInputChange"
                  :defaultValue="playermultigametype"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></multi-select-filter>
              </div>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Initial Load Style') }}</h4>
                <p class="text-sm text-subtext">{{ $t('How player data is loaded when a profile opens.') }}</p>
              </div>
              <div class="w-full sm:w-2/5 sm:max-w-[20rem] shrink-0">
                <single-select-filter :compact="true" :hidelabel="true"
                  :values="playerdataloadstyles"
                  :text="'Player Load'"
                  @dropdown-closed="dropdownOpen = false; saveSettings()"
                  @input-changed="handleInputChange"
                  :defaultValue="playerload"
                  :trackclosure="true"
                  @dropdown-opened="dropdownOpen = true"
                ></single-select-filter>
              </div>
            </div>
            <label class="flex items-center justify-between gap-x-6 cursor-pointer">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Show Custom Games') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Applies to Match History only.') }}</p>
              </div>
              <input type="checkbox" class="form-checkbox" :checked="customgames" @change="customgames = $event.target.checked; saveSettings()">
            </label>
          </div>
        </panel-box>

        <panel-box title="Talents" subtitle="Talent builder and talent statistics on global pages." :divider="false" @click.capture="saveCard = 'talents'">
          <template #right>
            <span v-if="saveState && saveCard === 'talents'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              <basil-icon v-else name="check" class="text-teal"></basil-icon>
              {{ saveState === 'saving' ? $t('Saving...') : $t('Saved') }}
            </span>
          </template>
          <div class="flex flex-col mt-2 divide-y divide-field [&>*]:py-4 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <h4 class="font-medium">{{ $t('Talent Builder Layout') }}</h4>
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Vertical" tab2text="Horizontal" :small="true" :ignoreclick="true" @tab-click="talentbuilderstylesetting" :overridedefaultside="talentbuilderstyle"></tab-button>
            </div>
            <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0">
                <h4 class="font-medium">{{ $t('Talent Builder Load') }}</h4>
                <p class="text-sm text-subtext">{{ $t('Load builds right away or only after applying filters.') }}</p>
              </div>
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Auto Load" tab2text="On Filter" :small="true" :ignoreclick="true" @tab-click="talentbuilderloadsetting" :overridedefaultside="talentbuilderload"></tab-button>
            </div>
          </div>
        </panel-box>

        <panel-box v-if="flairOptions.length || availableBorders.length" title="Flair" subtitle="Choose which flair shows next to your BattleTag. Hidden flair is hidden for everyone." :divider="false" @click.capture="saveCard = 'flair'">
          <template #right>
            <span v-if="saveState && saveCard === 'flair'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              <basil-icon v-else name="check" class="text-teal"></basil-icon>
              {{ saveState === 'saving' ? $t('Saving...') : $t('Saved') }}
            </span>
          </template>
          <div class="flex flex-col mt-2 divide-y divide-field [&>*]:py-4 [&>*:first-child]:pt-0 [&>*:last-child]:pb-0">
            <div v-if="availableBorders.length" class="flex flex-col gap-3">
              <div>
                <h4 class="font-medium">{{ $t('Avatar border') }}</h4>
                <p class="text-sm text-subtext">{{ $t('The border around your picture in the top bar.') }}</p>
              </div>
              <!-- Each choice drawn on the top bar's picture; click one to use it. Up to four, so pictures rather than the tab button. -->
              <div class="flex flex-wrap gap-4">
                <button v-for="option in borderOptions" :key="option.key" type="button" :aria-pressed="option.key === avatarBorder" @click="avatarbordersetting(option.key)"
                  :class="['flex flex-col items-center gap-3 w-[8.5rem] px-3 pt-4 pb-3 rounded-control border-2 transition-colors', option.key === avatarBorder ? 'bg-surface-1 border-teal text-white' : 'bg-surface-1 border-transparent text-subtext hover:bg-surface-2']">
                  <span class="relative block">
                    <span :class="['relative block w-14 h-14 rounded overflow-hidden', borderPreviewClass[option.key], option.key === avatarBorder ? '' : 'opacity-60']">
                      <img class="w-full h-full object-cover" src="/images/heroes/auto_select.jpg" alt="">
                    </span>
                    <owner-sparks v-if="option.key === 'owner'"></owner-sparks>
                  </span>
                  <span class="text-sm">{{ $t(option.label) }}</span>
                </button>
              </div>
            </div>
            <div v-for="flair in flairOptions" :key="flair.key" class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2">
              <div class="min-w-0 flex items-center gap-4">
                <i v-if="flair.icon" :class="['shrink-0', flair.icon, flair.iconClass]"></i>
                <div class="min-w-0">
                  <h4 class="font-medium">{{ flair.label }}</h4>
                  <p class="text-sm text-subtext">{{ flair.description }}</p>
                </div>
              </div>
              <tab-button class="shrink-0 self-start sm:self-auto" tab1text="Show" tab2text="Hide" :small="true" :ignoreclick="true" @tab-click="(side) => flairsetting(flair.key, side)" :overridedefaultside="flairSides[flair.key]"></tab-button>
            </div>
          </div>
        </panel-box>
  
        <panel-box title="Connections" subtitle="Linked accounts." :divider="false">
          <template #right>
            <span v-if="saveState && saveCard === 'connections'" class="ml-auto self-start flex items-center gap-2 text-sm text-subtext" aria-live="polite">
              <img v-if="saveState === 'saving'" class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="" />
              {{ $t('Removing Patreon...') }}
            </span>
          </template>
          <div class="flex max-sm:flex-col sm:items-center justify-between gap-x-6 gap-y-2 mt-2">
            <div class="min-w-0">
              <h4 class="font-medium">Patreon</h4>
              <p class="text-sm text-subtext">{{ $t('Link your Patreon account to unlock supporter features.') }}</p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
              <span v-if="user.patreon_account" class="bg-teal px-3 py-1 rounded text-sm">{{ $t('Connected') }}</span>
              <custom-button
                v-if="!user.patreon_account"
                :href="'/authenticate/patreon'"
                text="Login with Patreon"
                alt="Login with Patreon"
                size="medium"
                color="red"
              ></custom-button>
              <custom-button
                v-if="user.patreon_account"
                :ignoreclick="true"
                text="Remove Patreon"
                alt="Remove Patreon"
                size="medium"
                color="red"
                :loading="saveCard === 'connections' && saveState === 'saving'"
                :disabled="saveCard === 'connections' && saveState === 'saving'"
                @click="removePatreon()"
              ></custom-button>
            </div>
          </div>
        </panel-box>
    </div>

    <div class="text-center text-sm text-subtext">
      To request new settings, please contact us directly at
      <a href="mailto:Zemill@heroesprofile.com" class="link">Zemill@heroesprofile.com</a>,
      or open a discussion on
      <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" class="link">GitHub</a>.
    </div>
  </div>
  </div>

</template>

<script>
import Cookies from 'js-cookie';

export default {
  name: 'ProfileSettings',
  props: {
    user: Object,
    filters: Object,
    locale: { type: String, default: 'en' },
    locales: { type: Object, default: () => ({}) },
    availableFlair: {
      type: Array,
      default: () => [],
    },
    // Avatar borders this account has earned: 'owner', 'patreon', 'void_eye'
    availableBorders: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      dropdownOpen: false,
      // null, 'saving' or 'saved', shown in the top right of the card the change was made in
      saveState: null,
      // The card last clicked in: general, global, player, talents, flair or connections
      saveCard: null,
      savedTimer: null,
      // 'left' = show, 'right' = hide
      flairSides: { owner: 'left', patreon: 'left', void_eye: 'left' },
      flairHide: {},
      // The nav avatar's border: 'owner', 'patreon', 'void_eye' or 'none'
      avatarBorder: null,
      borderChange: {},

      usergametype: null,
      playermultigametype: null,
      saveplayermultigametype: null,
      usermultigametype: null,
      savemultigametype: null,
      advancedfiltering: null,
      talentBuildType: null,
      darkmode: 'left',
      talentbuilderstyle: 'left',
      talentbuilderload: 'left',
      userLocale: this.locale,
      playerhistorytable: 'left',
      playerload: 'true',
      customgames: false,
      accountVisibility: 'false',

      playerdataloadstyles: [
        { code: 'true',  name: 'Standard' },
        { code: 'false', name: 'No Load' },
      ],
    };
  },
  created() {
    this.accountVisibility = this.user.private == 1 ? 'true' : 'false';
    this.usergametype = this.defaultGameType;
    this.playermultigametype = this.defaultPlayerMultiGameType;
    this.saveplayermultigametype = this.playermultigametype;
    this.usermultigametype = this.defaultMultiGameType;
    this.savemultigametype = this.usermultigametype;
    this.advancedfiltering = this.defaultAdvancedFiltering;
    this.talentBuildType = this.defaultBuildType;
    this.darkmode = this.defaultDarkMode;
    this.talentbuilderstyle = this.defaultTalentBuilderStyle;
    this.talentbuilderload = this.user.user_settings.some(item => item.setting === 'talentbuilderload' && item.value === 'filter') ? 'right' : 'left';
    this.playerhistorytable = this.defaultPlayerhistorytable;
    this.customgames = this.defaultCustomGames;
    this.playerload = this.defaultPlayerLoad;
    const border = this.user.user_settings.find(item => item.setting === 'avatar_border');
    this.avatarBorder = border && (border.value === 'none' || this.availableBorders.includes(border.value)) ? border.value : (this.availableBorders[0] || 'none');
    ['owner', 'patreon', 'void_eye'].forEach((key) => {
      const setting = this.user.user_settings.find(item => item.setting === `flair_hide_${key}`);
      this.flairSides[key] = setting && setting.value == 1 ? 'right' : 'left';
    });
  },
  computed: {
    localeOptions() {
      return Object.entries(this.locales).map(([code, name]) => ({ code, name }));
    },
    // The same classes the nav gives the picture (nav.blade.php)
    borderPreviewClass() {
      return { owner: 'border-2 owner-glow', patreon: 'border-2 border-gold patreon-glow', void_eye: 'border-2 border-[#a77bf3] void-glow', none: '' };
    },
    // The earned borders, then None, in the tab button's order
    borderOptions() {
      const labels = { owner: 'Site Owner', patreon: 'Patreon', void_eye: 'Mark of the Void' };
      return [...this.availableBorders.map(key => ({ key, label: labels[key] })), { key: 'none', label: 'None' }];
    },
    flairOptions() {
      const all = [
        { key: 'owner', label: 'Site Owner', description: 'The crown shown for the Heroes Profile owner.', icon: 'fas fa-crown owner-crown' },
        { key: 'patreon', label: 'Patreon', description: 'The star shown for Patreon supporters.', icon: 'fas fa-star', iconClass: 'text-gold' },
        { key: 'void_eye', label: 'Mark of the Void', description: "Earned by finding Xal'atath's eye. Hiding it does not affect your ad-free time.", icon: 'void-eye-img' },
      ];
      return all.filter(flair => this.availableFlair.includes(flair.key));
    },
    defaultMultiGameType() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'multi_game_type');
        if (setting && setting.value.trim() !== '') {
          return setting.value.split(',').map(v => v.trim());
        }
      }
      return ['sl'];
    },
    defaultGameType() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'game_type');
        return setting ? setting.value : 'sl';
      }
      return 'sl';
    },
    defaultPlayerMultiGameType() {
      let setting = this.user.user_settings.find(item => item.setting === 'player_multi_game_type');
      if (setting && setting.value.trim() !== '') {
        return setting.value.split(',').map(v => v.trim());
      }
      return [];
    },
    defaultBuildType() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'talentbuildtype');
        return setting ? setting.value : this.filters.talent_build_types[0].code;
      }
      return this.filters.talent_build_types[0].code;
    },
    defaultAdvancedFiltering() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'advancedfiltering');
        return setting ? setting.value : 'false';
      }
      return 'false';
    },
    defaultDarkMode() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'darkmode');
        return (setting && setting.value == 1) ? 'right' : 'left';
      }
      return 'left';
    },
    defaultTalentBuilderStyle() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'talentbuilderstyle');
        return (setting && setting.value === 'horizontal') ? 'right' : 'left';
      }
      return 'left';
    },
    defaultPlayerhistorytable() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'playerhistorytable');
        return (setting && setting.value == 1) ? 'right' : 'left';
      }
      return 'left';
    },
    defaultCustomGames() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'customgames');
        return setting ? (setting.value == 1 ? true : false) : false;
      }
      return false;
    },
    defaultPlayerLoad() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'playerload');
        return setting ? setting.value : 'true';
      }
      return 'true';
    },
  },
  methods: {
    // Runs a save with the note showing Saving..., then Saved for a moment
    async withSaveState(save) {
      clearTimeout(this.savedTimer);
      this.saveState = 'saving';
      try {
        await save();
        this.saveState = 'saved';
        this.savedTimer = setTimeout(() => { this.saveState = null; }, 3000);
      } catch (error) {
        this.saveState = null;
      }
    },
    saveSettings() {
      return this.withSaveState(() => this.postSettings());
    },
    async postSettings() {
      let darkmodeinput = this.darkmode === 'right' ? true : false;
      let playerhistorytableinput = this.playerhistorytable === 'right' ? true : false;

      await this.$axios.post('/api/v1/profile/save/settings', {
        usergametype: this.usergametype,
        playermultigametype: this.saveplayermultigametype,
        usermultigametype: this.savemultigametype,
        advancedfiltering: this.advancedfiltering,
        talentbuildtype: this.talentBuildType,
        talentbuilderstyle: this.talentbuilderstyle === 'right' ? 'horizontal' : 'vertical',
        talentbuilderload: this.talentbuilderload === 'right' ? 'filter' : 'auto',
        darkmode: darkmodeinput,
        playerload: this.playerload,
        customgames: this.customgames,
        playerhistorytable: playerhistorytableinput,
        ...this.flairHide,
        ...this.borderChange,
      });
      this.flairHide = {};
      this.borderChange = {};
      this.usermultigametype = this.savemultigametype;
      this.playermultigametype = this.saveplayermultigametype;
    },
    async saveLocale() {
      if (this.userLocale === this.locale) {
        return;
      }

      Cookies.set('locale', this.userLocale, { expires: 365, path: '/' });
      await this.$axios.post('/api/v1/profile/save/settings', { locale: this.userLocale }).catch(() => {});
      window.location.reload();
    },
    accountvisibilitysetting(side) {
      this.accountVisibility = side === 'right' ? 'true' : 'false';
      this.withSaveState(() => this.$axios.post('/api/v1/profile/set/account/visibility', {
        accountVisibility: this.accountVisibility,
      }));
    },
    // Shows "Removing Patreon..." in the card until the page reloads with the account unlinked
    async removePatreon() {
      if (this.saveCard === 'connections' && this.saveState === 'saving') return;
      this.saveCard = 'connections';
      this.saveState = 'saving';
      try {
        await this.$axios.post('/api/v1/profile/remove/patreon', {});
        window.location.href = '/Profile/Settings';
      } catch (error) {
        this.saveState = null;
      }
    },
    handleInputChange(eventPayload) {
      if (eventPayload.type === 'single') {
        const { field, value } = eventPayload;
        if (field === 'Talent Build Type') {
          this.talentBuildType = value;
        } else if (field === 'Game Type') {
          this.usergametype = value;
        } else if (field === 'Player Load') {
          this.playerload = value;
        } else if (field === 'Language') {
          this.userLocale = value;
        }
      } else if (eventPayload.type === 'multi') {
        if (eventPayload.field === 'Game Type') {
          this.savemultigametype = eventPayload.value;
        } else if (eventPayload.field === 'Player Game Type') {
          this.saveplayermultigametype = [...eventPayload.value];
        }
      }
    },
    darkmodesetting(side) {
      this.darkmode = side;
      this.saveSettings();
    },
    talentbuilderstylesetting(side) {
      this.talentbuilderstyle = side;
      this.saveSettings();
    },
    talentbuilderloadsetting(side) {
      this.talentbuilderload = side;
      this.saveSettings();
    },
    playermatchhistorystylesetting(side) {
      this.playerhistorytable = side;
      this.saveSettings();
    },
    avatarbordersetting(key) {
      this.avatarBorder = key;
      this.borderChange = { avatar_border: this.avatarBorder };
      this.saveSettings();
    },
    flairsetting(key, side) {
      this.flairSides[key] = side;
      this.flairHide = { [`flair_hide_${key}`]: side === 'right' };
      this.saveSettings();
    },
  },
};
</script>

