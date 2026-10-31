<template>
  <api-admin-card title="Community Polls" storage-key="polls" tight>
    <p class="text-sm text-gray-medium mb-4">
      Polls live at /Poll/{slug}. Voting needs a Battle.net login. Once a poll has votes, its options
      can be reworded but not added or removed. Times are UTC.
    </p>

    <div v-if="error" class="bg-red p-3 mb-4 text-sm">{{ error }}</div>
    <div v-if="notice" class="bg-teal p-3 mb-4 text-sm">{{ notice }}</div>

    <div class="mb-4">
      <button type="button" class="btn bg-teal hover:bg-lteal" @click="startNew">New poll</button>
    </div>

    <p v-if="!loading && !polls.length" class="text-sm text-gray-medium">No polls yet.</p>

    <div v-else class="overflow-x-auto mb-6">
      <table class="min-w-0 w-full responsive-table text-sm">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left">Poll</th>
            <th class="py-2 px-3 text-left">Status</th>
            <th class="py-2 px-3 text-left">Votes</th>
            <th class="py-2 px-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="poll in polls" :key="poll.id">
            <td class="py-2 px-3">
              <a :href="'/Poll/' + poll.slug" target="_blank" rel="noopener" class="link">{{ poll.title }}</a>
              <div class="text-xs text-gray-medium">/Poll/{{ poll.slug }}</div>
            </td>
            <td class="py-2 px-3">
              <span v-if="!poll.is_open" class="text-gray-medium">Closed {{ poll.closes_at }}</span>
              <span v-else-if="poll.closes_at" class="text-lteal">Open until {{ poll.closes_at }}</span>
              <span v-else class="text-lteal">Open</span>
            </td>
            <td class="py-2 px-3">{{ poll.votes }}</td>
            <td class="py-2 px-3 whitespace-nowrap space-x-2">
              <button class="link" @click="open(poll.id, 'results')">Results</button>
              <button class="link" @click="open(poll.id, 'edit')">Edit</button>
              <button v-if="poll.is_open" class="link" @click="close(poll)">Close</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="form" class="bg-darken p-4 rounded mb-6">
      <h3 class="text-base mb-3">{{ form.id ? 'Edit poll' : 'New poll' }}</h3>

      <div class="grid gap-3 md:grid-cols-2 mb-3">
        <label class="text-sm">
          Title
          <input v-model="form.title" type="text" maxlength="255" class="text-black p-2 rounded-control w-full mt-1" />
        </label>
        <label class="text-sm">
          Slug (the URL: lowercase letters, numbers, dashes)
          <input v-model="form.slug" type="text" maxlength="100" class="text-black p-2 rounded-control w-full mt-1" />
        </label>
      </div>

      <label class="text-sm block mb-3">
        Description (shown above the options)
        <textarea v-model="form.description" rows="6" class="text-black p-2 rounded-control w-full mt-1"></textarea>
      </label>

      <div class="flex flex-wrap items-center gap-6 mb-4">
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.allow_multiple" type="checkbox" />
          Allow picking more than one option
        </label>
        <label class="text-sm">
          Closes (UTC, empty = open until closed by hand)
          <input v-model="form.closes_at" type="datetime-local" class="text-black p-2 rounded-control ml-2" />
        </label>
      </div>

      <h4 class="text-sm mb-2">Options</h4>
      <div v-for="(option, index) in form.options" :key="option.key" class="flex flex-wrap gap-2 mb-2">
        <input v-model="option.label" type="text" maxlength="100" placeholder="Label" class="text-black p-2 rounded-control w-full md:w-1/3" />
        <input v-model="option.description" type="text" maxlength="1000" placeholder="Description (optional)" class="text-black p-2 rounded-control flex-1 min-w-[200px]" />
        <button v-if="!form.has_votes" type="button" class="link text-sm" @click="form.options.splice(index, 1)">Remove</button>
      </div>
      <button v-if="!form.has_votes" type="button" class="link text-sm mb-4" @click="addOption">Add option</button>
      <p v-else class="text-xs text-gray-medium mb-4">This poll has votes, so options can only be reworded.</p>

      <div class="flex gap-2">
        <button type="button" class="btn bg-blue hover:bg-lblue" :disabled="saving" @click="save">
          {{ saving ? 'Saving' : 'Save' }}
        </button>
        <button type="button" class="link text-sm" @click="form = null">Cancel</button>
      </div>
    </div>

    <div v-if="detail" class="bg-darken p-4 rounded">
      <h3 class="text-base mb-1">{{ detail.poll.title }}</h3>
      <p class="text-sm text-gray-medium mb-4">{{ detail.results.total }} {{ detail.results.total === 1 ? 'vote' : 'votes' }}</p>

      <div v-for="option in detail.poll.options" :key="option.id" class="mb-3">
        <div class="text-sm mb-1">{{ option.label }}</div>
        <stat-bar :value="percent(option.id)" :displayText="`${percent(option.id)}% (${count(option.id)})`"></stat-bar>
      </div>

      <h4 class="text-sm mt-6 mb-2">Comments</h4>
      <p v-if="!detail.comments.length" class="text-sm text-gray-medium">No comments.</p>
      <div v-else class="overflow-x-auto">
        <table class="min-w-0 w-full responsive-table text-sm">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left">Battletag</th>
              <th class="py-2 px-3 text-left">Picked</th>
              <th class="py-2 px-3 text-left">Comment</th>
              <th class="py-2 px-3 text-left">Updated</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, index) in detail.comments" :key="index">
              <td class="py-2 px-3 whitespace-nowrap">{{ row.battletag }}</td>
              <td class="py-2 px-3">{{ row.picks.join(', ') }}</td>
              <td class="py-2 px-3 whitespace-pre-line">{{ row.comment }}</td>
              <td class="py-2 px-3 whitespace-nowrap">{{ row.updated_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </api-admin-card>
</template>

<script>
let optionKey = 0;

export default {
  name: 'ApiAdminPolls',
  data(){
    return {
      polls: [],
      form: null,
      detail: null,
      loading: false,
      saving: false,
      error: null,
      notice: null,
    }
  },
  mounted(){
    this.load();
  },
  methods: {
    blankOption(option = {}){
      return { key: ++optionKey, id: option.id ?? null, label: option.label ?? '', description: option.description ?? '' };
    },
    addOption(){
      this.form.options.push(this.blankOption());
    },
    count(optionId){
      return this.detail?.results?.counts?.[optionId] ?? 0;
    },
    percent(optionId){
      const total = this.detail?.results?.total;
      return total ? Math.round((this.count(optionId) / total) * 100) : 0;
    },
    async load(){
      this.loading = true;
      try {
        const response = await this.$axios.get('/api/v1/admin/polls');
        this.polls = response.data.polls;
      } catch(error) {
        this.error = 'Could not load polls.';
      } finally {
        this.loading = false;
      }
    },
    startNew(){
      this.detail = null;
      this.notice = null;
      this.form = {
        id: null,
        slug: '',
        title: '',
        description: '',
        allow_multiple: true,
        closes_at: '',
        has_votes: false,
        options: [this.blankOption(), this.blankOption()],
      };
    },
    async open(id, mode){
      this.error = null;
      this.notice = null;
      try {
        const response = await this.$axios.get(`/api/v1/admin/polls/${id}`);
        if(mode === 'edit'){
          const poll = response.data.poll;
          this.detail = null;
          this.form = { ...poll, description: poll.description ?? '', closes_at: poll.closes_at ?? '', options: poll.options.map(o => this.blankOption(o)) };
        } else {
          this.form = null;
          this.detail = response.data;
        }
      } catch(error) {
        this.error = 'Could not load that poll.';
      }
    },
    async save(){
      this.saving = true;
      this.error = null;
      this.notice = null;
      const body = {
        slug: this.form.slug,
        title: this.form.title,
        description: this.form.description || null,
        allow_multiple: this.form.allow_multiple,
        closes_at: this.form.closes_at || null,
        options: this.form.options.map(o => ({ id: o.id, label: o.label, description: o.description || null })),
      };
      try {
        await this.$axios.post(this.form.id ? `/api/v1/admin/polls/${this.form.id}` : '/api/v1/admin/polls', body);
        this.notice = 'Poll saved.';
        this.form = null;
        this.load();
      } catch(error) {
        const errors = error?.response?.data?.errors;
        this.error = errors ? Object.values(errors).flat()[0] : (error?.response?.data?.message || 'The poll was not saved.');
      } finally {
        this.saving = false;
      }
    },
    async close(poll){
      if(!confirm(`Close "${poll.title}" now? Voting stops and everyone can see the results.`)){
        return;
      }
      this.error = null;
      try {
        await this.$axios.post(`/api/v1/admin/polls/${poll.id}/close`);
        this.notice = 'Poll closed.';
        this.load();
      } catch(error) {
        this.error = 'The poll was not closed.';
      }
    },
  },
}
</script>
