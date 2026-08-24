<template>
  <div>
    <scroll-to-top></scroll-to-top>

    <page-heading
      heading="Docs"
      :infoText1="'Every endpoint, generated from the routes themselves — so this page cannot describe an endpoint that does not exist.'"
    ></page-heading>

    <div v-if="!spec" class="mx-auto max-w-[900px] px-4 mt-8">
      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-2">The specification has not been built yet</h2>
        <p class="text-sm mb-4">Run <code class="text-lteal">php artisan api:build-spec</code> and reload.</p>
      </div>
    </div>

    <div v-else class="mx-auto max-w-[1800px] px-4 mt-6 flex gap-8 items-start">

      <!--
        Bounded to the viewport and scrollable in its own right. Sticky alone
        pins it, and anything past the fold then has nothing left to scroll.
      -->
      <nav class="hidden lg:block w-200 shrink-0 sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto pr-2">
        <input
          v-model="search"
          type="text"
          placeholder="Filter endpoints"
          class="form-control w-full text-black rounded p-2 text-sm mb-3"
        />

        <ul v-if="variables.length && !search" class="text-sm mb-4">
          <li><a href="#variables" class="hover:text-lteal">Variables</a></li>
        </ul>

        <div v-for="group in groups" :key="group.name" class="mb-4">
          <h3 class="text-lteal text-sm uppercase tracking-wider mb-1">{{ group.name }}</h3>
          <ul class="text-sm space-y-1">
            <li v-for="op in group.operations" :key="op.id">
              <a :href="'#' + op.id" class="hover:text-lteal break-all">{{ op.path }}</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="flex-grow min-w-0">

        <!--
          Admins only. Both switches exist on the account page too; they are
          mirrored here because this is where calls are actually run, and going
          back and forth to change what a call returns is the whole friction.
        -->
        <div v-if="state && state.admin" class="bg-lighten p-4 mb-6 flex flex-wrap items-center gap-6">
          <div>
            <div class="text-sm mb-1">Admin mode</div>
            <tab-button
              tab1text="On"
              tab2text="Off"
              tab1alt="Ignore quota, keys and migration status"
              tab2alt="Be treated as an ordinary account"
              :ignoreclick="true"
              :overridedefaultside="state.admin_mode ? 'left' : 'right'"
              @tab-click="setAdminMode"
            ></tab-button>
          </div>

          <div>
            <div class="text-sm mb-1">Data</div>
            <tab-button
              tab1text="Live"
              tab2text="Test"
              tab1alt="Return real data"
              tab2alt="Return example data"
              :ignoreclick="true"
              :overridedefaultside="state.test_mode ? 'right' : 'left'"
              @tab-click="setTestMode"
            ></tab-button>
          </div>

          <p class="text-xs text-gray-medium max-w-[420px]">
            <template v-if="state.admin_mode">
              Calls skip quota and need no key. Turn admin mode off to see exactly what a
              customer on your plan would get.
            </template>
            <template v-else>
              Being treated as an ordinary account — quota, key and migration status all apply.
            </template>
          </p>
        </div>

        <p v-if="!groups.length" class="text-sm text-gray-medium">Nothing matches "{{ search }}".</p>

        <api-variables v-if="variables.length && !search" :variables="variables"></api-variables>

        <section v-for="group in groups" :key="group.name" class="mb-10">
          <h2 class="text-2xl mb-4">{{ group.name }}</h2>

          <article
            v-for="op in group.operations"
            :key="op.id"
            :id="op.id"
            class="bg-lighten p-6 mb-4 scroll-mt-4"
          >
            <div class="flex flex-wrap items-baseline gap-3 mb-2">
              <span class="bg-teal text-white rounded px-2 py-1 text-xs uppercase">{{ op.method }}</span>
              <code class="text-lg break-all">{{ op.path }}</code>
              <span v-if="!op.secured" class="bg-yellow text-black rounded px-2 py-1 text-xs">no key needed</span>
            </div>

            <p class="text-sm mb-4">{{ op.summary }}</p>

            <!-- The page this endpoint answers for. Faster than any description at
                 showing what the data is — go and look at it.

                 Player and match pages are addressed by battletag or replay id, so
                 those are shown as a pattern rather than linked: a link with the
                 placeholders still in it would just 404. -->
            <p v-if="op.sitePage" class="text-sm mb-4">
              Powers
              <a
                v-if="!op.sitePage.includes('{')"
                :href="op.sitePage"
                target="_blank"
                rel="noopener"
                class="link"
              >heroesprofile.com{{ op.sitePage }}</a>
              <code v-else class="text-lteal">heroesprofile.com{{ op.sitePage }}</code>
              — the same data, filtered the same way.
            </p>

            <p v-if="op.quotaKey" class="text-xs text-gray-medium mb-4">
              Counts against your <code class="text-lteal">{{ op.quotaKey }}</code> allowance.
            </p>

            <template v-if="op.parameters.length">
              <h3 class="text-sm uppercase tracking-wider text-lteal mb-2">Parameters</h3>
              <table class="min-w-0 w-full responsive-table mb-4">
                <thead>
                  <tr>
                    <th class="py-2 px-3 text-left text-sm">Name</th>
                    <th class="py-2 px-3 text-left text-sm">In</th>
                    <th class="py-2 px-3 text-left text-sm">Type</th>
                    <th class="py-2 px-3 text-left text-sm">Description</th>
                    <th class="py-2 px-3 text-left text-sm">Value</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in op.parameters" :key="p.name">
                    <td class="py-2 px-3">
                      {{ p.name }}
                      <div v-if="p.required" class="text-xs">required</div>
                    </td>
                    <td class="py-2 px-3">{{ p.in }}</td>
                    <td class="py-2 px-3">
                      {{ p.schema && p.schema.type }}
                      <div v-if="p.schema && p.schema.enum" class="text-xs break-all">{{ p.schema.enum.join(', ') }}</div>
                    </td>
                    <td class="py-2 px-3">
                      {{ p.description }}
                      <div v-if="p.example !== undefined" class="text-xs">e.g. {{ p.example }}</div>
                    </td>
                    <td class="py-2 px-3">
                      <select
                        v-if="p.schema && p.schema.enum"
                        v-model="values[op.id][p.name]"
                        class="form-control w-full text-black rounded p-1 text-sm"
                      >
                        <option value=""></option>
                        <option v-for="choice in p.schema.enum" :key="choice" :value="choice">{{ choice }}</option>
                      </select>
                      <input
                        v-else
                        v-model="values[op.id][p.name]"
                        type="text"
                        :placeholder="p.example !== undefined ? String(p.example) : ''"
                        class="form-control w-full text-black rounded p-1 text-sm"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </template>

            <div class="mb-4">
              <div v-if="!authenticated" class="text-sm text-gray-medium">
                <a href="/Api/Login" class="link">Sign in</a> to run this endpoint from here.
              </div>

              <template v-else>
                <div class="flex flex-wrap items-center gap-3 mb-2">
                  <custom-button
                    :text="running === op.id ? 'Running…' : 'Execute'"
                    :alt="'Run ' + op.path"
                    size="small"
                    color="teal"
                    :ignoreclick="true"
                    :disabled="running === op.id"
                    @click="execute(op)"
                  ></custom-button>
                  <span class="text-xs text-gray-medium">
                    <template v-if="state && state.admin_mode">Runs as admin — no key, no quota.</template>
                    <template v-else>Runs against your account and spends one call.</template>
                  </span>
                </div>

                <div v-if="running === op.id" class="mt-3">
                  <loading-component :textoverride="true">
                    Calling {{ op.path }}
                  </loading-component>
                </div>

                <div v-else-if="results[op.id]" class="mt-3">
                  <div class="flex flex-wrap items-baseline gap-3 text-sm mb-1">
                    <span :class="results[op.id].status < 300 ? 'text-lteal' : 'text-lred'">
                      HTTP {{ results[op.id].status }}
                    </span>
                    <span v-if="results[op.id].headers['x-hp-data-source'] === 'fixture'" class="bg-yellow text-black rounded px-2 py-1 text-xs">
                      example data — your account is on test data
                    </span>
                  </div>

                  <p v-if="results[op.id].public_url" class="text-xs break-all mb-2">
                    <span class="text-gray-medium">Request URL</span>
                    <!-- Carries its own scheme: it comes from the spec's declared
                         server, so it is exactly the base URL the docs advertise. -->
                    <code class="text-lteal">{{ results[op.id].public_url }}</code>
                  </p>

                  <div class="relative">
                    <button
                      class="absolute top-2 right-8 bg-lighten hover:bg-teal transition-colors rounded px-2 py-1 text-xs"
                      :title="copied === op.id ? 'Copied' : 'Copy response'"
                      :aria-label="copied === op.id ? 'Copied' : 'Copy response'"
                      @click="copy(op)"
                    >
                      <i :class="copied === op.id ? 'fas fa-check' : 'fas fa-copy'"></i>
                    </button>

                    <pre class="bg-darken p-3 pr-20 text-xs overflow-x-auto max-h-96">{{ pretty(results[op.id].body) }}</pre>
                  </div>
                </div>
              </template>
            </div>

            <h3 class="text-sm uppercase tracking-wider text-lteal mb-2">Responses</h3>
            <div v-for="response in op.responses" :key="response.status" class="mb-3">
              <div class="flex flex-wrap items-baseline gap-2 text-sm">
                <span :class="response.status.startsWith('2') ? 'text-lteal' : 'text-yellow'">{{ response.status }}</span>
                <span class="text-gray-medium">{{ response.contentType }}</span>
              </div>
              <p class="text-sm">{{ response.description }}</p>
              <api-schema-tree v-if="response.schema" :schema="response.schema"></api-schema-tree>
            </div>
          </article>
        </section>
      </div>
    </div>
  </div>
