<template>
  <div>
    <page-heading :heading="'Streamers on Twitch'" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1200px] px-4 mt-6">
      <div class="bg-lighten border-l-4 border-yellow rounded p-3 mb-6 text-sm">
        <strong>Beta:</strong> the Heroes Profile Twitch extension is still in testing and you may encounter
        issues. Report problems at <a href="/Contact" class="link">heroesprofile.com/Contact</a> or zemill@heroesprofile.com.
      </div>

      <div id="how-to-watch" class="bg-lighten rounded-lg mb-8 scroll-mt-4">
        <button
          @click="toggleHowTo"
          class="w-full flex items-center gap-2 px-4 py-3 text-left text-lg font-semibold"
          :aria-expanded="showHowTo ? 'true' : 'false'"
        >
          <span class="text-lteal text-sm">{{ showHowTo ? '▼' : '►' }}</span>
          How to watch a stream with the extension
        </button>

        <div v-if="showHowTo" class="px-4 pb-4 text-sm">
          <h3 class="font-semibold mb-1">On a computer</h3>
          <ol class="list-decimal list-inside space-y-1 mb-4 text-gray-light">
            <li>Open one of the live channels above on twitch.tv.</li>
            <li>
              Move your mouse over the video. A small Heroes Profile icon appears against the
              <strong>right-hand edge of the player</strong>, about halfway down. It is easy to
              miss, and it disappears again when you move the mouse away from the video.
            </li>
            <li>Click the icon. The panel opens over the stream.</li>
            <li>Click the icon again, or the ✕ in the panel's title bar, to close it.</li>
          </ol>
          <div class="grid gap-4 sm:grid-cols-[2fr,1fr] items-start mb-4">
            <figure>
              <img
                src="/images/twitch/extension-icon.webp"
                alt="A Twitch stream with the Heroes Profile icon circled against the right edge of the video player"
                class="rounded-lg border border-lighten w-full"
                loading="lazy"
              />
              <figcaption class="text-xs text-gray-medium mt-1">
                The icon sits against the right edge of the video, and only appears while your mouse
                is over the player.
              </figcaption>
            </figure>

            <figure>
              <img
                src="/images/twitch/extension-icon-zoom.webp"
                alt="Close-up of the Heroes Profile icon on the edge of the Twitch player"
                class="rounded-lg border border-lighten w-full"
                loading="lazy"
              />
              <figcaption class="text-xs text-gray-medium mt-1">
                Close up: look for the Heroes Profile logo.
              </figcaption>
            </figure>
          </div>

          <p class="mb-4 text-gray-light">
            It works the same in theatre mode and fullscreen, and you can keep it open while you
            watch.
          </p>

          <figure class="mb-4">
            <img
              src="/images/twitch/extension-open.webp"
              alt="The Heroes Profile Live panel open over a stream, showing both teams with heroes, talents and win rates"
              class="rounded-lg border border-lighten w-full max-w-[700px]"
              loading="lazy"
            />
            <figcaption class="text-xs text-gray-medium mt-1">
              The panel open on a stream, showing an example lobby.
            </figcaption>
          </figure>

          <h3 class="font-semibold mb-1">On the Twitch mobile app</h3>
          <p class="mb-4 text-gray-light">
            Open the channel, then swipe across from the chat to the extensions panel below the
            stream. The same lobby is shown in a single column.
          </p>

          <h3 class="font-semibold mb-1">What you will see</h3>
          <ul class="list-disc list-inside space-y-1 mb-4 text-gray-light">
            <li>Both teams, each in the column matching its side of the map in game.</li>
            <li>Every player's hero, win rate and account level from Heroes Profile.</li>
            <li>Talents as they are picked. Hover one to read what it does.</li>
            <li><strong>Copy build</strong> copies a code you can paste into the game's chat to try that build yourself.</li>
            <li>A player's name opens their Heroes Profile page in a new tab.</li>
          </ul>

          <h3 class="font-semibold mb-1">If it says "Waiting for the next game"</h3>
          <p class="text-gray-light">
            That is the normal idle state: it fills in once the streamer is in a lobby. Streamers can
            also set a delay, so what you see can be up to 15 minutes behind the game to stop stream
            sniping. Nothing appears at all if the streamer is not running the uploader.
          </p>

          <p class="mt-4 text-xs text-gray-medium">
            Link to this guide: <a :href="howToUrl" class="link">{{ howToUrl }}</a>
          </p>
        </div>
      </div>

      <h2 class="bg-teal px-4 py-3 rounded-t-lg text-lg font-semibold flex items-center gap-2">
        <span class="inline-block w-2 h-2 rounded-full bg-red"></span>
        Live Now
        <span class="text-sm font-normal">({{ directory.live.length }})</span>
      </h2>
      <div class="bg-lighten rounded-b-lg p-4 mb-8">
        <p v-if="!directory.live.length" class="text-sm text-gray-medium">
          Nobody using the extension is live right now. Check back later.
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <a
            v-for="stream in directory.live"
            :key="stream.login"
            :href="stream.url"
            target="_blank"
            rel="noopener"
            class="bg-darken rounded-lg overflow-hidden hover:ring-2 hover:ring-teal transition"
          >
            <div class="relative">
              <img
                v-if="stream.thumbnail_url"
                :src="stream.thumbnail_url"
                :alt="stream.display_name + ' stream preview'"
                class="w-full aspect-video object-cover"
                loading="lazy"
              />
              <span class="absolute top-2 left-2 bg-red text-white text-xs px-2 py-0.5 rounded">LIVE</span>
              <span class="absolute bottom-2 left-2 bg-black/70 text-white text-xs px-2 py-0.5 rounded">
                {{ stream.viewer_count.toLocaleString() }} viewers
              </span>
            </div>
            <div class="p-3">
              <div class="font-semibold">{{ stream.display_name }}</div>
              <div class="text-sm text-gray-medium truncate" :title="stream.title">{{ stream.title }}</div>
              <div v-if="stream.game_name" class="text-xs text-lteal mt-1">{{ stream.game_name }}</div>
            </div>
          </a>
        </div>
      </div>

      <h2 class="bg-blue px-4 py-3 rounded-t-lg text-lg font-semibold">
        All Streamers
        <span class="text-sm font-normal">({{ directory.offline.length }} offline)</span>
      </h2>
      <div class="bg-lighten rounded-b-lg p-4 mb-8">
        <p v-if="!directory.offline.length" class="text-sm text-gray-medium">No other listed streamers yet.</p>

        <ul v-else class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
          <li v-for="streamer in directory.offline" :key="streamer.login" class="bg-darken rounded p-3">
            <a :href="streamer.url" target="_blank" rel="noopener" class="link font-semibold">{{ streamer.display_name }}</a>
            <div v-if="streamer.battletag && streamer.blizz_id" class="text-xs mt-1">
              <a :href="profileUrl(streamer)" class="hover:text-lteal">Heroes Profile: {{ streamer.battletag }}</a>
            </div>
          </li>
        </ul>
      </div>

      <div class="bg-lighten rounded-lg p-6 mb-8 text-sm">
        <h2 class="text-lg mb-2">Stream Heroes of the Storm?</h2>
        <p class="mb-2">
          The Heroes Profile Twitch extension shows your viewers everyone in your game — heroes,
          talents as they are picked, win rates and account levels — without you lifting a finger. It is included
          with any Heroes Profile API plan, and every channel gets a free month to try it.
          It reads temporary files the game writes as it goes, so it is not 100% real time and
          talent picks can lag behind the game.
        </p>
        <p>
          <a href="/Api/Account#twitch" class="link">Set it up</a> ·
          <a href="https://dashboard.twitch.tv/extensions/chu7yxvts476q2ys0pben0ij8iagsk-2.0.0" target="_blank" rel="noopener" class="link">Extension on Twitch</a> ·
          <a href="/Twitch/Guidelines" class="link">Streamer code of conduct</a>
        </p>
      </div>

      <p class="text-xs text-gray-medium mb-8">Updated {{ updatedAt }}. Refreshes every few minutes.</p>
    </div>
  </div>
</template>

<script>
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
      infoText: "Heroes of the Storm streamers using the Heroes Profile Twitch extension. Live channels are listed first.",
      // Opened by #how-to-watch, so the guide can be linked to directly.
      showHowTo: false,
      howToUrl: 'https://www.heroesprofile.com/Twitch#how-to-watch',
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
      return this.directory.updated_at ? new Date(this.directory.updated_at).toLocaleTimeString() : '';
    },
  },
  methods: {
    toggleHowTo(){
      this.showHowTo = !this.showHowTo;
      // Keeps the address bar on the shareable url without jumping the page.
      history.replaceState(null, '', this.showHowTo ? '#how-to-watch' : window.location.pathname);
    },
    profileUrl(streamer){
      return `/Player/${encodeURIComponent(streamer.battletag)}/${streamer.blizz_id}/${streamer.region}`;
    },
  },
}
</script>
