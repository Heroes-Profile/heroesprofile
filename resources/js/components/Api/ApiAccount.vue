<template>
  <div>
    <page-heading :infoText1="infoText" heading="Account"></page-heading>

    <div class="mx-auto max-w-[1000px] p-4">
      <div v-if="receivesTestData" class="bg-lighten border-l-4 border-yellow p-4 mb-8">
        <h2 class="text-lg mb-2">You Are Receiving Test Data</h2>
        <p class="text-sm mb-2">
          Calls to the API return fixed example responses with the correct shape but
          placeholder values. They do not count against your quota, so you can build and
          test freely. Rate limits still apply.
        </p>
        <p v-if="!migrated" class="text-sm text-gray-medium">
          Activating live data will expire your existing API key on the old site. Do that
          once your integration is ready.
        </p>
      </div>

      <div v-if="account.admin" class="bg-lighten border-l-4 border-teal p-6 mb-8">
        <h2 class="text-lg mb-2">Admin Mode <span class="text-sm text-lteal">{{ adminMode ? 'on' : 'off' }}</span></h2>
        <p class="text-sm text-gray-medium mb-4">
          While on, calls ignore quota, need no key, and are not held back by migration
          status. Turn it off to be treated as an ordinary account — the grant stays, so
          you can switch back.
        </p>
        <tab-button
          tab1text="On"
          tab2text="Off"
          tab1alt="Ignore quota, keys and migration status"
          tab2alt="Be treated as an ordinary account"
          :ignoreclick="true"
          :overridedefaultside="adminMode ? 'left' : 'right'"
          @tab-click="setAdminMode"
        ></tab-button>
      </div>

      <!-- Admins see this whether or not they have migrated: admin mode ignores
           the migration gate, so the live/test switch is meaningful either way. -->
      <div v-if="migrated || account.admin" class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-2">Data Mode</h2>

        <template v-if="account.can_use_live_data">
          <p class="text-sm text-gray-medium mb-4">
            Switch to test data whenever you are building or debugging. Responses become
            fixed examples and stop counting against your weekly quota.
          </p>
          <tab-button
            tab1text="Live Data"
            tab2text="Test Data"
            tab1alt="Return real data and use quota"
            tab2alt="Return example data and use no quota"
            :ignoreclick="true"
            :overridedefaultside="testMode ? 'right' : 'left'"
            @tab-click="setTestMode"
          ></tab-button>
        </template>

        <template v-else>
          <p class="text-sm text-gray-medium mb-4">
            You are on test data. Responses are fixed examples and cost no quota, so you can
            build against the API before paying for it.
            <strong class="text-white">Live data needs an active subscription</strong> — this
            switch unlocks once you have one.
          </p>
          <a href="/Api/Account/Billing" class="inline-block bg-blue hover:bg-lblue transition-colors text-white rounded py-2 px-4">
            Choose a plan
          </a>
        </template>
      </div>

      <div v-if="!migrated" class="bg-lighten border-l-4 border-teal p-6 mb-8">
        <h2 class="text-lg mb-2">{{ activateHeading }}</h2>
        <p class="text-sm mb-2">
          Your calls currently return example data. Activating switches them to real
          results, and every call starts counting against your weekly quota.
        </p>
        <p v-if="account.has_legacy_token" class="text-sm text-yellow mb-3">
          This immediately expires your existing key on the old API site. It cannot be
          undone. Do it once your integration is ready to move.
        </p>
        <p v-else class="text-sm text-gray-medium mb-3">
          You can switch back to example data at any time using the test data toggle.
        </p>

        <button
          :disabled="!keys.length || activating"
          @click="activate"
          class="transition-colors text-white rounded bg-teal hover:bg-lteal py-2 px-4 disabled:bg-gray-medium"
        >
          {{ activating ? 'Activating…' : activateHeading }}
        </button>

        <p v-if="!keys.length" class="text-sm text-gray-medium mt-2">
          Create an API key below first — activating expires your old one.
        </p>
      </div>

      <div v-if="linkerror" class="bg-red p-3 mb-4">{{ linkerror }}</div>
      <div v-if="notice" class="bg-teal p-3 mb-4">{{ notice }}</div>
      <div v-if="error" class="bg-red p-3 mb-4">{{ error }}</div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">Patreon</h2>

        <template v-if="patreonLinked">
          <p v-if="account.patreon_plan" class="text-sm mb-4">
            Linked. Your pledge includes the <strong>{{ account.patreon_plan }}</strong> tier
            at no extra cost — it stacks with any plan you buy, and each endpoint uses
            whichever allowance is higher.
          </p>
          <p v-else class="text-sm text-gray-medium mb-4">
            Linked, but your current pledge does not reach a tier that includes API access.
          </p>

          <custom-button @click="unlinkPatreon" :text="'Unlink Patreon'" :alt="'Unlink Patreon'" :size="'small'" :ignoreclick="true"></custom-button>
        </template>

        <template v-else>
          <p class="text-sm text-gray-medium mb-4">
            Already supporting Heroes Profile on Patreon? Link your account and your
            pledge includes API access — no need to pay twice.
          </p>

          <a href="/Api/Patreon/Link" class="link">Link Patreon</a>
        </template>
      </div>

      <div v-if="newKey" class="bg-teal p-4 mb-8">
        <h2 class="text-lg mb-2">Copy Your New Key Now</h2>
        <p class="text-sm mb-3">
          This is the only time it will be shown. We store a hash of it, so it cannot be
          retrieved again. If you lose it, revoke the key and create another.
        </p>
        <div class="flex flex-wrap gap-2 items-center">
          <code class="bg-darken p-2 break-all flex-grow">{{ newKey.plain_text }}</code>
          <custom-button @click="copyKey" :text="copied ? 'Copied' : 'Copy'" :alt="'Copy key'" :size="'small'" :ignoreclick="true"></custom-button>
        </div>
      </div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">API Keys</h2>

        <form @submit.prevent="createKey" class="flex flex-wrap gap-2 items-end mb-6">
          <div class="flex flex-col flex-grow">
            <label for="keyname" class="text-sm mb-1">Name this key</label>
            <input
              class="form-control search-input text-black"
              type="text"
              id="keyname"
              v-model="keyName"
              placeholder="e.g. my-dashboard"
              maxlength="255"
              required
            >
          </div>
          <button
            :disabled="isLoading || !keyName"
            type="submit"
            class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 my-2 disabled:bg-gray-medium"
          >
            Create Key
          </button>
        </form>

        <p v-if="!keys.length" class="text-sm text-gray-medium">
          You have no active keys. Create one above to start using the API.
        </p>
        <table  v-else class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Name</th>
              <th class="py-2 px-3 text-left text-sm">Created</th>
              <th class="py-2 px-3 text-left text-sm">Last Used</th>
              <th class="py-2 px-3 text-left text-sm"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="key in keys" :key="key.id">
              <td class="py-2 px-3">{{ key.name }}</td>
              <td class="py-2 px-3">{{ key.created_at }}</td>
              <td class="py-2 px-3">{{ key.last_used_at || 'Never' }}</td>
              <td class="py-2 px-3 text-right">
                <custom-button @click="revokeKey(key)" :text="'Revoke'" :alt="'Revoke key'" :size="'small'" :color="'red'" :ignoreclick="true"></custom-button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-4">This Week's Usage</h2>
        <api-usage-table :usage="usage" :compact="true"></api-usage-table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiAccount',
  components: {
  },
  props: {
    account: {
      type: Object,
      required: true
    },
    initialkeys: {
      type: Array,
      default: () => [],
    },
    usage: {
      type: Array,
      default: () => [],
    },
    notice: {
      type: String,
      default: null,
    },
    linkerror: {
      type: String,
      default: null,
    },
  },
  data(){
    return {
      infoText: this.account.email,
      keys: [...this.initialkeys],
      keyName: '',
      newKey: null,
      isLoading: false,
      copied: false,
      error: null,
      testMode: this.account.test_mode,
      patreonLinked: this.account.patreon_linked,
      adminMode: this.account.admin_mode,
      receivesTestData: this.account.receives_test_data,
      migrated: this.account.migrated,
      activating: false,
    }
  },
  computed: {
    // Accounts registered here have no old key, so there is nothing to migrate.
    activateHeading(){
      return this.account.has_legacy_token ? 'Migrate Account' : 'Activate Live Data';
    },
  },
  methods: {
    async unlinkPatreon(){
      this.error = null;

      try {
        await this.$axios.post('/Api/Patreon/Unlink');
        this.patreonLinked = false;
        // The granted tier came from the pledge, so the page's copy is now wrong.
        window.location.reload();
      } catch (error) {
        this.error = 'Could not unlink Patreon. Please try again.';
      }
    },
    async activate(){
      const warning = this.account.has_legacy_token
        ? 'Activate live data? This expires your existing key on the old API site immediately and cannot be undone.'
        : 'Activate live data? Calls will return real results and start using your weekly quota.';

      if(!confirm(warning)){
        return;
      }

      this.activating = true;
      this.error = null;

      try {
        const response = await this.$axios.post('/api/v1/account/migrate');

        this.migrated = response.data.migrated;
        this.receivesTestData = response.data.receives_test_data;
      } catch (e) {
        this.error = e.response?.data?.error || 'Could not activate live data. Please try again.';
      }

      this.activating = false;
    },
    async setAdminMode(side) {
      const enabled = side === 'left';

      if (enabled === this.adminMode) {
        return;
      }

      try {
        const response = await this.$axios.post('/api/v1/account/admin-mode', {
          admin_mode: enabled,
        });

        this.adminMode = response.data.admin_mode;
      } catch (error) {
        // Left as it was; the switch reflects the server, not the click.
      }
    },

    async setTestMode(side){
      const enabled = side === 'right';

      if(enabled === this.testMode){
        return;
      }

      this.error = null;

      try {
        const response = await this.$axios.post('/api/v1/account/test-mode', {
          test_mode: enabled,
        });

        this.testMode = response.data.test_mode;
        this.receivesTestData = response.data.receives_test_data;
      } catch (error) {
        // The server refuses live data without a plan. Say why rather than
        // offering a retry that cannot succeed.
        this.error = error.response?.data?.error || 'Could not change data mode. Please try again.';
      }
    },
    async createKey(){
      this.isLoading = true;
      this.error = null;

      try {
        const response = await this.$axios.post('/api/v1/account/keys', {
          name: this.keyName,
        });

        this.newKey = response.data.key;
        this.keys.unshift(response.data.key);
        this.keyName = '';
        this.copied = false;
      } catch (error) {
        this.error = error.response?.data?.error
          || error.response?.data?.message
          || 'Could not create the key. Please try again.';
      }

      this.isLoading = false;
    },
    async revokeKey(key){
      if(!confirm('Revoke "' + key.name + '"? Anything using it will stop working immediately.')){
        return;
      }

      this.error = null;

      try {
        await this.$axios.post('/api/v1/account/keys/revoke', { id: key.id });
        this.keys = this.keys.filter(k => k.id !== key.id);

        if(this.newKey && this.newKey.id === key.id){
          this.newKey = null;
        }
      } catch (error) {
        this.error = error.response?.data?.error || 'Could not revoke the key. Please try again.';
      }
    },
    copyKey(){
      navigator.clipboard.writeText(this.newKey.plain_text).then(() => {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 3000);
      });
    },
  },
}
</script>
