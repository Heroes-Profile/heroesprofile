<template>
  <!-- A section of /Api/Account; the connect flows return to #twitch. -->
  <div id="twitch" class="bg-lighten p-6 mb-8 scroll-mt-4">
    <h2 class="text-lg mb-2">Twitch Extension</h2>
    <p class="text-sm text-gray-medium mb-4">
      Show your viewers the lobby, heroes, talents and player stats of the game you are playing,
      live on your Twitch stream. Included with any paid plan, with a free month to try it.
    </p>
    <p class="text-sm text-gray-medium mb-4">
      The data comes from temporary files the game writes as you play, so it is not 100% real time:
      talent picks can lag behind what is actually happening in the game.
    </p>

    <div>
      <div v-if="error" class="bg-red p-3 mb-4">{{ error }}</div>
      <div v-if="message" class="bg-teal p-3 mb-4">{{ message }}</div>

      <div v-if="channel && channel.suspended" class="bg-darken border-l-4 border-red p-4 mb-4">
        <p class="text-sm">
          <strong>The extension is switched off for this channel.</strong>
          {{ channel.suspension_reason }}
          Write to zemill@heroesprofile.com if you think this is a mistake.
        </p>
      </div>

      <!-- Step 1: Twitch -->
      <div class="bg-darken p-4 mb-4">
        <h3 class="font-semibold mb-4">1. Twitch Channel</h3>

        <template v-if="channel">
          <p class="text-sm mb-4">
            Connected to <strong>{{ channel.twitch_display_name || channel.twitch_login }}</strong>.
          </p>
          <div class="flex flex-wrap gap-2">
            <a href="/Api/Twitch/Link" class="link text-sm">Connect a different channel</a>
            <span class="text-gray-medium">·</span>
            <button @click="unlink" class="link text-sm">Disconnect</button>
          </div>
        </template>

        <template v-else>
          <p class="text-sm text-gray-medium mb-4">
            Sign in with the Twitch account you stream from. We only read your channel
            name — nothing is posted and no permissions are requested.
          </p>
          <a href="/Api/Twitch/Link" class="transition-colors text-white rounded bg-blue hover:bg-lblue py-2 px-4 inline-block">
            Connect Twitch
          </a>
        </template>
      </div>

      <template v-if="channel">
        <!-- Access -->
        <div class="bg-darken p-4 mb-4" :class="accessBorder">
          <h3 class="font-semibold mb-2">Access</h3>
          <p class="text-sm">{{ accessText }}</p>
          <p v-if="!entitled" class="text-sm mt-3">
            Any paid API plan, or a linked Patreon pledge of $5 or more, includes the Twitch extension.
            <a href="/Api/Account/Billing" class="link">Choose a plan</a>
            or link Patreon in the Patreon section above.
          </p>
        </div>

        <!-- Step 2: Battle.net -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">2. Your Battle.net Account</h3>
          <p class="text-sm text-gray-medium mb-4">
            Tells us which player in the lobby is you, so your team is always shown first.
          </p>

          <p v-if="channel.battletag" class="text-sm mb-4">
            Connected as <strong>{{ channel.battletag }}</strong> ({{ channel.region_name }}).
          </p>

          <form action="/Api/Twitch/Battlenet/Link" method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex flex-col">
              <label class="text-sm mb-1" for="twitch-region">Region you play in</label>
              <select id="twitch-region" name="region" v-model="region" class="text-black p-2 rounded">
                <option v-for="(name, id) in regions" :key="id" :value="id">{{ name }}</option>
              </select>
            </div>
            <button type="submit" class="transition-colors text-white rounded bg-blue hover:bg-lblue py-2 px-4">
              {{ channel.battletag ? 'Reconnect Battle.net' : 'Connect Battle.net' }}
            </button>
          </form>
        </div>

        <!-- Step 3: Uploader key -->
        <div v-if="newKey" class="bg-teal p-4 mb-4">
          <h3 class="font-semibold mb-2">Copy Your Uploader Key Now</h3>
          <p class="text-sm mb-3">
            Paste it into the Heroes Profile Uploader under Settings → Twitch Extension.
            This is the only time it will be shown.
          </p>
          <div class="flex flex-wrap gap-2 items-center">
            <code class="bg-darken p-2 break-all flex-grow">{{ newKey }}</code>
            <custom-button @click="copyKey" :text="copied ? 'Copied' : 'Copy'" :alt="'Copy key'" :size="'small'" :ignoreclick="true"></custom-button>
          </div>
        </div>

        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">3. Uploader Key</h3>
          <p class="text-sm text-gray-medium mb-4">
            The Heroes Profile Uploader reads your game as it happens and sends it here with this key.
            Anyone holding it can post to your extension, so treat it like a password.
          </p>

          <p v-if="channel.has_key" class="text-sm mb-2">
            Current key ends in <code class="text-lteal">{{ channel.key_last4 }}</code>.
          </p>
          <p class="text-sm mb-4" :class="channel.uploader_last_seen_at ? '' : 'text-gray-medium'">
            {{ lastSeenText }}
          </p>

          <button
            @click="rotateKey"
            :disabled="rotating"
            class="transition-colors text-white rounded bg-blue hover:bg-lblue py-2 px-4 disabled:bg-gray-medium"
          >
            {{ channel.has_key ? 'Replace Key' : 'Create Key' }}
          </button>
          <p v-if="channel.has_key" class="text-xs text-gray-medium mt-2">
            Replacing it stops the old key working immediately.
          </p>
        </div>

        <!-- Step 4: Settings -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">4. Extension Settings</h3>

          <h4 class="text-sm font-semibold mb-1">Stream-snipe delay</h4>
          <p class="text-sm text-gray-medium mb-3">
            Viewers see lobby, hero and talent data this many seconds after it happens.
            Set it to match or exceed the delay on your stream. Your own config view on
            Twitch always shows it live.
          </p>
          <p class="text-sm text-gray-medium mb-3">
            Twitch's own stream delay only delays the video, not the extension. With a 10 minute
            delay on Twitch and none here, viewers would see your picks 10 minutes before they
            happen on stream.
          </p>
          <p class="text-sm text-gray-medium mb-3">
            Everything is held for the full delay, including for viewers who open the extension
            mid-game: with 10 minutes set, they see the game as it was 10 minutes ago. When one
            game ends and you queue again, viewers may still be watching the last one.
          </p>

          <div class="flex flex-wrap gap-2 mb-3">
            <button
              v-for="preset in delayPresets"
              :key="preset.value"
              @click="delay = preset.value"
              class="transition-colors rounded py-1 px-3 text-sm"
              :class="delay === preset.value ? 'bg-teal text-white' : 'bg-darken hover:bg-teal'"
            >
              {{ preset.label }}
            </button>
          </div>

          <div class="flex items-center gap-2 mb-6">
            <input
              type="number"
              min="0"
              :max="maxdelay"
              v-model.number="delay"
              class="text-black p-2 rounded w-28"
              aria-label="Delay in seconds"
            />
            <span class="text-sm">seconds (0 – {{ maxdelay }})</span>
          </div>

          <label class="flex items-center gap-2 mb-6 text-sm">
            <input type="checkbox" v-model="showStats" />
            Show win rate and account level for each player
          </label>

          <button
            @click="saveSettings"
            :disabled="savingSettings || !delayValid"
            class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4 disabled:bg-gray-medium"
          >
            {{ savingSettings ? 'Saving…' : 'Save Settings' }}
          </button>
          <p v-if="settingsSaved" class="text-sm text-lteal mt-2">Saved. Applies from the next update.</p>
        </div>

        <!-- Step 5: Public listing -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">5. Heroes Profile Streamer Page</h3>
          <p class="text-sm text-gray-medium mb-4">
            List your channel on <a href="/Twitch" class="link">heroesprofile.com/Twitch</a>,
            where viewers can find streamers using the extension. Live channels are shown first.
          </p>

          <div v-if="channel.listing_hidden" class="bg-darken border-l-4 border-red p-3 mb-4 text-sm">
            Your listing has been hidden by Heroes Profile. Write to zemill@heroesprofile.com to ask why.
          </div>

          <div v-if="channel.listing_opt_in && !channel.listing_terms_current" class="bg-darken border-l-4 border-yellow p-3 mb-4 text-sm">
            The streamer code of conduct has changed. Accept the new version to stay listed.
          </div>

          <label class="flex items-center gap-2 mb-3 text-sm">
            <input type="checkbox" v-model="listingOptIn" />
            List my channel on Heroes Profile
          </label>

          <label v-if="listingOptIn" class="flex items-start gap-2 mb-4 text-sm">
            <input type="checkbox" v-model="acceptTerms" class="mt-1" />
            <span>
              I have read and agree to the
              <a href="/Twitch/Guidelines" target="_blank" rel="noopener" class="link">streamer code of conduct</a>:
              no racism, sexism or misogyny, homophobia or transphobia, hate speech or harassment,
              and I follow Twitch's Community Guidelines. Heroes Profile may remove my listing at any time.
            </span>
          </label>

          <button
            @click="saveListing"
            :disabled="savingListing || (listingOptIn && !acceptTerms)"
            class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4 disabled:bg-gray-medium"
          >
            {{ savingListing ? 'Saving…' : 'Save' }}
          </button>
        </div>

        <!-- Step 6: Twitch -->
        <div class="bg-darken p-4">
          <h3 class="font-semibold mb-4">6. Turn It On in Twitch</h3>
          <ol class="text-sm list-decimal list-inside space-y-1">
            <li>In your Twitch Creator Dashboard, open <strong>Extensions</strong> and search for <strong>Heroes Profile</strong>.</li>
            <li>Install it and activate it as a <strong>Video Component</strong>.</li>
            <li>In the Heroes Profile Uploader, paste your uploader key and tick <strong>Twitch Extension</strong>.</li>
            <li>Start a game. Your viewers will see the lobby, heroes and talents as they are picked.</li>
          </ol>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiTwitchSettings',
  props: {
    initialchannel: Object,
    regions: {
      type: Object,
      default: () => ({}),
    },
    maxdelay: {
      type: Number,
      default: 900,
    },
    // Outcome of a Twitch or Battle.net connect redirect, flashed by the server.
    notice: String,
    linkerror: String,
  },
  data(){
    return {
      channel: this.initialchannel,
      error: this.linkerror,
      message: this.notice,
      region: this.initialchannel?.region ? String(this.initialchannel.region) : '1',
      newKey: null,
      copied: false,
      rotating: false,
      delay: this.initialchannel?.delay_seconds ?? 0,
      showStats: this.initialchannel?.show_stats ?? true,
      savingSettings: false,
      settingsSaved: false,
      listingOptIn: !!this.initialchannel?.listing_opt_in,
      acceptTerms: !!(this.initialchannel?.listing_opt_in && this.initialchannel?.listing_terms_current),
      savingListing: false,
      delayPresets: [
        { label: 'Off', value: 0 },
        { label: '30s', value: 30 },
        { label: '1m', value: 60 },
        { label: '2m', value: 120 },
        { label: '5m', value: 300 },
      ],
    }
  },
  computed: {
    entitlement(){
      return this.channel?.entitlement;
    },
    entitled(){
      return !!this.entitlement?.active;
    },
    accessBorder(){
      return this.entitled ? 'border-l-4 border-teal' : 'border-l-4 border-yellow';
    },
    accessText(){
      const e = this.entitlement;
      if(!e){
        return 'We could not check your access just now. Refresh the page to try again.';
      }
      switch(e.source){
        case 'paid':
          return `Included with your ${e.plan} plan.`;
        case 'comp':
          return `Granted free of charge until ${this.formatDate(e.expires_at)}.`;
        case 'trial':
          return `Free trial — ${this.daysLeft(e.expires_at)} left (ends ${this.formatDate(e.expires_at)}).`;
        case 'pending':
          return 'Your 30-day free trial starts the first time the uploader sends a live game. No card needed.';
        case 'suspended':
          return 'Switched off for this channel.';
        default:
          return this.channel.trial_started_at
            ? 'Your free trial has ended.'
            : 'Not active.';
      }
    },
    lastSeenText(){
      if(!this.channel.uploader_last_seen_at){
        return 'The uploader has not sent any games yet.';
      }
      return 'Last game received ' + new Date(this.channel.uploader_last_seen_at).toLocaleString() + '.';
    },
    delayValid(){
      return Number.isInteger(this.delay) && this.delay >= 0 && this.delay <= this.maxdelay;
    },
  },
  methods: {
    formatDate(iso){
      return iso ? new Date(iso).toLocaleDateString() : '';
    },
    daysLeft(iso){
      const days = Math.max(0, Math.ceil((new Date(iso) - new Date()) / 86400000));
      return days === 1 ? '1 day' : `${days} days`;
    },
    fail(error, fallback){
      this.error = error?.response?.data?.error || error?.response?.data?.message || fallback;
    },
    async rotateKey(){
      if(this.channel.has_key && !confirm('Replace your uploader key? The current one stops working immediately.')){
        return;
      }
      this.rotating = true;
      this.error = null;
      try {
        const response = await this.$axios.post('/api/v1/account/twitch/key');
        this.newKey = response.data.key;
        this.channel = response.data.channel;
        this.copied = false;
      } catch(error) {
        this.fail(error, 'Could not create a key. Please try again.');
      } finally {
        this.rotating = false;
      }
    },
    async copyKey(){
      try {
        await navigator.clipboard.writeText(this.newKey);
        this.copied = true;
      } catch(error) {
        this.copied = false;
      }
    },
    async saveSettings(){
      this.savingSettings = true;
      this.settingsSaved = false;
      this.error = null;
      try {
        const response = await this.$axios.post('/api/v1/account/twitch/settings', {
          delay_seconds: this.delay,
          show_stats: this.showStats,
        });
        this.channel = response.data.channel;
        this.settingsSaved = true;
      } catch(error) {
        this.fail(error, 'Could not save your settings.');
      } finally {
        this.savingSettings = false;
      }
    },
    async saveListing(){
      this.savingListing = true;
      this.error = null;
      try {
        const response = await this.$axios.post('/api/v1/account/twitch/listing', {
          listing_opt_in: this.listingOptIn,
          accept_terms: this.acceptTerms,
        });
        this.channel = response.data.channel;
        this.message = this.listingOptIn ? 'Your channel is listed on the streamer page.' : 'Your channel is no longer listed.';
      } catch(error) {
        this.fail(error, 'Could not save your listing preference.');
      } finally {
        this.savingListing = false;
      }
    },
    async unlink(){
      if(!confirm('Disconnect this Twitch channel? The extension stops showing your games and your uploader key stops working.')){
        return;
      }
      this.error = null;
      try {
        await this.$axios.post('/api/v1/account/twitch/unlink');
        this.channel = null;
        this.newKey = null;
        this.message = 'Twitch channel disconnected.';
      } catch(error) {
        this.fail(error, 'Could not disconnect the channel.');
      }
    },
  },
}
</script>
