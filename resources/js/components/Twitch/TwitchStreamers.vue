<template>
  <div>
    <page-heading :heading="'Streamers on Twitch'" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1200px] px-4 mt-6">
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
    }
  },
  computed: {
    updatedAt(){
      return this.directory.updated_at ? new Date(this.directory.updated_at).toLocaleTimeString() : '';
    },
  },
  methods: {
    profileUrl(streamer){
      return `/Player/${encodeURIComponent(streamer.battletag)}/${streamer.blizz_id}/${streamer.region}`;
    },
  },
}
</script>