</template>

<script>
/*
 * Renders the generated OpenAPI document. Read-only: firing requests is the test
 * client, which builds on this same parsing.
 *
 * Sections come from the document's tags, which the spec builder emits from
 * config rather than deriving from the URL — grouping by path segment leaves
 * `party` and `draft` stranded in sections of their own.
 */
export default {
  name: 'ApiDocs',
  props: {
    spec: {
      type: Object,
      default: null,
    },
    authenticated: Boolean,
    account: {
      type: Object,
      default: null,
    },
    variables: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      search: '',
      values: {},
      results: {},
      running: null,
      copied: null,
      // Local copy: the switches below change it without a page reload.
      state: this.account,
    }
  },
  created() {
    // Seed only what a call cannot go without: the required parameters, plus the
    // timeframe pair that every global endpoint needs to mean anything.
    //
    // Optional filters are deliberately left empty. Pre-filling them made Execute
    // return a narrow slice — one hero, one map, one region — which reads as the
    // endpoint's normal answer rather than as a filter someone else chose. The
    // example still shows as placeholder text.
    this.operations.forEach(op => {
      const seeded = {};

      op.parameters.forEach(p => {
        const seed = p.required || p.name === 'timeframe_type' || p.name === 'timeframe';

        seeded[p.name] = seed && p.example !== undefined ? String(p.example) : '';
      });

      this.values[op.id] = seeded;
    });
  },
  computed: {
    operations() {
      if (!this.spec || !this.spec.paths) return [];

      const rows = [];

      Object.keys(this.spec.paths).forEach(path => {
        const methods = this.spec.paths[path];

        Object.keys(methods).forEach(method => {
          const op = methods[method];

          rows.push({
            id: (op.operationId || path).replace(/[^\w.-]/g, '-'),
            // The proxy takes a route name, never a URL. Kept separate from `id`,
            // which is only an anchor and is free to be mangled for the DOM.
            routeName: op.operationId || null,
            group: (op.tags && op.tags[0]) || 'Other',
            path,
            method,
            summary: op.summary || '',
            quotaKey: op['x-endpoint-key'] || null,
            sitePage: op['x-site-page'] || null,
            // An empty security array on the operation overrides the document
            // default, which is how the keyless uploader routes are marked.
            secured: !(op.security && op.security.length === 0),
            parameters: op.parameters || [],
            responses: this.responses(op.responses || {}),
          });
        });
      });

      return rows;
    },

    /*
     * Sections come from the spec's tags, in the order it declares them —
     * Reference first, then the big ones. Endpoints sort alphabetically inside.
     */
    groups() {
      const term = this.search.trim().toLowerCase();
      const grouped = {};

      this.operations
        .filter(op => !term || op.path.toLowerCase().includes(term) || op.summary.toLowerCase().includes(term))
        .forEach(op => {
          (grouped[op.group] = grouped[op.group] || []).push(op);
        });

      const declared = (this.spec.tags || []).map(tag => tag.name);
      const found = Object.keys(grouped);

      // Anything the spec did not tag keeps its place rather than vanishing.
      const order = declared
        .filter(name => found.includes(name))
        .concat(found.filter(name => !declared.includes(name)).sort());

      return order.map(name => ({
        name,
        operations: grouped[name].sort((a, b) => a.path.localeCompare(b.path)),
      }));
    },
  },
  methods: {
    /*
     * The call is made by the portal, not the browser: keys are hashed, so there
     * is nothing here to send. The server resolves the account's own key, charges
     * it, and returns what came back.
     */
    /* Both write through to the account, and both are admin-only server-side. */
    async setAdminMode(side) {
      await this.setMode('/api/v1/account/admin-mode', { admin_mode: side === 'left' });
    },

    async setTestMode(side) {
      await this.setMode('/api/v1/account/test-mode', { test_mode: side === 'right' });
    },

    async setMode(url, payload) {
      try {
        const response = await this.$axios.post(url, payload);
        this.state = Object.assign({}, this.state, response.data);
      } catch (error) {
        // Left as it was; the switch reflects the server, not the click.
      }
    },

    async execute(op) {
      this.running = op.id;

      try {
        const response = await this.$axios.post('/Api/Docs/Try', {
          route: op.routeName,
          parameters: this.values[op.id] || {},
        });

        this.results[op.id] = response.data;
      } catch (error) {
        const body = error.response && error.response.data;

        // Our own errors use `error`; Laravel's use `message` — a 500 from the
        // delegated controller, a 422 from validation, a 419 from a stale CSRF
        // token. Showing only the first turned all of those into one unhelpful
        // sentence, hiding the actual cause. Fall through to whatever is there,
        // and keep the whole body when it is neither.
        this.results[op.id] = {
          status: error.response ? error.response.status : 0,
          headers: {},
          body: (body && (body.error || body.message || body))
            || 'The request could not be made — no response from the server.',
          public_url: null,
        };
      } finally {
        this.running = null;
      }
    },

    pretty(body) {
      return typeof body === 'string' ? body : JSON.stringify(body, null, 2);
    },

    async copy(op) {
      const text = this.pretty(this.results[op.id].body);

      try {
        // Only available over https and on localhost, so a LAN address in
        // development falls back rather than silently doing nothing.
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(text);
        } else {
          const field = document.createElement('textarea');
          field.value = text;
          field.style.position = 'fixed';
          field.style.opacity = '0';
          document.body.appendChild(field);
          field.select();
          document.execCommand('copy');
          document.body.removeChild(field);
        }

        this.copied = op.id;
        setTimeout(() => {
          if (this.copied === op.id) this.copied = null;
        }, 2000);
      } catch (error) {
        this.copied = null;
      }
    },

    responses(responses) {
      return Object.keys(responses).sort().map(status => {
        const response = responses[status] || {};
        const contentType = Object.keys(response.content || {})[0] || null;

        return {
          status,
          description: response.description || '',
          contentType,
          schema: contentType ? (response.content[contentType] || {}).schema : null,
        }
      });
    },
  },
}
</script>
