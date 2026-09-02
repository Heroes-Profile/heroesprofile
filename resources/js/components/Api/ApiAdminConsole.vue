<template>
  <div>
    <page-heading :infoText1="infoText" heading="Admin"></page-heading>

    <div class="mx-auto max-w-[1000px] p-4">
      <div v-if="error" class="bg-red p-3 mb-4">{{ error }}</div>
      <div v-if="notice" class="bg-teal p-3 mb-4">{{ notice }}</div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">At a Glance</h2>

        <div v-if="metrics" class="flex flex-wrap gap-6 text-sm">
          <div>
            <div class="text-2xl">{{ metrics.accounts }}</div>
            <div class="text-gray-medium">Accounts</div>
          </div>
          <div>
            <div class="text-2xl">{{ metrics.migrated }}</div>
            <div class="text-gray-medium">Migrated</div>
          </div>
          <div>
            <div class="text-2xl">{{ metrics.active_keys }}</div>
            <div class="text-gray-medium">Active keys</div>
          </div>
          <div>
            <div class="text-2xl">{{ metrics.active_subscribers }}</div>
            <div class="text-gray-medium">Active subscribers</div>
          </div>
          <div>
            <div class="text-2xl">${{ metrics.mrr }}</div>
            <div class="text-gray-medium">Monthly revenue</div>
          </div>
          <div v-for="(total, status) in metrics.subscriptions_by_status" :key="status">
            <div class="text-2xl">{{ total }}</div>
            <div class="text-gray-medium capitalize">{{ status.replace('_', ' ') }}</div>
          </div>
        </div>

        <p v-else class="text-sm text-gray-medium">Loading.</p>
      </div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">Find an Account</h2>

        <div class="flex flex-wrap gap-2 mb-4">
          <input
            v-model="term"
            @keyup.enter="search"
            type="text"
            placeholder="Email or name"
            class="flex-1 min-w-[200px] p-2 bg-darken"
          />
          <custom-button @click="search" :text="'Search'" :alt="'Search accounts'" :size="'small'" :ignoreclick="true"></custom-button>
        </div>

        <p v-if="searched && !results.length" class="text-sm text-gray-medium">No accounts matched.</p>

        <table v-if="results.length" class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Email</th>
              <th class="py-2 px-3 text-left text-sm">Name</th>
              <th class="py-2 px-3 text-left text-sm">Data</th>
              <th class="py-2 px-3 text-left text-sm"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in results" :key="row.id">
              <td class="py-2 px-3">{{ row.email }}</td>
              <td class="py-2 px-3">{{ row.name }}</td>
              <td class="py-2 px-3">{{ row.migrated ? 'Live' : 'Fixtures' }}</td>
              <td class="py-2 px-3">
                <a class="link" href="#" @click.prevent="load(row.id)">Open</a>
              </td>
            </tr>
          </tbody>
        </table>

        <p v-if="truncated" class="text-sm text-gray-medium mt-3">
          Showing the first {{ results.length }} matches. Narrow the search to see the rest.
        </p>
      </div>

      <template v-if="detail">
        <div class="bg-lighten p-6 mb-8">
          <h2 class="text-lg mb-1">{{ detail.account.email }}</h2>
          <p class="text-sm text-gray-medium mb-4">{{ detail.account.name }} &middot; id {{ detail.account.id }}</p>

          <div v-if="detail.subscription_issue" class="border-l-4 border-red p-3 mb-4">
            <p class="text-sm"><strong>Key is being refused.</strong> {{ detail.subscription_issue }}</p>
          </div>

          <div v-if="!detail.account.admin" class="mb-4">
            <custom-button @click="impersonate" :text="'View as this user'" :alt="'View as this user'" :size="'small'" :ignoreclick="true"></custom-button>
            <p class="text-xs text-gray-medium mt-2">
              Signs you in as them. Anything you do then happens on their account.
            </p>
          </div>

          <table class="min-w-0 w-full responsive-table">
            <tbody>
              <tr>
                <td class="py-2 px-3 text-sm">Subscription</td>
                <td class="py-2 px-3">
                  <template v-if="detail.subscription">
                    {{ detail.subscription.plan_name }} &middot; {{ detail.subscription.status }}
                    <span v-if="detail.subscription.ends_at"> &middot; ends {{ detail.subscription.ends_at }}</span>
                  </template>
                  <template v-else>None</template>
                </td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Comped tiers</td>
                <td class="py-2 px-3">
                  <template v-if="detail.granted.length">{{ detail.granted.map(p => p.name).join(', ') }}</template>
                  <template v-else>None</template>
                </td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Project</td>
                <td class="py-2 px-3">
                  <!-- Linked only when it already starts with http(s). Whatever else
                       they typed is shown as text: it is stored verbatim, and an
                       href is not the place to find out what a stranger put in it. -->
                  <a
                    v-if="linkableWebsite"
                    class="link"
                    :href="linkableWebsite"
                    target="_blank"
                    rel="noopener noreferrer"
                  >{{ detail.account.website }}</a>
                  <template v-else-if="detail.account.website">{{ detail.account.website }}</template>
                  <template v-else>Not given</template>
                </td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Data source</td>
                <td class="py-2 px-3">{{ detail.account.receives_test_data ? 'Fixtures' : 'Live' }}</td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Migrated</td>
                <td class="py-2 px-3">{{ detail.account.migrated ? 'Yes' : 'No' }}</td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Email verified</td>
                <td class="py-2 px-3">{{ detail.account.email_verified_at || 'No' }}</td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Terms accepted</td>
                <td class="py-2 px-3">
                  {{ detail.account.terms_accepted_at || 'No' }}
                  <span v-if="detail.account.terms_version_accepted"> (v{{ detail.account.terms_version_accepted }})</span>
                </td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Active keys</td>
                <td class="py-2 px-3">{{ detail.keys.length }}</td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Stripe customer</td>
                <td class="py-2 px-3">{{ detail.account.stripe_id || 'None' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-lighten p-6 mb-8">
          <h2 class="text-lg mb-1">Standing</h2>
          <p class="text-sm text-gray-medium mb-4">
            Warn first. A suspension with nothing on record behind it is our word against theirs.
          </p>

          <div v-if="detail.enforcement.suspended" class="border-l-4 border-red p-3 mb-4">
            <p class="text-sm mb-1">
              <strong>{{ detail.enforcement.terminated ? 'Closed' : 'Suspended' }}</strong>
              since {{ detail.enforcement.since }}.
            </p>
            <p class="text-sm text-gray-medium">{{ detail.enforcement.reason }}</p>
          </div>

          <div v-else-if="detail.enforcement.open_warning" class="border-l-4 border-yellow p-3 mb-4">
            <p class="text-sm mb-1">
              <strong>Warned</strong> {{ detail.enforcement.open_warning.sent_at }} &middot; not read yet.
              <span v-if="detail.enforcement.open_warning.respond_by">
                Asked to fix by {{ detail.enforcement.open_warning.respond_by }}.
              </span>
              <span v-if="detail.enforcement.open_warning.overdue" class="text-yellow">Overdue.</span>
            </p>
            <p class="text-sm text-gray-medium">{{ detail.enforcement.open_warning.reason }}</p>
          </div>

          <p v-else class="text-sm mb-4">In good standing.</p>

          <label class="block text-sm mb-1">What they are told</label>
          <textarea
            v-model="actionReason"
            rows="3"
            placeholder="Attribution on your overlay is in the About panel. Section 4 asks for it on the same screen as the data."
            class="w-full p-2 bg-darken mb-3"
          ></textarea>

          <label class="block text-sm mb-1">
            Internal notes <span class="text-gray-medium">— never shown to them</span>
          </label>
          <textarea
            v-model="actionNotes"
            rows="2"
            placeholder="Where you saw it, call volumes, what was said and when."
            class="w-full p-2 bg-darken mb-3"
          ></textarea>

          <label class="block text-sm mb-1">
            Fix by <span class="text-gray-medium">— warnings only, optional</span>
          </label>
          <input v-model="actionRespondBy" type="date" class="p-2 bg-darken mb-4 block" />

          <div class="flex flex-wrap gap-3">
            <button
              @click="act('warn')"
              :disabled="busy"
              class="transition-colors text-white rounded bg-yellow hover:bg-lyellow py-2 px-4 disabled:bg-gray-medium"
            >
              Warn
            </button>
            <button
              v-if="!detail.enforcement.terminated"
              @click="act('suspend')"
              :disabled="busy"
              class="transition-colors text-white rounded bg-red hover:bg-lred py-2 px-4 disabled:bg-gray-medium"
            >
              Suspend
            </button>
            <button
              @click="act('terminate')"
              :disabled="busy"
              class="transition-colors text-white rounded bg-red hover:bg-lred py-2 px-4 disabled:bg-gray-medium"
            >
              Close account
            </button>
            <button
              v-if="detail.enforcement.suspended"
              @click="act('reinstate')"
              :disabled="busy"
              class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4 disabled:bg-gray-medium"
            >
              Reinstate
            </button>
          </div>

          <p class="text-xs text-gray-medium mt-3">
            A warning changes nothing about their access. Suspending stops their keys on both
            sites and leaves billing running. Closing the account also cancels their
            subscription, and that cannot be restarted for them — they would have to subscribe
            again themselves.
          </p>

          <h3 class="text-base mt-6 mb-2">History</h3>

          <p v-if="!detail.history.length" class="text-sm text-gray-medium">Nothing on record.</p>

          <table v-else class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th class="py-2 px-3 text-left text-sm">When</th>
                <th class="py-2 px-3 text-left text-sm">Action</th>
                <th class="py-2 px-3 text-left text-sm">Told them</th>
                <th class="py-2 px-3 text-left text-sm">Notes</th>
                <th class="py-2 px-3 text-left text-sm">By</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in detail.history" :key="row.id">
                <td class="py-2 px-3">{{ row.at }}</td>
                <td class="py-2 px-3">
                  {{ row.action }}
                  <span v-if="row.acknowledged_at" class="text-gray-medium">— read {{ row.acknowledged_at }}</span>
                </td>
                <td class="py-2 px-3">{{ row.reason || '—' }}</td>
                <td class="py-2 px-3">{{ row.notes || '—' }}</td>
                <td class="py-2 px-3">{{ row.by || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-lighten p-6 mb-8">
          <h2 class="text-lg mb-1">Comped Access</h2>
          <p class="text-sm text-gray-medium mb-4">
            Granted by hand, per partner or esports org. Takes effect on the next API call.
          </p>

          <div class="flex flex-wrap gap-x-6 gap-y-3">
            <label v-for="(value, flag) in detail.flags" :key="flag" class="flex items-center gap-2 text-sm">
              <input
                type="checkbox"
                :checked="value"
                :disabled="busy"
                @change="setFlag(flag, $event.target.checked)"
              />
              {{ flag }}
            </label>
          </div>
        </div>

        <div class="bg-lighten p-6 mb-8">
          <h2 class="text-lg mb-4">Endpoint Limits</h2>
          <api-usage-table :usage="detail.usage"></api-usage-table>
        </div>
      </template>

      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-4">Recent Subscription Activity</h2>

        <p v-if="!activity.length" class="text-sm text-gray-medium">Nothing yet.</p>

        <table v-else class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Email</th>
              <th class="py-2 px-3 text-left text-sm">Status</th>
              <th class="py-2 px-3 text-left text-sm">Started</th>
              <th class="py-2 px-3 text-left text-sm">Last change</th>
              <th class="py-2 px-3 text-left text-sm">Ends</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in activity" :key="row.id">
              <td class="py-2 px-3">{{ row.email }}</td>
              <td class="py-2 px-3">{{ row.status }}</td>
              <td class="py-2 px-3">{{ row.started_at }}</td>
              <td class="py-2 px-3">{{ row.changed_at }}</td>
              <td class="py-2 px-3">{{ row.ends_at || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiAdminConsole',
  data(){
    return {
      infoText: "Look up an account, grant comped access, and see what subscriptions have moved recently.",
      term: '',
      results: [],
      truncated: false,
      searched: false,
      detail: null,
      activity: [],
      metrics: null,
      busy: false,
      error: null,
      notice: null,
      actionReason: '',
      actionNotes: '',
      actionRespondBy: '',
    }
  },
  computed: {
    linkableWebsite(){
      const website = this.detail?.account?.website;

      return /^https?:\/\//i.test(website || '') ? website : null;
    },
  },
  mounted(){
    this.loadMetrics();
    this.loadActivity();
  },
  methods: {
    async loadMetrics(){
      try {
        const response = await this.$axios.get('/api/v1/admin/metrics');
        this.metrics = response.data;
      } catch (error) {
        // Counts are context, not the job. A failure here should not hide search.
      }
    },
    async loadActivity(){
      try {
        const response = await this.$axios.get('/api/v1/admin/activity');
        this.activity = response.data.activity;
      } catch (error) {
        // As above.
      }
    },
    async search(){
      if(this.term.trim().length < 2){
        this.error = 'Enter at least two characters.';
        return;
      }

      this.error = null;

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/search', { term: this.term.trim() });
        this.results = response.data.accounts;
        this.truncated = response.data.truncated;
        this.searched = true;
      } catch (error) {
        this.error = this.messageFrom(error);
      }
    },
    async load(id){
      this.error = null;
      this.notice = null;
      // Text typed against the last account must not follow you to the next one.
      this.clearAction();

      try {
        const response = await this.$axios.get('/api/v1/admin/accounts/' + id);
        this.detail = response.data;
      } catch (error) {
        this.error = this.messageFrom(error);
      }
    },
    async impersonate(){
      this.error = null;

      try {
        const response = await this.$axios.post('/Api/Admin/Impersonate/' + this.detail.account.id);
        window.location = response.data.redirect;
      } catch (error) {
        this.error = this.messageFrom(error);
      }
    },
    async setFlag(flag, value){
      this.busy = true;
      this.error = null;
      this.notice = null;

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/' + this.detail.account.id + '/flag', {
          flag: flag,
          value: value,
        });

        this.detail.flags = response.data.flags;
        this.notice = flag + (value ? ' granted.' : ' removed.');

        // The granted-tier list is derived from the flags, so it is now stale.
        await this.load(this.detail.account.id);
      } catch (error) {
        this.error = this.messageFrom(error);
        // Put the checkbox back where the server says it is.
        await this.load(this.detail.account.id);
      } finally {
        this.busy = false;
      }
    },
    // Every rung takes effect the moment it is pressed, and closing an account
    // cancels a subscription nobody here can restart — so each one asks first.
    confirmationFor(action){
      if(action === 'warn'){
        return 'Send this warning? They get an email and a banner. Nothing about their access changes.';
      }

      if(action === 'suspend'){
        return 'Suspend this account? Their keys stop working on both sites immediately. Billing keeps running.';
      }

      if(action === 'terminate'){
        return 'Close this account? Their keys stop working and their subscription is cancelled immediately. You cannot restart it for them.';
      }

      return 'Reinstate this account? Their existing keys start working again.';
    },
    async act(action){
      const reason = this.actionReason.trim();

      if(action !== 'reinstate' && !reason){
        this.error = 'Say what they are being told — the same words go in the email and on their account page.';
        return;
      }

      if(!confirm(this.confirmationFor(action))){
        return;
      }

      this.busy = true;
      this.error = null;
      this.notice = null;

      const payload = { notes: this.actionNotes.trim() || null };

      if(action !== 'reinstate'){
        payload.reason = reason;
      }

      if(action === 'warn' && this.actionRespondBy){
        payload.respond_by = this.actionRespondBy;
      }

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/' + this.detail.account.id + '/' + action, payload);

        this.detail.enforcement = response.data.enforcement;
        this.detail.history = response.data.history;
        this.clearAction();

        this.notice = {
          warn: 'Warning sent.',
          suspend: 'Account suspended and told why.',
          terminate: 'Account closed.',
          reinstate: 'Account reinstated.',
        }[action];
      } catch (error) {
        this.error = this.messageFrom(error);
      } finally {
        this.busy = false;
      }
    },
    clearAction(){
      this.actionReason = '';
      this.actionNotes = '';
      this.actionRespondBy = '';
    },
    messageFrom(error){
      return (error.response && error.response.data && error.response.data.error)
        ? error.response.data.error
        : 'Something went wrong.';
    },
  },
}
</script>
