<template>
  <div>
  <page-heading :heading="'Settings'"></page-heading>
  <div class="relative flex mx-auto w-full max-w-[1200px] mt-[2vh] h-[85vh]">
    <div v-if="isLoading" class="absolute inset-0 z-50 rounded-lg overflow-hidden">
      <loading-component :textoverride="true">Saving...</loading-component>
    </div>

    <!-- Left sidebar -->
    <div class="w-[180px] shrink-0 bg-lighten rounded-l-lg flex flex-col">
      <nav class="flex flex-col pt-4">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="text-left px-6 py-4 text-sm font-medium transition-colors"
          :class="activeTab === tab.key ? 'bg-teal text-white' : 'text-gray-300 hover:bg-gray-700'"
        >
          {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Right panel -->
    <div class="flex-1 bg-lighten rounded-r-lg border-l border-gray-600 overflow-y-auto">
      <transition name="fade">
        <div v-if="settingsSaved" class="bg-teal text-white text-center py-2 text-sm rounded-tr-lg">
          Settings Saved
        </div>
      </transition>

      <!-- General -->
      <div v-if="activeTab === 'general'" class="p-8">
        <h2 class="text-xl font-bold mb-6 text-teal">General</h2>
        <div class="space-y-6">
          <div>
            <h3 class="mb-2">Table Style</h3>
            <tab-button tab1text="Light" tab2text="Dark" :ignoreclick="true" @tab-click="darkmodesetting" :overridedefaultside="darkmode"></tab-button>
          </div>
          <div>
            <h3 class="mb-1">Account Visibility</h3>
            <p class="text-sm text-gray-400 mb-3">Control whether your profile is visible to other users.</p>
            <single-select-filter
              :values="privateOptions"
              :text="'Account Visibility'"
              @dropdown-closed="setAccountVisbility()"
              @input-changed="handleInputChange"
              :defaultValue="accountVisibility"
              :trackclosure="true"
            ></single-select-filter>
          </div>
        </div>
      </div>

      <!-- Global Pages -->
      <div v-if="activeTab === 'global'" class="p-8">
        <h2 class="text-xl font-bold mb-6 text-teal">Global Pages</h2>
        <div class="space-y-6">
          <div>
            <h3 class="mb-2">Default Multi-Select Game Type</h3>
            <div class="w-fit">
              <multi-select-filter
                :values="filters.game_types_full"
                :text="'Game Type'"
                @dropdown-closed="saveSettings()"
                @input-changed="handleInputChange"
                :defaultValue="usermultigametype"
                :trackclosure="true"
              ></multi-select-filter>
            </div>
          </div>
          <div>
            <h3 class="mb-2">Default Single Game Type</h3>
            <single-select-filter
              :values="filters.game_types_full"
              :text="'Game Type'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="usergametype"
              :trackclosure="true"
            ></single-select-filter>
          </div>
          <div>
            <h3 class="mb-2">Default Build Type</h3>
            <single-select-filter
              :values="filters.talent_build_types"
              :text="'Talent Build Type'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="talentBuildType"
              :trackclosure="true"
            ></single-select-filter>
          </div>
          <div>
            <h3 class="mb-2">Show Advanced Filtering Options</h3>
            <single-select-filter
              :values="advancedfilteringoptions"
              :text="'Advanced Filtering'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="advancedfiltering"
              :trackclosure="true"
            ></single-select-filter>
          </div>
        </div>
      </div>

      <!-- Player -->
      <div v-if="activeTab === 'player'" class="p-8">
        <h2 class="text-xl font-bold mb-6 text-teal">Player</h2>
        <div class="space-y-6">
          <div>
            <h3 class="mb-2">Match History Style</h3>
            <tab-button tab1text="Table" tab2text="Compact" :ignoreclick="true" @tab-click="playermatchhistorystylesetting" :overridedefaultside="playerhistorytable"></tab-button>
          </div>
          <div>
            <h3 class="mb-2">Default HP MMR Breakdown Game Type</h3>
            <single-select-filter
              :values="filters.game_types_full"
              :text="'HP MMR Game Type'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="mmrplayerusergametype"
              :trackclosure="true"
            ></single-select-filter>
          </div>
          <div>
            <h3 class="mb-2">Player Data Initial Load Style</h3>
            <single-select-filter
              :values="playerdataloadstyles"
              :text="'Player Load'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="playerload"
              :trackclosure="true"
            ></single-select-filter>
          </div>
          <div>
            <h3 class="mb-2">Show Custom Games (Match History only)</h3>
            <single-select-filter
              :values="showcustomgamesoptions"
              :text="'Show Custom Games'"
              @dropdown-closed="saveSettings()"
              @input-changed="handleInputChange"
              :defaultValue="customgames"
              :trackclosure="true"
            ></single-select-filter>
          </div>
        </div>
      </div>

      <!-- Connections -->
      <div v-if="activeTab === 'connections'" class="p-8">
        <h2 class="text-xl font-bold mb-6 text-teal">Connections</h2>
        <div class="space-y-8">
          <div class="">
            <h3 class="mb-1">Patreon</h3>
            <p class="text-sm text-gray-400 mb-3">Link your Patreon account to unlock supporter features.</p>
            <div class="flex items-center gap-4">
              <span v-if="user.patreon_account" class="bg-teal px-3 py-1 rounded text-sm">Connected</span>
              <custom-button
                v-if="!user.patreon_account"
                :href="'/authenticate/patreon'"
                text="Login with Patreon"
                alt="Login with Patreon"
                size="medium"
                color="blue"
              ></custom-button>
              <custom-button
                v-if="user.patreon_account"
                :ignoreclick="true"
                text="Remove Patreon"
                alt="Remove Patreon"
                size="medium"
                color="red"
                @click="removePatreon()"
              ></custom-button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="mx-auto w-full max-w-[1200px] mt-4 px-2 text-sm text-gray-400">
    To request new settings, please contact us directly at
    <a href="mailto:Zemill@heroesprofile.com" class="link">Zemill@heroesprofile.com</a>,
    or open a discussion on
    <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" class="link">GitHub</a>.
  </div>
  </div>

</template>

<script>
export default {
  name: 'ProfileSettings',
  props: {
    user: Object,
    filters: Object,
  },
  data() {
    return {
      isLoading: false,
      activeTab: 'general',
      tabs: [
        { key: 'general',     label: 'General' },
        { key: 'global',      label: 'Global Pages' },
        { key: 'player',      label: 'Player' },
        { key: 'connections', label: 'Connections' },
      ],

      userhero: null,
      usergametype: null,
      mmrplayerusergametype: null,
      usermultigametype: null,
      savemultigametype: null,
      advancedfiltering: null,
      talentBuildType: null,
      darkmode: 'left',
      playerhistorytable: 'left',
      playerload: 'true',
      customgames: false,
      accountVisibility: 'false',
      settingsSaved: false,

      advancedfilteringoptions: [
        { code: 'true',  name: 'Show' },
        { code: 'false', name: "Don't Show" },
      ],
      privateOptions: [
        { code: 'true',  name: 'Private' },
        { code: 'false', name: 'Not Private' },
      ],
      playerdataloadstyles: [
        { code: 'true',  name: 'Standard' },
        { code: 'false', name: 'No Load' },
      ],
      showcustomgamesoptions: [
        { code: false, name: "Don't Show" },
        { code: true,  name: 'Show' },
      ],
    };
  },
  created() {
    this.accountVisibility = this.user.private == 1 ? 'true' : 'false';
    this.usergametype = this.defaultGameType;
    this.mmrplayerusergametype = this.defaultMMRPlayerGameType;
    this.usermultigametype = this.defaultMultiGameType;
    this.savemultigametype = this.usermultigametype;
    this.advancedfiltering = this.defaultAdvancedFiltering;
    this.talentBuildType = this.defaultBuildType;
    this.darkmode = this.defaultDarkMode;
    this.playerhistorytable = this.defaultPlayerhistorytable;
    this.customgames = this.defaultCustomGames;
    this.playerload = this.defaultPlayerLoad;
  },
  computed: {
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
    defaultMMRPlayerGameType() {
      if (this.user.user_settings.length > 0) {
        let setting = this.user.user_settings.find(item => item.setting === 'mmr_player_game_type');
        return setting ? setting.value : 'sl';
      }
      return 'sl';
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
    async saveSettings() {
      this.isLoading = true;

      let darkmodeinput = this.darkmode === 'right' ? true : false;
      let playerhistorytableinput = this.playerhistorytable === 'right' ? true : false;

      try {
        await this.$axios.post('/api/v1/profile/save/settings', {
          userid: this.user.battlenet_accounts_id,
          userhero: this.userhero,
          usergametype: this.usergametype,
          mmrplayerusergametype: this.mmrplayerusergametype,
          usermultigametype: this.savemultigametype,
          advancedfiltering: this.advancedfiltering,
          talentbuildtype: this.talentBuildType,
          darkmode: darkmodeinput,
          playerload: this.playerload,
          customgames: this.customgames,
          playerhistorytable: playerhistorytableinput,
        });
        this.settingsSaved = true;
        setTimeout(() => { this.settingsSaved = false; }, 5000);
        this.usermultigametype = this.savemultigametype;
      } catch (error) {
        // handle error
      }
      this.isLoading = false;
    },
    async setAccountVisbility() {
      try {
        await this.$axios.post('/api/v1/profile/set/account/visibility', {
          userid: this.user.battlenet_accounts_id,
          accountVisibility: this.accountVisibility,
        });
      } catch (error) {
        // handle error
      }
    },
    async removePatreon() {
      try {
        await this.$axios.post('/api/v1/profile/remove/patreon', {
          userid: this.user.battlenet_accounts_id,
        });
        window.location.href = '/Profile/Settings';
      } catch (error) {
        // handle error
      }
    },
    handleInputChange(eventPayload) {
      if (eventPayload.type === 'single') {
        const { field, value } = eventPayload;
        if (field === 'Heroes') {
          this.userhero = this.filters.heroes.find(h => h.code === value)?.name;
        } else if (field === 'Advanced Filtering') {
          this.advancedfiltering = value;
        } else if (field === 'Account Visibility') {
          this.accountVisibility = value;
        } else if (field === 'Talent Build Type') {
          this.talentBuildType = value;
        } else if (field === 'Game Type') {
          this.usergametype = value;
        } else if (field === 'HP MMR Game Type' || field === 'MMR Game Type') {
          this.mmrplayerusergametype = value;
        } else if (field === 'Player Load') {
          this.playerload = value;
        } else if (field === 'Show Custom Games') {
          this.customgames = value;
        }
      } else if (eventPayload.type === 'multi') {
        if (eventPayload.field === 'Game Type') {
          this.savemultigametype = eventPayload.value;
        }
      }
    },
    darkmodesetting(side) {
      this.darkmode = side;
      this.saveSettings();
    },
    playermatchhistorystylesetting(side) {
      this.playerhistorytable = side;
      this.saveSettings();
    },
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s; }
.fade-enter, .fade-leave-to { opacity: 0; }
</style>
