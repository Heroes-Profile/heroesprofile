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
            placeholder="Email, name or ID"
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

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-1">Usage This Window</h2>
        <p class="text-sm text-gray-medium mb-4">
          Each account's current 7-day window, summed across endpoints. Cost is an upper-bound estimate.
        </p>

        <p v-if="!usage.length" class="text-sm text-gray-medium">No usage in the current window.</p>

        <div v-else class="overflow-x-auto">
          <table class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th v-for="column in usageColumns" :key="column.key" @click="sortUsage(column.key)" class="py-2 px-3 text-left text-sm">
                  {{ column.label }}
                  <span v-if="usageSortKey === column.key">{{ usageSortDir === 'asc' ? '▲' : '▼' }}</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in sortedUsage"
                :key="row.id"
                @click="load(row.id, true)"
                class="cursor-pointer"
                :class="{ '!bg-teal !text-white': selectedUsageId === row.id }"
              >
                <td class="py-2 px-3">
                  {{ row.email || 'id ' + row.id }}
                  <img
                    v-if="loadingId === row.id"
                    src="/images/logo/heroesprofilelogo.png"
                    alt="Loading"
                    class="inline-block w-5 h-5 ml-2 align-middle animate-spin"
                  />
                </td>
                <td class="py-2 px-3">{{ formatNumber(row.calls) }}</td>
                <td class="py-2 px-3">{{ formatBytes(row.egress_bytes) }}</td>
                <td class="py-2 px-3">{{ formatDuration(row.compute_ms) }}</td>
                <td class="py-2 px-3">{{ formatCost(row.cost_usd) }}</td>
                <td class="py-2 px-3" :class="{ 'text-gray-medium': !row.approved_at }">{{ row.approved_at || 'Never' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <template v-if="detail">
        <div ref="detail" class="bg-lighten p-6 mb-8">
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
                  <template v-if="detail.account.project_name">
                    <strong>{{ detail.account.project_name }}</strong>
                    <span class="text-sm text-gray-medium">— updated {{ detail.account.project_updated_at }}</span>
                    <p class="text-sm whitespace-pre-line mt-1">{{ detail.account.project_description }}</p>
                  </template>
                  <template v-else>Not described</template>
                </td>
              </tr>
              <tr>
                <td class="py-2 px-3 text-sm">Website</td>
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
          <h2 class="text-lg mb-1">Approval</h2>
          <p class="text-sm text-gray-medium mb-4">
            Records your approval of the project as described above, with your notes. Nothing is
            sent and their access is unchanged — grant access with the Comped Access checkboxes.
          </p>

          <p class="text-sm mb-4">
            <template v-if="detail.last_approval">
              Last approved {{ detail.last_approval.at }}<template v-if="detail.last_approval.by"> by {{ detail.last_approval.by }}</template>.
            </template>
            <span v-else class="text-gray-medium">Never approved.</span>
          </p>

          <label class="block text-sm mb-1">
            Approval notes <span class="text-gray-medium">— never shown to them</span>
          </label>
          <textarea
            v-model="approveNotes"
            rows="3"
            maxlength="2000"
            placeholder="What you checked and what you agreed to."
            class="w-full p-2 bg-darken mb-2"
          ></textarea>
          <button
            @click="approve"
            :disabled="busy"
            class="transition-colors text-white rounded bg-blue hover:bg-lblue py-1 px-3 text-sm mb-4 disabled:bg-gray-medium"
          >
            Approve
          </button>

          <h3 class="text-base mt-2 mb-2">Approval History</h3>
          <p class="text-sm text-gray-medium mb-3">
            Approvals and Comped Access changes, each with the project description as it read at the time.
          </p>

          <p v-if="!detail.approvals.length" class="text-sm text-gray-medium">Nothing on record.</p>

          <table v-else class="min-w-0 w-full responsive-table">
            <thead>
              <tr>
                <th class="py-2 px-3 text-left text-sm">When</th>
                <th class="py-2 px-3 text-left text-sm">What</th>
                <th class="py-2 px-3 text-left text-sm">Project at the time</th>
                <th class="py-2 px-3 text-left text-sm">Notes</th>
                <th class="py-2 px-3 text-left text-sm">By</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in detail.approvals" :key="row.id">
                <td class="py-2 px-3">{{ row.at }}</td>
                <td class="py-2 px-3">
                  <strong v-if="row.type === 'approval'">Approved</strong>
                  <template v-else>
                    {{ row.flag }}
                    <span class="text-gray-medium">— {{ row.granted ? 'granted' : 'removed' }}</span>
                  </template>
                </td>
                <td class="py-2 px-3">
                  <template v-if="row.project_name || row.project_description">
                    <strong v-if="row.project_name">{{ row.project_name }}</strong>
                    <div class="whitespace-pre-line">{{ row.project_description }}</div>
                    <div v-if="row.project_updated_at" class="text-sm text-gray-medium">Description last edited {{ row.project_updated_at }}</div>
                  </template>
                  <span v-else class="text-gray-medium">No description</span>
                </td>
                <td class="py-2 px-3">
                  <template v-if="editingApprovalId === row.id">
                    <textarea v-model="approvalNotes" rows="3" maxlength="2000" class="w-full p-2 bg-darken"></textarea>
                    <div class="flex gap-2 mt-1">
                      <button type="button" class="underline text-sm" :disabled="busy" @click="saveApprovalNotes(row.id)">Save</button>
                      <button type="button" class="underline text-sm" :disabled="busy" @click="editingApprovalId = null">Cancel</button>
                    </div>
                  </template>
                  <template v-else>
                    <div class="whitespace-pre-line">{{ row.notes || '—' }}</div>
                    <button type="button" class="underline text-sm" :disabled="busy" @click="editApprovalNotes(row)">Edit</button>
                  </template>
                </td>
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
          <h2 class="text-lg mb-1">Standing</h2>
          <p class="text-sm text-gray-medium mb-4">
            Warn first. A suspension with nothing on record behind it is our word against theirs.
          </p>

          <div v-if="detail.enforcement.suspended" class="border-l-4 border-red p-3 mb-4">
            <p class="text-sm mb-1">
              <strong>{{ detail.enforcement.terminated ? 'Closed' : 'Suspended' }}</strong>
              since {{ detail.enforcement.since }}.
            </p>
            <div class="text-sm text-gray-medium standing-text" v-html="detail.enforcement.reason"></div>
          </div>

          <div v-else-if="detail.enforcement.open_warning" class="border-l-4 border-yellow p-3 mb-4">
            <p class="text-sm mb-1">
              <strong>Warned</strong> {{ detail.enforcement.open_warning.sent_at }} &middot; not read yet.
              <span v-if="detail.enforcement.open_warning.respond_by">
                Asked to fix by {{ detail.enforcement.open_warning.respond_by }}.
              </span>
              <span v-if="detail.enforcement.open_warning.overdue" class="text-yellow">Overdue.</span>
            </p>
            <div class="text-sm text-gray-medium standing-text" v-html="detail.enforcement.open_warning.reason"></div>
          </div>

          <p v-else class="text-sm mb-4">In good standing.</p>

          <label class="block text-sm mb-1">What they are told</label>
          <standing-editor v-model="actionReason"></standing-editor>

          <label class="block text-sm mb-1">
            Internal notes <span class="text-gray-medium">— never shown to them</span>
          </label>
          <textarea
            v-model="actionNotes"
            rows="2"
            placeholder="Where you saw it, call volumes, what was said and when."
            class="w-full p-2 bg-darken mb-2"
          ></textarea>
          <button
            @click="saveNote"
            :disabled="busy || !actionNotes.trim()"
            class="transition-colors text-white rounded bg-blue hover:bg-lblue py-1 px-3 text-sm mb-4 disabled:bg-gray-medium"
          >
            Save note only
          </button>

          <label class="block text-sm mb-1">
            Fix or reply by <span class="text-gray-medium">— info requests and warnings, optional</span>
          </label>
          <input v-model="actionRespondBy" type="date" class="p-2 bg-darken mb-4 block" />

          <div class="flex flex-wrap gap-3">
            <button
              @click="act('info')"
              :disabled="busy"
              class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4 disabled:bg-gray-medium"
            >
              Request info
            </button>
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
            An info request only sends them an email asking about their project. A warning
            changes nothing about their access either. Suspending stops their keys on both
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
                <td class="py-2 px-3">
                  <div v-if="row.reason" class="standing-text" v-html="row.reason"></div>
                  <template v-else>—</template>
                </td>
                <td class="py-2 px-3">{{ row.notes || '—' }}</td>
                <td class="py-2 px-3">{{ row.by || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="bg-lighten p-6 mb-8">
          <h2 class="text-lg mb-4">Endpoint Limits</h2>
          <api-usage-table :usage="detail.usage"></api-usage-table>
        </div>
      </template>

      <api-admin-twitch class="mb-8"></api-admin-twitch>

      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-4">Recent Subscription Activity</h2>

        <p v-if="!activity.length" class="text-sm text-gray-medium">Nothing yet.</p>

        <div v-else class="overflow-x-auto">
          <table class="min-w-0 w-full responsive-table">
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
  </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
import { formatNumber, formatBytes, formatDuration, formatCost } from '../../utils/apiFormat';

export default {
  name: 'ApiAdminConsole',
  components: {
    // Outside components/ so the editor ships with this page only, not every page.
    StandingEditor: defineAsyncComponent(() => import('../../admin/StandingEditor.vue')),
  },
  data(){
    return {
      infoText: "Look up an account, grant comped access, and see what subscriptions have moved recently.",
      term: '',
      results: [],
      truncated: false,
      searched: false,
      detail: null,
      activity: [],
      usage: [],
      usageColumns: [
        { key: 'email', label: 'Email' },
        { key: 'calls', label: 'Calls' },
        { key: 'egress_bytes', label: 'Egress' },
        { key: 'compute_ms', label: 'Compute' },
        { key: 'cost_usd', label: 'Cost' },
        { key: 'approved_at', label: 'Approved' },
      ],
      usageSortKey: 'cost_usd',
      usageSortDir: 'desc',
      loadingId: null,
      metrics: null,
      busy: false,
      error: null,
      notice: null,
      actionReason: '',
      actionNotes: '',
      actionRespondBy: '',
      approveNotes: '',
      editingApprovalId: null,
      approvalNotes: '',
    }
  },
  computed: {
    linkableWebsite(){
      const website = this.detail?.account?.website;

      return /^https?:\/\//i.test(website || '') ? website : null;
    },
    // The row being loaded takes the highlight straight away, not after the fetch.
    selectedUsageId(){
      return this.loadingId ?? this.detail?.account?.id ?? null;
    },
    sortedUsage(){
      const key = this.usageSortKey;
      const direction = this.usageSortDir === 'asc' ? 1 : -1;

      // Never approved sorts as oldest.
      const value = (row) => key === 'email' ? (row.email || '').toLowerCase()
        : key === 'approved_at' ? (row.approved_at || '')
        : row[key];

      return this.usage.slice().sort((a, b) => {
        const valA = value(a);
        const valB = value(b);

        return valA < valB ? -direction : valA > valB ? direction : 0;
      });
    },
  },
  mounted(){
    this.loadMetrics();
    this.loadUsage();
    this.loadActivity();
  },
  methods: {
    formatNumber,
    formatBytes,
    formatDuration,
    formatCost,
    async loadUsage(){
      try {
        const response = await this.$axios.get('/api/v1/admin/usage');
        this.usage = response.data.usage;
      } catch (error) {
        // As with metrics.
      }
    },
    sortUsage(key){
      if(key === this.usageSortKey){
        this.usageSortDir = this.usageSortDir === 'asc' ? 'desc' : 'asc';
      } else {
        // Oldest approval first, so whoever has never been approved is at the top.
        this.usageSortDir = key === 'email' || key === 'approved_at' ? 'asc' : 'desc';
      }

      this.usageSortKey = key;
    },
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
      const term = this.term.trim();
      if(term.length < 2 && !/^\d+$/.test(term)){
        this.error = 'Enter at least two characters, or an account ID.';
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
    async load(id, scroll = false){
      this.error = null;
      this.notice = null;
      // Text typed against the last account must not follow you to the next one.
      this.clearAction();
      this.loadingId = id;

      try {
        const response = await this.$axios.get('/api/v1/admin/accounts/' + id);
        this.detail = response.data;

        // The usage list can be long enough to push the detail off screen.
        if(scroll){
          this.$nextTick(() => this.$refs.detail?.scrollIntoView({ behavior: 'smooth' }));
        }
      } catch (error) {
        this.error = this.messageFrom(error);
      } finally {
        // A slower earlier click must not clear the spinner of a later one.
        if(this.loadingId === id){
          this.loadingId = null;
        }
      }
    },
    editApprovalNotes(row){
      this.editingApprovalId = row.id;
      this.approvalNotes = row.notes || '';
    },
    async saveApprovalNotes(approvalId){
      this.busy = true;
      this.error = null;
      this.notice = null;

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/' + this.detail.account.id + '/approvals/' + approvalId + '/notes', {
          notes: this.approvalNotes.trim() || null,
        });

        this.detail.approvals = response.data.approvals;
        this.editingApprovalId = null;
        this.notice = 'Approval notes saved.';
      } catch (error) {
        this.error = this.messageFrom(error);
      } finally {
        this.busy = false;
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
      if(action === 'info'){
        return 'Send this question? They get an email only. No banner, and nothing about their access changes.';
      }

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

      if((action === 'warn' || action === 'info') && this.actionRespondBy){
        payload.respond_by = this.actionRespondBy;
      }

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/' + this.detail.account.id + '/' + action, payload);

        this.detail.enforcement = response.data.enforcement;
        this.detail.history = response.data.history;
        this.clearAction();

        this.notice = {
          info: 'Question sent.',
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
    async saveNote(){
      this.busy = true;
      this.error = null;
      this.notice = null;

      try {
        const response = await this.$axios.post('/api/v1/admin/accounts/' + this.detail.account.id + '/note', {
          notes: this.actionNotes.trim(),
        });

        this.detail.enforcement = response.data.enforcement;
        this.detail.history = response.data.history;
        this.actionNotes = '';
        this.notice = 'Note saved. Nothing was sent.';
      } catch (error) {
        this.error = this.messageFrom(error);
      } finally {
        this.busy = false;
      }
    },
    async approve(){
      if(!confirm('Record your approval of this project as currently described? Nothing is sent and their access does not change.')){
        return;
      }

      this.busy = true;
      this.error = null;
      this.notice = null;

      try {
        const id = this.detail.account.id;
        const response = await this.$axios.post('/api/v1/admin/accounts/' + id + '/approve', {
          notes: this.approveNotes.trim() || null,
        });

        this.detail.approvals = response.data.approvals;
        this.detail.last_approval = response.data.last_approval;
        this.approveNotes = '';
        this.notice = 'Approval recorded. Nothing was sent.';

        const row = this.usage.find(u => u.id === id);

        if(row && response.data.last_approval){
          row.approved_at = response.data.last_approval.at;
        }
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
      this.approveNotes = '';
      this.editingApprovalId = null;
      this.approvalNotes = '';
    },
    messageFrom(error){
      return (error.response && error.response.data && error.response.data.error)
        ? error.response.data.error
        : 'Something went wrong.';
    },
  },
}
</script>
