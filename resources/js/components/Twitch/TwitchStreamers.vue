<template>
  <div>
    <page-heading heading="Streamers on Twitch" :subheading="$t('Streamers using the Heroes Profile extension')"></page-heading>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 pb-10 flex flex-col gap-6">
      <div class="flex items-center gap-3.5 rounded bg-quest/10 border-l-4 border-quest px-5 py-3.5 text-sm">
        <span class="shrink-0 rounded-md bg-quest px-2 py-0.5 text-xs font-bold text-surface">{{ $t('BETA') }}</span>
        <p>{{ $t('The Twitch extension is still in testing. Report problems on the Contact page or at zemill@heroesprofile.com.') }}</p>
      </div>

      <!-- Opened by #how-to-watch, so the guide can be linked to directly -->
      <div id="how-to-watch" :class="['scroll-mt-4', showHowTo ? 'w-full' : 'w-fit max-w-full']">
        <button type="button" :class="['w-full bg-surface-4 px-4 py-3 flex gap-4 justify-between items-center text-left', showHowTo ? 'rounded-t' : 'rounded']" :aria-expanded="showHowTo" @click="toggleHowTo">
          <h2 class="text-lg">{{ $t('How to watch with the extension') }}</h2>
          <basil-icon name="arrow" :class="['transition-transform', showHowTo ? '-rotate-90' : 'rotate-90']"></basil-icon>
        </button>

        <transition name="drop">
        <div v-if="showHowTo" class="bg-surface-4 rounded-b px-4 md:px-6 pb-6 pt-2 flex max-xl:flex-col gap-4 md:gap-6">
          <div class="flex-1 min-w-0 bg-surface rounded-panel p-4 md:p-6 flex flex-col gap-5">
            <h3 class="text-xl font-bold">{{ $t('On a computer') }}</h3>
            <ol class="flex flex-col gap-2.5">
              <li v-for="(step, index) in steps" :key="index" class="flex items-start gap-3">
                <span class="shrink-0 w-7 h-7 rounded-full bg-teal flex items-center justify-center text-[0.8125rem] font-bold">{{ index + 1 }}</span>
                <span class="pt-0.5">{{ step }}</span>
              </li>
            </ol>
            <div class="grid sm:grid-cols-[1fr_auto_1fr] gap-4">
              <figure v-for="shot in screenshots" :key="shot.src" class="flex flex-col gap-2 min-w-0">
                <img :src="shot.src" :alt="shot.alt" :class="['rounded border border-gray-dark h-[14rem] object-cover', shot.narrow ? 'w-full sm:w-[13rem]' : 'w-full']" loading="lazy" />
                <figcaption class="text-xs text-subtext">{{ shot.caption }}</figcaption>
              </figure>
            </div>
            <p class="flex items-center gap-2.5 text-sm text-subtext"><basil-icon name="info" class="text-lg shrink-0"></basil-icon>{{ $t('Works the same in theatre mode and fullscreen, and you can keep it open while you watch.') }}</p>
          </div>

          <div class="xl:w-[26rem] shrink-0 flex flex-col gap-4">
            <div class="bg-surface rounded-panel px-6 py-5 flex flex-col gap-2.5">
              <h3 class="font-bold text-lg">{{ $t('On the Twitch mobile app') }}</h3>
              <p class="text-sm text-subtext">{{ $t('Open the channel, then swipe from the chat to the extensions panel below the stream. The same lobby is shown in a single column.') }}</p>
            </div>
            <div class="flex-1 bg-surface rounded-panel px-6 py-5 flex flex-col gap-2.5">
              <h3 class="font-bold text-lg">{{ $t("What you'll see") }}</h3>
              <ul class="flex flex-col gap-2.5 text-sm">
                <li v-for="item in whatYouSee" :key="item" class="flex items-start gap-2.5"><span class="shrink-0 mt-2 w-1.5 h-1.5 rounded-full bg-teal"></span>{{ item }}</li>
              </ul>
            </div>
            <div class="rounded bg-quest/10 border-l-4 border-quest px-6 py-5 flex flex-col gap-2">
              <h3 class="font-bold">{{ $t('Says "Waiting for the next game"?') }}</h3>
              <p class="text-sm text-subtext">{{ $t("That's normal: it fills in once the streamer is in a lobby. Streamers can add a delay of up to 15 minutes against stream sniping. Nothing shows if the streamer isn't running the uploader.") }}</p>
            </div>
            <button type="button" class="self-start flex items-center gap-1.5 text-sm font-semibold text-teal hover:underline" @click="copyGuideLink">
              {{ copied ? $t('Copied') : $t('Copy link to this guide') }}<basil-icon name="share" class="text-lg"></basil-icon>
            </button>
          </div>
        </div>
        </transition>
      </div>

      <div class="grid xl:grid-cols-[1fr_32rem] gap-6">
        <panel-box :divider="false">
          <div class="flex items-center gap-3">
            <h2 class="text-2xl font-bold">{{ $t('Live now') }}</h2>
            <span class="flex items-center gap-1.5 rounded-lg bg-surface-1 px-2.5 py-1 text-sm font-semibold text-subtext"><span class="w-2 h-2 rounded-full bg-red"></span>{{ $t(':count live', { count: directory.live.length }) }}</span>
          </div>

          <div v-if="!directory.live.length" class="flex-1 flex flex-col items-center justify-center gap-2.5 text-center rounded bg-surface-1 border border-dashed border-gray-dark px-4 py-10">
            <span class="w-16 h-16 rounded-full bg-[#9145ff]/20 text-[#9145ff] flex items-center justify-center text-2xl"><i class="fas fa-play"></i></span>
            <p class="text-lg font-semibold">{{ $t('Nobody using the extension is live right now') }}</p>
            <p class="text-sm text-subtext">{{ $t('Check back later. The list refreshes every few minutes') }}<template v-if="updatedAt"> · {{ $t('Updated :updatedat', { updatedat: updatedAt }) }}</template></p>
          </div>

          <template v-else>
            <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-3">
              <a v-for="stream in directory.live" :key="stream.login" :href="stream.url" target="_blank" rel="noopener" class="group/bt rounded bg-surface-1 hover:bg-surface-2 overflow-hidden transition-colors">
                <div class="relative">
                  <img v-if="stream.thumbnail_url" :src="stream.thumbnail_url" :alt="$t(':name stream preview', { name: stream.display_name })" class="w-full aspect-video object-cover" loading="lazy" />
                  <div v-else class="w-full aspect-video bg-surface-3"></div>
                  <span class="absolute top-2 left-2 rounded-md bg-red px-2 py-0.5 text-xs font-bold">{{ $t('LIVE') }}</span>
                  <span class="absolute bottom-2 left-2 rounded-md bg-black/70 px-2 py-0.5 text-xs">{{ $t(':count viewers', { count: stream.viewer_count.toLocaleString() }) }}</span>
                </div>
                <div class="p-3 flex flex-col gap-0.5 min-w-0">
                  <span class="font-semibold"><span class="group-hover/bt:underline">{{ stream.display_name }}</span><basil-icon name="arrow-small" class="text-xs ml-2 opacity-0 group-hover/bt:opacity-100 transition-opacity"></basil-icon></span>
                  <span class="text-sm text-subtext truncate" :title="stream.title">{{ stream.title }}</span>
                </div>
              </a>
            </div>
            <p v-if="updatedAt" class="text-xs text-subtext">{{ $t('Updated :updatedat', { updatedat: updatedAt }) }}</p>
          </template>
        </panel-box>

        <panel-box :divider="false" class="border-t-4 border-[#9145ff] !gap-4">
          <span class="text-[0.8125rem] font-bold uppercase tracking-wider text-[#9145ff]">{{ $t('For streamers') }}</span>
          <h2 class="text-2xl font-bold">{{ $t('Stream Heroes of the Storm?') }}</h2>
          <p class="text-subtext">{{ $t("The extension shows your viewers everyone in your game: heroes, talents as they're picked, win rates and account levels, with no work from you.") }}</p>
          <ul class="flex flex-col gap-2 text-sm">
            <li v-for="item in pitch" :key="item" class="flex items-center gap-2.5"><span class="shrink-0 w-1.5 h-1.5 rounded-full bg-[#9145ff]"></span>{{ item }}</li>
          </ul>
          <div class="flex flex-wrap gap-3 mt-auto">
            <custom-button class="flex-1 font-semibold" href="/Api/Account#twitch" text="Set it up" alt="Set it up" color="teal"></custom-button>
            <custom-button class="flex-1 font-bold" href="https://dashboard.twitch.tv/extensions/chu7yxvts476q2ys0pben0ij8iagsk-2.0.0" :targetblank="true" text="Extension on Twitch" alt="Extension on Twitch" color="blue"></custom-button>
          </div>
          <a href="/Twitch/Guidelines" class="self-start text-sm font-semibold text-teal hover:underline">{{ $t('Streamer code of conduct') }}<basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
        </panel-box>
      </div>

      <panel-box title="All streamers" subtitle="Channels with the Heroes Profile extension installed." :divider="false">
        <template #right>
          <span class="ml-auto self-center rounded-lg bg-surface-1 px-3 py-1 text-sm font-medium text-subtext">{{ $t(':count offline', { count: directory.offline.length }) }}</span>
        </template>
        <p v-if="!streamers.length" class="text-sm text-subtext">{{ $t('No streamers listed yet.') }}</p>
        <!-- The row opens the channel; the profile line is its own link, laid over it -->
        <ul v-else class="grid sm:grid-cols-2 xl:grid-cols-4 gap-3">
          <li v-for="streamer in streamers" :key="streamer.login" class="group/bt relative flex items-center gap-3.5 rounded bg-surface-1 hover:bg-surface-2 px-4 py-3.5 min-w-0 transition-colors">
            <span class="relative shrink-0 w-12 h-12 rounded-full bg-[#9145ff]/20 text-[#9145ff] flex items-center justify-center text-xl font-bold">
              {{ streamer.display_name.charAt(0).toUpperCase() }}
              <span :class="['absolute left-9 top-9 w-3 h-3 rounded-full border-[3px] border-surface-1', streamer.live ? 'bg-red' : 'bg-[#808794]']"></span>
            </span>
            <span class="flex-1 min-w-0 flex flex-col gap-0.5">
              <span class="flex items-center gap-2 min-w-0">
                <a :href="streamer.url" target="_blank" rel="noopener" class="font-semibold truncate hover:underline after:absolute after:inset-0">{{ streamer.display_name }}</a>
                <span v-if="streamer.live" class="shrink-0 rounded-md bg-red px-1.5 text-xs font-bold">{{ $t('LIVE') }}</span>
              </span>
              <a v-if="streamer.battletag && streamer.blizz_id" :href="profileUrl(streamer)" class="relative z-10 self-start max-w-full truncate text-sm text-subtext hover:text-white hover:underline">{{ $t('Heroes Profile: :battletag', { battletag: streamer.battletag }) }}</a>
              <span v-else class="text-sm text-subtext">{{ $t('Profile not linked') }}</span>
            </span>
            <basil-icon name="arrow-small" class="shrink-0 text-xs"></basil-icon>
          </li>
        </ul>
        <p class="text-xs text-subtext">{{ $t('Offline channels are shown with a grey dot. Live channels move to the top with a red LIVE badge.') }}</p>
      </panel-box>
    </div>
  </div>
