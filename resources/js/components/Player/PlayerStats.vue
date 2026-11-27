<template>
  <div class="">
    <player-page-top>
      <template #heading>
      <page-heading :heading="'Profile Data'" :battletag="battletag" :region="region" :blizzid="blizzid" :regionstring="regionsmap[region]" :isPatreon="isPatreon" :isOwner="isOwner">
    </page-heading>
</template>
    <div class="w-full">
      <filters
        :onFilter="filterData"
        :filters="filters"
        :isLoading="isLoading"
        :gametypedefault="gametypedefault"
        :defaultseasons="seasonselection"
        :includegametypefull="true"
        :includemultiseason="true"
        :includegamedaterange="true"
        :hideadvancedfilteringbutton="true"
      >
      </filters>
    </div>
    </player-page-top>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div v-if="data == ''" class="flex md:p-20 gap-10 mx-auto justify-center items-between">
      <div class="flex items-center">
        <span>{{ $t('No data for this player and filters') }}</span>
      </div>
    </div>
    <div v-else-if="data" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">

      <panel-box>
      <div class="flex flex-col xl:flex-row gap-6">

        <!-- Player overview -->
        <panel-box ref="overviewCard" :inner="true" class="!gap-8 xl:shrink-0">
          <div class="flex items-center gap-3">
            <icon-with-hover v-if="realRank && realRank.current && realRank.current.league != 'unranked'" popupsize="fit">
              <template #trigger>
                <!-- A league badge's height; Master and Grand Master art is larger and spills over it, so the card keeps one height -->
                <div class="h-12 flex items-center">
                  <real-rank-badge :rank="realRank.current" :width="48"></real-rank-badge>
                </div>
              </template>
              <image-hover-box :title="$t('Storm League')"></image-hover-box>
              <real-rank-badge :rank="realRank.current" :width="96" label="below" label-class="text-subtext mt-1"></real-rank-badge>
            </icon-with-hover>
            <div class="flex flex-col">
              <h2 class="text-3xl font-bold">{{ battletag }}</h2>
              <region-tag :region="regionsmap[region]" :full="true"></region-tag>
            </div>
            <!-- Pinned to the top, so they sit in the corner the same distance from the top and the right, as in the redesign -->
            <div class="card-actions ml-auto self-start flex gap-2 text-header">
              <loading-component v-if="cardDownloading" :compact="true" :textoverride="true">{{ $t('Processing image for download...') }}</loading-component>
              <template v-else>
                <button v-if="canCopyImage" type="button" class="flex p-1.5 hover:text-lteal" :title="cardCopied ? $t('Copied') : $t('Copy card image')" @click="copyCard"><basil-icon class="text-2xl" :name="cardCopied ? 'check' : 'share'"></basil-icon></button>
                <button type="button" class="flex p-1.5 hover:text-lteal" :title="$t('Download card image')" @click="downloadCard"><basil-icon class="text-2xl" name="download"></basil-icon></button>
              </template>
            </div>
          </div>

          <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
            <div class="flex flex-col items-center justify-between gap-1 shrink-0">
              <hero-image-wrapper :rectangle="true" :hero="inputHero" :title="inputHero.name" :excludehover="inputHero.name == 'Auto Select'" size="large"></hero-image-wrapper>
              <div class="text-xl font-semibold leading-[1.2] text-subtext">{{ $t('Level:') }} <span class="text-header">{{ data.account_level.toLocaleString('en-US') }}</span></div>
            </div>

            <overview-stats class="flex-1 min-w-0 w-full" :win-rate="data.win_rate" :stats="overviewStats"></overview-stats>
          </div>

          <div v-for="section in topSections" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
            <div class="flex justify-between items-baseline">
              <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
              <a class="text-header whitespace-nowrap link-more" :href="profileUrl + section.allPath">{{ $t(section.allText) }}</a>
            </div>
            <div class="flex flex-wrap gap-6 max-md:justify-center">
              <group-box v-for="group in section.groups" :key="group.label" class="!m-0" :playerlink="true" :text="group.label" :data="group.data"></group-box>
            </div>
          </div>

          <p class="flex flex-wrap gap-2"><span class="text-subtext">{{ $t('Total Time Played:') }}</span><span class="text-header">{{ formatDurationText(data.total_time_played) }}</span></p>
        </panel-box>

        <div class="flex-1 flex flex-col gap-6 min-w-0">
          <div class="grid 2xl:grid-cols-2 gap-6">
            <!-- Win rate by role -->
            <panel-box :inner="true" :title="$t('Win Rate By Role')" link-text="All Roles" :link-href="profileUrl + '/Role'">
              <!-- Spread over the card, so it and the party card beside it line up -->
              <div class="flex-1 flex flex-col justify-between gap-3">
                <div v-for="role in roleWinRates" :key="role.label" class="flex flex-col gap-1.5">
                  <a class="text-header leading-[1.2] w-fit link-more" :href="profileUrl + '/Role/' + role.label">{{ $t(role.label) }}</a>
                  <stat-bar :value="role.value.toFixed(2)" suffix="%" :color="$winRateColor(role.value)"></stat-bar>
                </div>
              </div>
            </panel-box>

            <player-party-size-panel :data="data" link-text="Party Stats" :link-href="profileUrl + '/Party'"></player-party-size-panel>
          </div>

          <player-mmr-panel :data="data" :link-href="profileUrl + '/MMR'"></player-mmr-panel>
        </div>
      </div>
      </panel-box>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="1" :mobile-override="false"></dynamic-banner-ad>

      <div class="grid xl:grid-cols-2 gap-6">
        <!-- Each card sits in its own outer panel, like the overview above -->
        <panel-box>
          <player-real-rank class="flex-1" :blizzid="blizzid" :region="region" @loaded="realRank = $event"></player-real-rank>
        </panel-box>

        <panel-box>
          <!-- The same hourly counts, by day of the week or by hour of the day -->
          <panel-box v-if="data.weekday_data" class="flex-1" :inner="true" :title="weekdayView === 'hour' ? $t('Time of Day Win Rates') : $t('Week Day Win Rates')">
            <template #right>
              <tab-button :small="true" tab1text="Week Day" tab2text="Time of Day" :ignoreclick="true" :overridedefaultside="weekdayView === 'hour' ? 'right' : 'left'" @tab-click="weekdayView = $event === 'right' ? 'hour' : 'week'"></tab-button>
            </template>
            <player-time-of-day-win-rates v-if="weekdayView === 'hour'" :data="data.weekday_data"></player-time-of-day-win-rates>
            <!-- The days spread down to the bottom of the card, level with the rank card beside it -->
            <player-weekday-win-rates v-else class="flex-1 justify-between" :data="data.weekday_data"></player-weekday-win-rates>
          </panel-box>
          <!-- Week days are only shown to the profile's owner; everyone else sees the panel's shape and why -->
          <panel-box v-else class="flex-1" :inner="true" :title="$t('Week Day Win Rates')" :link-text="authenticated ? null : $t('Log in with Battle.net to see your week day win rates')" :link-href="authenticated ? null : '/Authenticate/Battlenet'">
            <template v-if="authenticated" #right>
              <span class="text-subtext">{{ $t("Only shown to the profile's owner") }}</span>
            </template>
            <!-- Not loading, so the blocks hold still -->
            <div class="flex flex-col gap-3" aria-hidden="true">
              <div v-for="day in 7" :key="day" class="flex flex-col gap-1.5">
                <div class="flex justify-between">
                  <skeleton-box class="w-24 h-5 !animate-none"></skeleton-box>
                  <skeleton-box class="w-20 h-5 !animate-none"></skeleton-box>
                </div>
                <skeleton-box class="h-4 !animate-none"></skeleton-box>
              </div>
            </div>
          </panel-box>
        </panel-box>
      </div>

      <dynamic-banner-ad :patreon-user="patreonUser" :index="2" :mobile-override="false"></dynamic-banner-ad>

      <panel-box v-if="data.matchData" :title="$t('Recent Matches')" link-text="Match History" :link-href="profileUrl + '/Match/History'" :divider="false">
        <div>
          <game-summary-header></game-summary-header>
          <game-summary-box v-for="(item, index) in data.matchData" :key="index" :data="item" :replay-viewer-allowed="replayViewerAllowed" :analytics-cutover="analyticsCutover" :replay-download-blocked="replayDownloadBlocked"></game-summary-box>
        </div>
      </panel-box>

    </div>
    <!-- The load failed: the error page's card in place of the data -->
    <div v-else-if="loadError" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0">
      <error-card :status="loadError.status" :message="loadError.message" @retry="loadError.retry"></error-card>
    </div>
    <!-- The page's layout with grey blocks while it loads; the nav logo spins meanwhile -->
    <div v-else-if="isLoading" class="max-w-[120rem] mx-auto px-4 md:px-8 mt-8 md:mt-0 space-y-8">
      <!-- The cards start where the loaded ones do; the message sits on a tab of the panel above them -->
      <panel-box class="relative md:!rounded-tl-none">
        <loading-component :compact="true" :tab="true" :textoverride="true">{{ $t('Large amount of data. Please be patient. Loading data...') }}</loading-component>
        <!-- Built from the loaded cards' own parts (the same labels, bars and boxes, empty), so nothing moves when the data arrives -->
        <div class="flex flex-col xl:flex-row gap-6">
          <panel-box :inner="true" class="!gap-8 xl:shrink-0">
            <div class="flex items-center gap-3">
              <div class="flex flex-col">
                <h2 class="text-3xl font-bold">{{ battletag }}</h2>
                <region-tag class="skeleton-text w-fit" :region="regionsmap[region]" :full="true"></region-tag>
              </div>
              <div class="ml-auto self-start flex gap-2 invisible" aria-hidden="true">
                <span class="flex p-1.5"><basil-icon class="text-2xl" name="share"></basil-icon></span>
                <span class="flex p-1.5"><basil-icon class="text-2xl" name="download"></basil-icon></span>
              </div>
            </div>
            <div class="flex gap-8 max-sm:flex-col max-sm:items-center">
              <div class="flex flex-col items-center justify-between gap-1 shrink-0">
                <skeleton-box class="w-40 md:w-[14rem] h-[20.125rem] rounded"></skeleton-box>
                <skeleton-box class="h-[1.5rem] w-32"></skeleton-box>
              </div>
              <overview-stats class="flex-1 min-w-0 w-full" :loading="true" :count="8"></overview-stats>
            </div>
            <div v-for="section in [{ title: 'Heroes', allText: 'All Heroes' }, { title: 'Maps', allText: 'All Maps' }]" :key="section.title" class="flex flex-col gap-4 w-fit max-w-full">
              <div class="flex justify-between items-baseline">
                <h3 class="text-2xl font-bold">{{ $t(section.title) }}</h3>
                <span class="skeleton-text whitespace-nowrap">{{ $t(section.allText) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
              </div>
              <div class="flex flex-wrap gap-6 max-md:justify-center">
                <div v-for="label in ['Most Played', 'Highest Win Rate', 'Latest Played']" :key="label" class="w-auto inline-block">
                  <h4 class="text-sm mb-2"><span class="skeleton-text">{{ $t(label) }}</span></h4>
                  <div class="flex flex-wrap gap-3 max-md:gap-2">
                    <skeleton-box v-for="tile in 3" :key="tile" class="w-16 h-16 rounded"></skeleton-box>
                  </div>
                </div>
              </div>
            </div>
            <p class="flex flex-wrap items-center gap-2"><span class="skeleton-text">{{ $t('Total Time Played:') }}</span><skeleton-box class="h-4 w-36"></skeleton-box></p>
          </panel-box>

          <div class="flex-1 flex flex-col gap-6 min-w-0">
            <div class="grid 2xl:grid-cols-2 gap-6">
              <panel-box :inner="true" :title="$t('Win Rate By Role')" link-text="All Roles" :skeleton="true">
                <div class="flex-1 flex flex-col justify-between gap-3">
                  <div v-for="role in ['Bruiser', 'Healer', 'Melee Assassin', 'Ranged Assassin', 'Support', 'Tank']" :key="role" class="flex flex-col gap-1.5">
                    <span class="skeleton-text leading-[1.2] w-fit">{{ $t(role) }}<basil-icon name="arrow-small" class="text-sm ml-3"></basil-icon></span>
                    <stat-bar value="0.00" suffix="%" :hide-value="true"></stat-bar>
                  </div>
                </div>
              </panel-box>
              <player-party-size-panel :data="emptyPartyData" link-text="Party Stats" :skeleton="true"></player-party-size-panel>
            </div>
            <player-mmr-panel :data="{}" :skeleton="true"></player-mmr-panel>
          </div>
        </div>
      </panel-box>

      <div class="grid xl:grid-cols-2 gap-6">
        <!-- The rank card's shape with no rank read (its box and the "How to get your rank" button); the week days stretch beside it -->
        <panel-box v-for="title in ['Storm League Rank', 'Week Day Win Rates']" :key="title">
          <panel-box class="flex-1" :inner="true" :title="title">
            <skeleton-box class="flex-1 min-h-[24.125rem]"></skeleton-box>
            <skeleton-box v-if="title == 'Storm League Rank'" class="h-[3.25rem] w-[26rem] max-w-full rounded"></skeleton-box>
          </panel-box>
        </panel-box>
      </div>

      <panel-box :title="$t('Recent Matches')" link-text="Match History" :skeleton="true" :divider="false">
        <div>
          <game-summary-header :skeleton="true"></game-summary-header>
          <game-summary-skeleton v-for="index in 7" :key="index"></game-summary-skeleton>
        </div>
      </panel-box>
    </div>

  </div>

</template>

<script>
import { toBlob } from 'html-to-image';
import { formatDurationText } from '../../utils/formatDuration';

// html-to-image can't pull Roboto through app.css's @import, so the card image
// gets the latin Roboto faces embedded directly. Fetched once, on first use.
let cardFontCss = null;
function cardFonts() {
  cardFontCss ??= (async () => {
    const css = await (await fetch('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap')).text();
    const faces = css.split('/* ').filter(block => block.startsWith('latin */')).map(block => block.slice('latin */'.length));
    const embedded = await Promise.all(faces.map(async face => {
      const url = face.match(/url\((.+?)\)/)[1];
      const blob = await (await fetch(url)).blob();
      const dataUrl = await new Promise(resolve => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.readAsDataURL(blob);
      });
      return face.replace(url, dataUrl);
    }));
    return embedded.join('\n');
  })().catch(() => {
    // Without the font the card still renders, in the fallback font
    cardFontCss = null;
    return '';
  });
  return cardFontCss;
}

  export default {
    name: 'PlayerStats',
    components: {
    },
    props: {
      playerloadsetting: {
        type: [String, Boolean]
      },
      filters: Object,
      battletag: String,
      blizzid: String, 
      region: String,
      season: Number,
      regionsmap: Object,
      isPatreon: Boolean,
      patreonUser: Boolean,
      gametypedefault: Array,
      replayViewerAllowed: Boolean,
      analyticsCutover: String,
      replayDownloadBlocked: Boolean,
      authenticated: Boolean,

    },
    data(){
      return {
        loadError: null,
        cancelTokenSource: null,
        isLoading: false,
        data: null,
        modifiedgametype: null,
        modifiedseason: null,
        seasonselection: [],
        startdate: null,
        enddate: null,
        inputHero: null,
        disableFilterInput: null,
        realRank: null,
        cardCopied: false,
        cardDownloading: false,
        // Week Day Win Rates panel: 'week' or 'hour'
        weekdayView: 'week',
        canCopyImage: typeof window.ClipboardItem !== 'undefined' && !!(navigator.clipboard && navigator.clipboard.write),
      }
    },
    created(){
      if(this.season){
        // Comes from the query string, so it can arrive as a string
        this.seasonselection = [Number(this.season)];
        this.modifiedseason = [Number(this.season)];
      }

      if(this.gametypedefault && this.gametypedefault.length > 0){
        this.modifiedgametype = [...this.gametypedefault];
      }
    },
    mounted() {
      if(this.settinghero){
        this.inputHero = this.settinghero;
      }else{
        this.inputHero = {
          name: "Auto Select",
          short_name: "autoselect3",
          icon: "autoselect3.jpg",
        };
      }
      if(this.playerloadsetting == null || this.playerloadsetting == true || this.playerloadsetting == "true"){
        this.getData();
      }
    },
    computed: {
     gameTypesWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.game_types_full];
        updatedList.unshift(newValue);
        return updatedList;
      },
      seasonsWithAll() {
        const newValue = { code: 'All', name: 'All' };
        const updatedList = [...this.filters.seasons];
        updatedList.unshift(newValue);
        return updatedList;
      },
      profileUrl(){
        return `/Player/${this.battletag}/${this.blizzid}/${this.region}`;
      },
      overviewStats(){
        return [
          { label: 'First To Ten', value: this.data.first_to_ten_win_rate.toFixed(2) + '%' },
          { label: 'Second To Ten', value: this.data.second_to_ten_win_rate.toFixed(2) + '%' },
          { label: 'Wins', value: this.data.wins.toLocaleString('en-US') },
          { label: 'Losses', value: this.data.losses.toLocaleString('en-US') },
          { label: 'KDR', value: this.data.kdr },
          { label: 'KDA', value: this.data.kda },
          { label: 'MVP', value: this.data.mvp_rate.toFixed(2) + '%' },
          { label: 'AVG. Time On Fire', value: formatDurationText(this.data.average_time_on_fire) },
        ];
      },
      topSections(){
        return [
          { title: 'Heroes', allText: 'All Heroes', allPath: '/Hero', groups: [
            { label: 'Most Played', data: this.data.heroes_three_most_played },
            { label: 'Highest Win Rate', data: this.data.heroes_three_highest_win_rate },
            { label: 'Latest Played', data: this.data.heroes_three_latest_played },
          ] },
          { title: 'Maps', allText: 'All Maps', allPath: '/Map', groups: [
            { label: 'Most Played', data: this.data.maps_three_most_played },
            { label: 'Highest Win Rate', data: this.data.maps_three_highest_win_rate },
            { label: 'Latest Played', data: this.data.maps_three_latest_played },
          ] },
        ];
      },
      // The party card with no games, for the skeleton: its labels and empty bars
      emptyPartyData(){
        const data = {};
        ['one', 'two', 'three', 'four', 'five'].forEach(key => { data[`stack_${key}_wins`] = 0; data[`stack_${key}_losses`] = 0; data[`stack_${key}_win_rate`] = 0; });
        return data;
      },
      roleWinRates(){
        return [
          { label: 'Bruiser', value: this.data.bruiser_win_rate },
          { label: 'Healer', value: this.data.healer_win_rate },
          { label: 'Melee Assassin', value: this.data.melee_assassin_win_rate },
          { label: 'Ranged Assassin', value: this.data.ranged_assassin_win_rate },
          { label: 'Support', value: this.data.support_win_rate },
          { label: 'Tank', value: this.data.tank_win_rate },
        ];
      },
      isOwner(){
        if(this.battletag == "Zemill" && this.blizzid == 67280 && this.region == 1){
          return true;
        }
        return false;
      }
    },
    watch: {
    },
    methods: {
      formatDurationText,
      async getData(){
        this.loadError = null;
        this.disableFilterInput = true;
        this.isLoading = true;

        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled');
        }
        this.cancelTokenSource = this.$axios.CancelToken.source();

        try{
          const response = await this.$axios.post("/api/v1/player", {
            blizz_id: this.blizzid,
            region: this.region,
            battletag: this.battletag,
            game_type: this.modifiedgametype,
            season: this.modifiedseason,
            start_date: this.startdate,
            end_date: this.enddate,
          },
          {
            cancelToken: this.cancelTokenSource.token,
          });
          this.data = response.data;
        }catch(error){
        this.loadError = this.$loadError(error, () => this.getData());
        }finally {
          this.cancelTokenSource = null;
          this.isLoading = false;
          this.disableFilterInput = false;
        }
      },
      // The overview card as it is on the page, without its own buttons.
      async cardImage() {
        return toBlob(this.$refs.overviewCard.$el, {
          pixelRatio: 2,
          fontEmbedCSS: await cardFonts(),
          filter: (node) => !(node.classList && node.classList.contains('card-actions')),
        });
      },
      async downloadCard() {
        this.cardDownloading = true;
        // Shown for at least a second, so a fast download doesn't flash it
        const shown = new Promise(resolve => setTimeout(resolve, 1000));
        try {
          const blob = await this.cardImage();
          const link = document.createElement('a');
          link.download = `heroesprofile-${this.battletag}.png`;
          link.href = URL.createObjectURL(blob);
          link.click();
          URL.revokeObjectURL(link.href);
        } finally {
          await shown;
          this.cardDownloading = false;
        }
      },
      async copyCard() {
        try {
          await navigator.clipboard.write([new ClipboardItem({ 'image/png': this.cardImage() })]);
          this.cardCopied = true;
          setTimeout(() => { this.cardCopied = false; }, 2000);
        } catch (e) {
          // clipboard refused the image
        }
      },
      cancelAxiosRequest() {
        if (this.cancelTokenSource) {
          this.cancelTokenSource.cancel('Request canceled by user');
        }
      },
      filterData(filteredData){
        this.modifiedgametype = filteredData.multi["Game Type"] ? Array.from(filteredData.multi["Game Type"]) : null;
        this.modifiedseason = filteredData.multi.Season ? Array.from(filteredData.multi.Season) : null;
        this.startdate = filteredData.single["From Date"] || null;
        this.enddate = filteredData.single["To Date"] || null;
        this.applyFilter();
      },
      applyFilter(){
        if(!this.isLoading){
          this.data = null;
          this.getData();
        }
      },
    },

  }
</script>