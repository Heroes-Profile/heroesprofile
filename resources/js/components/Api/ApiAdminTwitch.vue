<template>
  <api-admin-card :title="$t('Twitch Extension Channels')" storage-key="twitch" tight>
    <p class="text-sm text-gray-medium mb-4">
      {{ $t('Hiding a listing only removes the channel from /Twitch. Suspending switches the extension off for the channel. Neither affects the API account or its billing.') }}
    </p>

    <form @submit.prevent="load" class="flex flex-wrap gap-2 mb-4">
      <input v-model="query" type="text" :placeholder="$t('Twitch name or BattleTag')" class="text-black p-2 rounded grow" />
      <button type="submit" class="btn bg-teal hover:bg-lteal">{{ $t('Search') }}</button>
    </form>

    <div v-if="error" class="bg-red p-3 mb-4 text-sm">{{ error }}</div>

    <p v-if="!loading && !channels.length" class="text-sm text-gray-medium">{{ $t('No channels.') }}</p>

    <div v-else class="overflow-x-auto">
      <table class="min-w-0 w-full text-sm">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left">{{ $t('Channel') }}</th>
            <th class="py-2 px-3 text-left">{{ $t('Account') }}</th>
            <th class="py-2 px-3 text-left">{{ $t('Access') }}</th>
            <th class="py-2 px-3 text-left">{{ $t('Last game') }}</th>
            <th class="py-2 px-3 text-left">{{ $t('Listing') }}</th>
            <th class="py-2 px-3 text-left">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="channel in channels" :key="channel.id">
            <td class="py-2 px-3">
              <a :href="'https://www.twitch.tv/' + channel.twitch_login" target="_blank" rel="noopener" class="inline-flex items-center link-row">{{ channel.twitch_login }}</a>
              <div class="text-xs text-gray-medium">{{ channel.battletag || $t('no player linked') }}</div>
            </td>
            <td class="py-2 px-3">{{ channel.email || '— unlinked —' }}</td>
            <td class="py-2 px-3">
              <span :class="channel.entitlement && channel.entitlement.active ? 'text-lteal' : 'text-yellow'">
                {{ accessLabel(channel) }}
              </span>
              <div v-if="channel.suspended" class="text-xs text-red">{{ $t('Suspended: :suspension_reason', { suspension_reason: channel.suspension_reason }) }}</div>
            </td>
            <td class="py-2 px-3">{{ channel.uploader_last_seen_at ? $formatDate(channel.uploader_last_seen_at, { dateStyle: 'medium', timeStyle: 'short' }) : '—' }}</td>
            <td class="py-2 px-3">
              <span v-if="channel.listing_hidden" class="text-red">{{ $t('Hidden: :listing_hidden_reason', { listing_hidden_reason: channel.listing_hidden_reason }) }}</span>
              <span v-else-if="channel.listing_opt_in">{{ $t('Listed') }}</span>
              <span v-else class="text-gray-medium">{{ $t('Not opted in') }}</span>
            </td>
            <td class="py-2 px-3 whitespace-nowrap space-x-2">
              <button class="link" @click="toggleListing(channel)">{{ channel.listing_hidden ? $t('Unhide') : $t('Hide') }}</button>
              <button class="link" @click="toggleSuspend(channel)">{{ channel.suspended ? $t('Unsuspend') : $t('Suspend') }}</button>
              <button class="link" @click="comp(channel)">{{ $t('Comp') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </api-admin-card>
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
        return this.$t('unknown');
      }
      if(e.source === 'paid'){
        return this.$t('Paid (:plan)', { plan: e.plan });
      }
      if(e.source === 'trial'){
        return this.$t('Trial to :date', { date: this.$formatDate(channel.trial_ends_at) });
      }
      if(e.source === 'comp'){
        return this.$t('Comp to :date', { date: this.$formatDate(channel.comped_until) });
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
        this.error = this.$t('Could not load channels.');
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
        this.error = error?.response?.data?.message || this.$t('That change was not saved.');
      }
    },
    toggleListing(channel){
      if(channel.listing_hidden){
        return this.post(channel, 'listing', { hidden: false });
      }
      const reason = prompt(this.$t('Why is this listing being hidden? The streamer is told it was hidden, not this text.'));
      if(reason){
        this.post(channel, 'listing', { hidden: true, reason });
      }
    },
    toggleSuspend(channel){
      if(channel.suspended){
        return this.post(channel, 'suspend', { suspended: false });
      }
      const reason = prompt(this.$t('Why is the extension being switched off for this channel? The streamer sees this.'));
      if(reason){
        this.post(channel, 'suspend', { suspended: true, reason });
      }
    },
    comp(channel){
      const until = prompt(this.$t('Free access until (YYYY-MM-DD). Leave empty to remove.'), channel.comped_until || '');
      if(until === null){
        return;
      }
      this.post(channel, 'comp', { comped_until: until || null });
    },
  },
}
</script>