</template>

<script>
import { formatDate as formatLocalDate } from '../../utils/formatGameDate';
export default {
  name: 'TwitchStreamers',
  props: {
    directory: {
      type: Object,
      default: () => ({ live: [], offline: [], updated_at: null }),
    },
  },
  data(){
    return {
      // Opened by #how-to-watch, so the guide can be linked to directly.
      showHowTo: false,
      copied: false,
      howToUrl: 'https://www.heroesprofile.com/Twitch#how-to-watch',
      steps: [
        this.$t('Open one of the live channels on twitch.tv.'),
        this.$t('Move your mouse over the video. A small Heroes Profile icon appears on the right-hand edge of the player, about halfway down. It hides again when the mouse leaves the video.'),
        this.$t('Click the icon. The panel opens over the stream.'),
        this.$t("Click the icon again, or the ✕ in the panel's title bar, to close it."),
      ],
      screenshots: [
        { src: '/images/twitch/extension-icon.webp', alt: 'A Twitch stream with the Heroes Profile icon circled against the right edge of the video player', caption: this.$t('Hover the player: the icon sits on its right edge.') },
        { src: '/images/twitch/extension-icon-zoom.webp', alt: 'Close-up of the Heroes Profile icon on the edge of the Twitch player', caption: this.$t('Close up: look for the Heroes Profile logo.'), narrow: true },
        { src: '/images/twitch/extension-open.webp', alt: 'The Heroes Profile Live panel open over a stream, showing both teams with heroes, talents and win rates', caption: this.$t('The panel open on a stream, showing an example lobby.') },
      ],
      whatYouSee: [
        this.$t('Both teams, each on the side of the map they play in game.'),
        this.$t("Every player's hero, win rate and account level."),
        this.$t("Talents as they're picked. Hover one to read it."),
        this.$t('Copy build: paste the code in game chat to try it.'),
        this.$t("A player's name opens their Heroes Profile page."),
      ],
      pitch: [
        this.$t('Free first month for every channel'),
        this.$t('Included with any Heroes Profile API plan'),
        this.$t('Not 100% real time: talent picks can lag behind the game'),
      ],
    }
  },
  mounted(){
    if(window.location.hash === '#how-to-watch'){
      this.showHowTo = true;
      this.$nextTick(() => document.getElementById('how-to-watch')?.scrollIntoView());
    }
  },
  computed: {
    updatedAt(){
      return this.directory.updated_at ? formatLocalDate(this.directory.updated_at, { timeStyle: 'short' }) : '';
    },
    // Live channels first, then the offline ones
    streamers(){
      return [
        ...this.directory.live.map(streamer => ({ ...streamer, live: true })),
        ...this.directory.offline,
      ];
    },
  },
  methods: {
    toggleHowTo(){
      this.showHowTo = !this.showHowTo;
      // Keeps the address bar on the shareable url without jumping the page.
      history.replaceState(null, '', this.showHowTo ? '#how-to-watch' : window.location.pathname);
    },
    copyGuideLink(){
      navigator.clipboard.writeText(this.howToUrl).then(() => {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 3000);
      });
    },
    profileUrl(streamer){
      return `/Player/${encodeURIComponent(streamer.battletag)}/${streamer.blizz_id}/${streamer.region}`;
    },
  },
}
</script>
