<template>
  <div class="bg-lighten p-6">
    <h2 class="text-lg mb-1">Twitch Extension Channels</h2>
    <p class="text-sm text-gray-medium mb-4">
      Hiding a listing only removes the channel from /Twitch. Suspending switches the extension off
      for the channel. Neither affects the API account or its billing.
    </p>

    <form @submit.prevent="load" class="flex flex-wrap gap-2 mb-4">
      <input v-model="query" type="text" placeholder="Twitch name or battletag" class="text-black p-2 rounded flex-grow" />
      <button type="submit" class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4">Search</button>
    </form>

    <div v-if="error" class="bg-red p-3 mb-4 text-sm">{{ error }}</div>

    <p v-if="!loading && !channels.length" class="text-sm text-gray-medium">No channels.</p>

    <div v-else class="overflow-x-auto">
      <table class="min-w-0 w-full responsive-table text-sm">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left">Channel</th>
            <th class="py-2 px-3 text-left">Account</th>
            <th class="py-2 px-3 text-left">Access</th>
            <th class="py-2 px-3 text-left">Last game</th>
            <th class="py-2 px-3 text-left">Listing</th>
            <th class="py-2 px-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="channel in channels" :key="channel.id">
            <td class="py-2 px-3">
              <a :href="'https://www.twitch.tv/' + channel.twitch_login" target="_blank" rel="noopener" class="link">{{ channel.twitch_login }}</a>
              <div class="text-xs text-gray-medium">{{ channel.battletag || 'no player linked' }}</div>
            </td>
            <td class="py-2 px-3">{{ channel.email || '— unlinked —' }}</td>
            <td class="py-2 px-3">
              <span :class="channel.entitlement && channel.entitlement.active ? 'text-lteal' : 'text-yellow'">
                {{ accessLabel(channel) }}
              </span>
              <div v-if="channel.suspended" class="text-xs text-red">Suspended: {{ channel.suspension_reason }}</div>
            </td>
            <td class="py-2 px-3">{{ channel.uploader_last_seen_at || '—' }}</td>
            <td class="py-2 px-3">
              <span v-if="channel.listing_hidden" class="text-red">Hidden: {{ channel.listing_hidden_reason }}</span>
              <span v-else-if="channel.listing_opt_in">Listed</span>
              <span v-else class="text-gray-medium">Not opted in</span>
            </td>
            <td class="py-2 px-3 whitespace-nowrap space-x-2">
              <button class="link" @click="toggleListing(channel)">{{ channel.listing_hidden ? 'Unhide' : 'Hide' }}</button>
              <button class="link" @click="toggleSuspend(channel)">{{ channel.suspended ? 'Unsuspend' : 'Suspend' }}</button>
              <button class="link" @click="comp(channel)">Comp</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiAdminTwitch',
  data(){
    return {
      channels: [],
      query: '',
      loading: false,
      error: null,
    }
  },
  mounted(){
    this.load();
  },
  methods: {
    accessLabel(channel){
      const e = channel.entitlement;
      if(!e){
        return 'unknown';
      }
      if(e.source === 'paid'){
        return 'Paid (' + e.plan + ')';
      }
      if(e.source === 'trial'){
        return 'Trial to ' + channel.trial_ends_at;
      }
      if(e.source === 'comp'){
        return 'Comp to ' + channel.comped_until;
      }
      return e.source;
    },
    replace(updated){
      const index = this.channels.findIndex(c => c.id === updated.id);
      if(index !== -1){
        this.channels.splice(index, 1, updated);
      }
    },
    async load(){
      this.loading = true;
      this.error = null;
      try {
        const response = await this.$axios.get('/api/v1/admin/twitch', { params: { q: this.query } });
        this.channels = response.data.channels;
      } catch(error) {
        this.error = 'Could not load channels.';
      } finally {
        this.loading = false;
      }
    },
    async post(channel, action, body){
      this.error = null;
      try {
        const response = await this.$axios.post(`/api/v1/admin/twitch/${channel.id}/${action}`, body);
        this.replace(response.data.channel);
      } catch(error) {
        this.error = error?.response?.data?.message || 'That change was not saved.';
      }
    },
    toggleListing(channel){
      if(channel.listing_hidden){
        return this.post(channel, 'listing', { hidden: false });
      }
      const reason = prompt('Why is this listing being hidden? The streamer is told it was hidden, not this text.');
      if(reason){
        this.post(channel, 'listing', { hidden: true, reason });
      }
    },
    toggleSuspend(channel){
      if(channel.suspended){
        return this.post(channel, 'suspend', { suspended: false });
      }
      const reason = prompt('Why is the extension being switched off for this channel? The streamer sees this.');
      if(reason){
        this.post(channel, 'suspend', { suspended: true, reason });
      }
    },
    comp(channel){
      const until = prompt('Free access until (YYYY-MM-DD). Leave empty to remove.', channel.comped_until || '');
      if(until === null){
        return;
      }
      this.post(channel, 'comp', { comped_until: until || null });
    },
  },
}
</script>
