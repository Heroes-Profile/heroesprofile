<template>
  <!-- A section of /Api/Account; the connect flows return to #twitch. -->
  <div id="twitch" class="bg-lighten p-6 mb-8 scroll-mt-4">
    <h2 class="text-lg mb-2">{{ $t('Twitch Extension') }} <span class="ml-1 px-2 py-0.5 rounded bg-yellow text-xs font-semibold align-middle">{{ $t('BETA') }}</span></h2>
    <div class="bg-darken border-l-4 border-yellow p-3 mb-4 text-sm">
      <strong>{{ $t('Beta:') }}</strong> {{ $t('this tool is still in testing and you may encounter issues. Report problems at') }} <a href="/Contact" class="link">heroesprofile.com/Contact</a> {{ $t('or zemill@heroesprofile.com.') }}
    </div>
    <p class="text-sm text-gray-medium mb-4">
      {{ $t('Show your viewers the lobby, heroes, talents and player stats of the game you are playing, live on your Twitch stream. Included with any paid plan, with a free month to try it.') }}
    </p>
    <p class="text-sm text-gray-medium mb-4">
      {{ $t('The data comes from temporary files the game writes as you play, so it is not 100% real time: talent picks can lag behind what is actually happening in the game.') }}
    </p>

    <div>
      <div v-if="error" class="bg-red p-3 mb-4">{{ error }}</div>
      <div v-if="message" class="bg-teal p-3 mb-4">{{ message }}</div>

      <div v-if="channel && channel.suspended" class="bg-darken border-l-4 border-red p-4 mb-4">
        <p class="text-sm">
          <strong>{{ $t('The extension is switched off for this channel.') }}</strong>
          {{ $t(':suspension_reason Write to zemill@heroesprofile.com if you think this is a mistake.', { suspension_reason: channel.suspension_reason }) }}
        </p>
      </div>

      <!-- Step 1: Twitch -->
      <div class="bg-darken p-4 mb-4">
        <h3 class="font-semibold mb-4">{{ $t('1. Twitch Channel') }}</h3>

        <template v-if="channel">
          <p class="text-sm mb-4">
            {{ $t('Connected to') }} <strong>{{ channel.twitch_display_name || channel.twitch_login }}</strong>.
          </p>
          <div class="flex flex-wrap gap-2">
            <a href="/Api/Twitch/Link" class="link text-sm">{{ $t('Connect a different channel') }}</a>
            <span class="text-gray-medium">·</span>
            <button @click="unlink" class="link text-sm">{{ $t('Disconnect') }}</button>
          </div>
        </template>

        <template v-else>
          <p class="text-sm text-gray-medium mb-4">
            {{ $t('Sign in with the Twitch account you stream from. We only read your channel name — nothing is posted and no permissions are requested.') }}
          </p>
          <custom-button href="/Api/Twitch/Link" text="Connect Twitch" alt="Connect Twitch" class="inline-block"></custom-button>
        </template>
      </div>

      <template v-if="channel">
        <!-- Access -->
        <div class="bg-darken p-4 mb-4" :class="accessBorder">
          <h3 class="font-semibold mb-2">{{ $t('Access') }}</h3>
          <p class="text-sm">{{ accessText }}</p>
          <p v-if="!entitled" class="text-sm mt-3">
            {{ $t('Any paid API plan, or a linked Patreon pledge of $5 or more, includes the Twitch extension.') }}
            <a href="/Api/Account/Billing" class="link">{{ $t('Choose a plan') }}</a>
            {{ $t('or link Patreon in the Patreon section above.') }}
          </p>
        </div>

        <!-- Step 2: Battle.net -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">{{ $t('2. Your Battle.net Account') }}</h3>
          <p class="text-sm text-gray-medium mb-4">
            {{ $t('Tells us which player in the lobby is you, so viewers can see which side is your team. Without it, the teams are shown as Left Team and Right Team.') }}
          </p>

          <p v-if="channel.battletag" class="text-sm mb-4">
            {{ $t('Connected as') }} <strong>{{ channel.battletag }}</strong> ({{ channel.region_name }}).
          </p>

          <form action="/Api/Twitch/Battlenet/Link" method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex flex-col">
              <label class="text-sm mb-1" for="twitch-region">{{ $t('Region you play in') }}</label>
              <select id="twitch-region" name="region" v-model="region" class="text-black p-2 rounded">
                <option v-for="(name, id) in regions" :key="id" :value="id">{{ name }}</option>
              </select>
            </div>
            <button type="submit" class="btn bg-blue hover:bg-lblue">
              {{ channel.battletag ? $t('Reconnect Battle.net') : $t('Connect Battle.net') }}
            </button>
          </form>
        </div>

        <!-- Step 3: Uploader key -->
        <div v-if="newKey" class="bg-teal p-4 mb-4">
          <h3 class="font-semibold mb-2">{{ $t('Copy Your Uploader Key Now') }}</h3>
          <p class="text-sm mb-3">
            {{ $t('Paste it into the Heroes Profile Uploader under Settings → Twitch Extension, then tick') }}
            <strong>{{ $t('Twitch Extension') }}</strong> {{ $t('on the uploader\'s main window. This is the only time the key will be shown.') }}
          </p>
          <div class="flex flex-wrap gap-2 items-center">
            <code class="bg-darken p-2 break-all grow">{{ newKey }}</code>
            <custom-button @click="copyKey" :text="copied ? $t('Copied') : $t('Copy')" :alt="'Copy key'" :size="'small'" :ignoreclick="true"></custom-button>
          </div>
        </div>

        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">{{ $t('3. Uploader Key') }}</h3>
          <p class="text-sm text-gray-medium mb-4">
            {{ $t('The Heroes Profile Uploader reads your game as it happens and sends it here with this key. Anyone holding it can post to your extension, so treat it like a password.') }}
          </p>

          <p v-if="channel.has_key" class="text-sm mb-2">
            {{ $t('Current key ends in') }} <code class="text-lteal">{{ channel.key_last4 }}</code>.
          </p>
          <p class="text-sm mb-4" :class="channel.uploader_last_seen_at ? '' : 'text-gray-medium'">
            {{ lastSeenText }}
          </p>

          <div class="bg-lighten p-3 mb-4 text-sm">
            <p class="font-semibold mb-2">{{ $t('In the uploader') }}</p>
            <ol class="list-decimal list-inside space-y-1 text-gray-light">
              <li>{{ $t('Settings → Twitch Extension: paste the key, then') }} <strong>{{ $t('Check key') }}</strong>.</li>
              <li>
                {{ $t('Close settings and tick') }} <strong>{{ $t('Twitch Extension') }}</strong> {{ $t('on the main window. The key on its own does nothing — nothing is sent until that box is ticked.') }}
              </li>
              <li>{{ $t('Play a game. The uploader shows "Twitch extension updated" each time it sends.') }}</li>
            </ol>
          </div>

          <button
            @click="rotateKey"
            :disabled="rotating"
            class="btn bg-blue hover:bg-lblue disabled:bg-gray-medium"
          >
            {{ channel.has_key ? $t('Replace Key') : $t('Create Key') }}
          </button>
          <p v-if="channel.has_key" class="text-xs text-gray-medium mt-2">
            {{ $t('Replacing it stops the old key working immediately.') }}
          </p>
        </div>

        <!-- Step 4: Settings -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">{{ $t('4. Extension Settings') }}</h3>

          <h4 class="text-sm font-semibold mb-1">{{ $t('Stream-snipe delay') }}</h4>
          <p class="text-sm text-gray-medium mb-3">
            {{ $t('Viewers see lobby, hero and talent data this many seconds after it happens. Set it to match or exceed the delay on your stream. Your own config view on Twitch always shows it live.') }}
          </p>
          <p class="text-sm text-gray-medium mb-3">
            {{ $t('Twitch\'s own stream delay only delays the video, not the extension. With a 10 minute delay on Twitch and none here, viewers would see your picks 10 minutes before they happen on stream.') }}
          </p>
          <p class="text-sm text-gray-medium mb-3">
            {{ $t('Everything is held for the full delay, including for viewers who open the extension mid-game: with 10 minutes set, they see the game as it was 10 minutes ago. When one game ends and you queue again, viewers may still be watching the last one.') }}
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
              :aria-label="$t('Delay in seconds')"
            />
            <span class="text-sm">{{ $t('seconds (0 – :maxdelay)', { maxdelay: maxdelay }) }}</span>
          </div>

          <label class="flex items-center gap-2 mb-6 text-sm">
            <input type="checkbox" v-model="showStats" />
            {{ $t('Show win rate and account level for each player') }}
          </label>

          <button
            @click="saveSettings"
            :disabled="savingSettings || !delayValid"
            class="btn bg-teal hover:bg-lteal disabled:bg-gray-medium"
          >
            {{ savingSettings ? $t('Saving…') : $t('Save Settings') }}
          </button>
          <p v-if="settingsSaved" class="text-sm text-lteal mt-2">{{ $t('Saved. Applies from the next update.') }}</p>
        </div>

        <!-- Step 5: Public listing -->
        <div class="bg-darken p-4 mb-4">
          <h3 class="font-semibold mb-4">{{ $t('5. Heroes Profile Streamer Page') }}</h3>
          <p class="text-sm text-gray-medium mb-4">
            {{ $t('List your channel on') }} <a href="/Twitch" class="link">heroesprofile.com/Twitch</a>{{ $t(', where viewers can find streamers using the extension. Live channels are shown first. Your channel appears once you have played a game with the uploader running, and drops off after :listingdays days without one.', { listingdays: listingdays }) }}
          </p>

          <div v-if="listingPending" class="bg-darken border-l-4 border-yellow p-3 mb-4 text-sm">
            {{ $t('Your channel is not on the streamer page yet. Play a game with the uploader running and the Twitch Extension box ticked, and it appears within a few minutes.') }}
          </div>

          <div v-if="channel.listing_hidden" class="bg-darken border-l-4 border-red p-3 mb-4 text-sm">
            {{ $t('Your listing has been hidden by Heroes Profile. Write to zemill@heroesprofile.com to ask why.') }}
          </div>

          <div v-if="channel.listing_opt_in && !channel.listing_terms_current" class="bg-darken border-l-4 border-yellow p-3 mb-4 text-sm">
            {{ $t('The streamer code of conduct has changed. Accept the new version to stay listed.') }}
          </div>

          <label class="flex items-center gap-2 mb-3 text-sm">
            <input type="checkbox" v-model="listingOptIn" />
            {{ $t('List my channel on Heroes Profile') }}
          </label>

          <label v-if="listingOptIn" class="flex items-start gap-2 mb-4 text-sm">
            <input type="checkbox" v-model="acceptTerms" class="mt-1" />
            <span>
              {{ $t('I have read and agree to the') }}
              <a href="/Twitch/Guidelines" target="_blank" rel="noopener" class="link">{{ $t('streamer code of conduct') }}</a>{{ $t(': no racism, sexism or misogyny, homophobia or transphobia, hate speech or harassment, and I follow Twitch\'s Community Guidelines. Heroes Profile may remove my listing at any time.') }}
            </span>
          </label>

          <button
            @click="saveListing"
            :disabled="savingListing || (listingOptIn && !acceptTerms)"
            class="btn bg-teal hover:bg-lteal disabled:bg-gray-medium"
          >
            {{ savingListing ? $t('Saving…') : $t('Save') }}
          </button>
        </div>

        <!-- Step 6: Twitch -->
        <div class="bg-darken p-4">
          <h3 class="font-semibold mb-4">{{ $t('6. Turn It On in Twitch') }}</h3>
          <ol class="text-sm list-decimal list-inside space-y-1">
            <li>{{ $t('Open the') }} <a href="https://dashboard.twitch.tv/extensions/chu7yxvts476q2ys0pben0ij8iagsk-2.0.0" target="_blank" rel="noopener" class="link">{{ $t('Heroes Profile extension on Twitch') }}</a>{{ $t(', or in your Twitch Creator Dashboard open') }} <strong>{{ $t('Extensions') }}</strong> {{ $t('and search for') }} <strong>Heroes Profile</strong>.</li>
            <li>{{ $t('Install it and activate it as a') }} <strong>{{ $t('Video Component') }}</strong>.</li>
            <li>{{ $t('In the Heroes Profile Uploader, paste your uploader key and tick') }} <strong>{{ $t('Twitch Extension') }}</strong>.</li>
            <li>{{ $t('Start a game. Your viewers will see the lobby, heroes and talents as they are picked.') }}</li>
          </ol>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { formatDate as formatLocalDate } from '../../utils/formatGameDate';
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
    // Days of uploader silence after which a listed channel drops off the page.
    listingdays: {
      type: Number,
      default: 30,
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
        return this.$t('We could not check your access just now. Refresh the page to try again.');
      }
      switch(e.source){
        case 'paid':
          return this.$t('Included with your :plan plan.', { plan: e.plan });
        case 'comp':
          return this.$t('Granted free of charge until :date.', { date: this.formatDate(e.expires_at) });
        case 'trial':
          return this.$t('Free trial — :days left (ends :date).', { days: this.daysLeft(e.expires_at), date: this.formatDate(e.expires_at) });
        case 'pending':
          return this.$t('Your 30-day free trial starts the first time the uploader sends a live game. No card needed.');
        case 'suspended':
          return this.$t('Switched off for this channel.');
        default:
          return this.channel.trial_started_at
            ? this.$t('Your free trial has ended.')
            : this.$t('Not active.');
      }
    },
    // Listed, but with nothing played inside the window the directory looks at.
    listingPending(){
      if(!this.channel || !this.channel.listing_opt_in){
        return false;
      }
      if(!this.channel.uploader_last_seen_at){
        return true;
      }
      const cutoff = Date.now() - this.listingdays * 86400000;
      return new Date(this.channel.uploader_last_seen_at).getTime() < cutoff;
    },
    lastSeenText(){
      if(!this.channel.uploader_last_seen_at){
        return this.$t('The uploader has not sent any games yet.');
      }
      return this.$t('Last game received :time.', { time: formatLocalDate(this.channel.uploader_last_seen_at, { dateStyle: 'medium', timeStyle: 'short' }) });
    },
    delayValid(){
      return Number.isInteger(this.delay) && this.delay >= 0 && this.delay <= this.maxdelay;
    },
  },
  methods: {
    formatDate(iso){
      return iso ? formatLocalDate(iso) : '';
    },
    daysLeft(iso){
      const days = Math.max(0, Math.ceil((new Date(iso) - new Date()) / 86400000));
      return days === 1 ? this.$t('1 day') : this.$t(':days days', { days });
    },
    fail(error, fallback){
      this.error = error?.response?.data?.error || error?.response?.data?.message || fallback;
    },
    async rotateKey(){
      if(this.channel.has_key && !confirm(this.$t('Replace your uploader key? The current one stops working immediately.'))){
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
        this.fail(error, this.$t('Could not create a key. Please try again.'));
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
        this.fail(error, this.$t('Could not save your settings.'));
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
        this.message = this.listingOptIn ? this.$t('Your channel is listed on the streamer page.') : this.$t('Your channel is no longer listed.');
      } catch(error) {
        this.fail(error, this.$t('Could not save your listing preference.'));
      } finally {
        this.savingListing = false;
      }
    },
    async unlink(){
      if(!confirm(this.$t('Disconnect this Twitch channel? The extension stops showing your games and your uploader key stops working.'))){
        return;
      }
      this.error = null;
      try {
        await this.$axios.post('/api/v1/account/twitch/unlink');
        this.channel = null;
        this.newKey = null;
        this.message = this.$t('Twitch channel disconnected.');
      } catch(error) {
        this.fail(error, this.$t('Could not disconnect the channel.'));
      }
    },
  },
}
</script>
