<template>
  <div>
    <page-heading :infoText1="infoText" heading="Billing"></page-heading>

    <div class="mx-auto max-w-[1000px] p-4">
      <div v-if="error" class="bg-red p-3 mb-4">{{ error }}</div>
      <div v-if="notice" class="bg-teal p-3 mb-4">{{ notice }}</div>

      <div v-if="servesfixtures" class="bg-lighten border-l-4 border-yellow p-4 mb-8">
        <p class="text-sm">
          <strong>Your API calls are returning example data.</strong>
          Subscribing does not change that on its own — live data is switched on from your
          <a href="/Api/Account" class="underline hover:text-lteal">account page</a>.
          Responses carry <code class="text-lteal">X-HP-Data-Source: fixture</code> while this is the case.
        </p>
      </div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">Payment Method</h2>

        <div v-if="card && !showCardForm" class="flex flex-wrap items-center gap-4">
          <span class="capitalize">{{ card.brand }} ending {{ card.last_four }}</span>
          <custom-button @click="startCardUpdate" :text="'Update Card'" :alt="'Update card'" :size="'small'" :ignoreclick="true"></custom-button>
        </div>

        <div v-show="showCardForm">
          <div ref="paymentElement" class="mb-4"></div>
          <div ref="addressElement" class="mb-4"></div>

          <div class="flex flex-wrap gap-2">
            <button
              :disabled="savingCard"
              @click="saveCard"
              class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4 disabled:bg-gray-medium"
            >
              {{ savingCard ? 'Saving…' : 'Save Card' }}
            </button>
            <custom-button v-if="card" @click="showCardForm = false" :text="'Cancel'" :alt="'Cancel'" :size="'small'" :ignoreclick="true"></custom-button>
          </div>
        </div>

        <p v-if="!card && !showCardForm" class="text-sm text-gray-medium">
          No card on file. Add one to subscribe.
        </p>
      </div>

      <div v-if="granted.length" class="bg-lighten border-l-4 border-teal p-6 mb-8">
        <h2 class="text-lg mb-2">Special Access</h2>
        <p class="text-sm text-gray-medium mb-3">
          Granted to your account at no charge. There is nothing to pay and nothing to
          cancel. You can still buy a plan below if you want access beyond this.
        </p>
        <ul class="text-sm list-disc list-inside">
          <li v-for="plan in granted" :key="plan.id">{{ plan.name }}</li>
        </ul>
      </div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">Plan</h2>

        <loading-component v-if="busy" :textoverride="true">
          {{ busyText }}<br/>Do not close this page.
        </loading-component>

        <template v-else>
        <div v-if="current" class="mb-4 text-sm">
          <div>
            Current plan: <strong>{{ planName(current.plan_id) }}</strong>
            <span class="text-gray-medium">({{ current.status }})</span>
          </div>
          <div v-if="current.on_grace_period" class="text-yellow mt-1">
            Cancels on {{ current.ends_at }}. You keep access until then.
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3 mb-4">
          <div v-for="plan in plans" :key="plan.id" class="bg-darken p-4 text-center flex flex-col">
            <div class="text-lg mb-1">{{ plan.name }}</div>
            <div class="text-2xl mb-4">
              ${{ plan.price }} <span class="text-sm text-gray-medium">/ mo</span>
            </div>
            <div class="mt-auto">
              <span v-if="current && current.plan_id === plan.id" class="text-sm text-lteal">Current plan</span>
              <a
                v-else-if="!plan.purchasable"
                href="/Api/DeveloperTier"
                class="transition-colors text-white rounded bg-red hover:bg-lred py-2 px-4 w-full inline-block text-center"
              >
                Contact Us
              </a>
              <button
                v-else
                :disabled="!card || busy"
                @click="subscribe(plan)"
                class="transition-colors text-white rounded bg-blue hover:bg-lblue py-2 px-4 w-full disabled:bg-gray-medium"
              >
                {{ current ? 'Switch' : 'Subscribe' }}
              </button>
            </div>
          </div>
        </div>

        <p v-if="!card" class="text-sm text-gray-medium">Add a card above before choosing a plan.</p>

        <div v-if="current && !current.on_grace_period" class="mt-4">
          <custom-button @click="cancel" :text="'Cancel Subscription'" :alt="'Cancel subscription'" :size="'small'" :color="'red'" :ignoreclick="true"></custom-button>
        </div>
        <div v-if="current && current.on_grace_period" class="mt-4">
          <custom-button @click="resume" :text="'Resume Subscription'" :alt="'Resume subscription'" :size="'small'" :color="'teal'" :ignoreclick="true"></custom-button>
        </div>
        </template>
      </div>

      <div class="bg-lighten p-6 mb-8">
        <h2 class="text-lg mb-4">Invoices</h2>

        <p v-if="!invoices.length" class="text-sm text-gray-medium">No invoices yet.</p>

        <table v-else class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Date</th>
              <th class="py-2 px-3 text-left text-sm">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td class="py-2 px-3">{{ invoice.date }}</td>
              <td class="py-2 px-3">{{ invoice.total }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bg-lighten p-6">
        <h2 class="text-lg mb-4">Endpoint Limits</h2>
        <p class="text-sm text-gray-medium mb-4">
          Every endpoint has its own weekly allowance and its own rolling seven-day
          window, which starts on your first call to that endpoint. Calls made while
          you are receiving test data are not counted.
        </p>
        <api-usage-table :usage="usage"></api-usage-table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiBilling',
  components: {
  },
  props: {
    stripekey: String,
    plans: {
      type: Array,
      default: () => [],
    },
    granted: {
      type: Array,
      default: () => [],
    },
    subscription: Object,
    paymentmethod: Object,
    usage: {
      type: Array,
      default: () => [],
    },
    servesfixtures: {
      type: Boolean,
      default: false,
    },
  },
  data(){
    return {
      infoText: "Manage your card, plan and invoices. Cancelling leaves your access active until the end of the period you have already paid for.",
      stripe: null,
      elements: null,
      card: this.paymentmethod,
      current: this.subscription,
      invoices: [],
      showCardForm: !this.paymentmethod,
      savingCard: false,
      busy: false,
      busyText: '',
      error: null,
      notice: null,
    }
  },
  mounted(){
    this.loadInvoices();

    if(this.showCardForm){
      this.mountElements();
    }
  },
  methods: {
    planName(planId){
      const plan = this.plans.find(p => p.id === planId);
      return plan ? plan.name : 'Unknown';
    },
    async loadInvoices(){
      try {
        const response = await this.$axios.get('/api/v1/account/billing/invoices');
        this.invoices = response.data.invoices;
      } catch (error) {
        // An invoice list that fails to load should not block billing changes.
      }
    },
    startCardUpdate(){
      this.showCardForm = true;
      this.$nextTick(() => this.mountElements());
    },
    async mountElements(){
      if(this.elements){
        return;
      }

      try {
        const response = await this.$axios.post('/api/v1/account/billing/setup-intent');

        this.stripe = window.Stripe(this.stripekey);
        this.elements = this.stripe.elements({
          clientSecret: response.data.client_secret,
          appearance: {
            theme: 'night',
            variables: {
              colorPrimary: '#008b8b',
              colorBackground: '#0F121D',
              colorText: '#ffffff',
              fontFamily: 'Open Sans, sans-serif',
              borderRadius: '4px',
            },
          },
        });

        this.elements.create('payment').mount(this.$refs.paymentElement);
        this.elements.create('address', { mode: 'billing' }).mount(this.$refs.addressElement);
      } catch (error) {
        this.error = 'Could not load the payment form. Please refresh and try again.';
      }
    },
    async saveCard(){
      this.savingCard = true;
      this.error = null;
      this.notice = null;

      const { setupIntent, error } = await this.stripe.confirmSetup({
        elements: this.elements,
        redirect: 'if_required',
      });

      if(error){
        this.error = error.message;
        this.savingCard = false;
        return;
      }

      try {
        const response = await this.$axios.post('/api/v1/account/billing/payment-method', {
          payment_method: setupIntent.payment_method,
        });

        this.card = response.data.payment_method;
        this.showCardForm = false;
        this.notice = 'Card saved.';
      } catch (e) {
        this.error = e.response?.data?.error || 'Could not save that card.';
      }

      this.savingCard = false;
    },
    async subscribe(plan){
      this.busyText = this.current
        ? 'Switching to ' + plan.name + '...'
        : 'Starting your ' + plan.name + ' subscription...';
      this.busy = true;
      this.error = null;
      this.notice = null;

      try {
        await this.$axios.post('/api/v1/account/billing/subscribe', { plan_id: plan.id });
        window.location.reload();
      } catch (e) {
        this.error = e.response?.data?.error || 'Could not change your plan.';
        this.busy = false;
      }
    },
    async cancel(){
      if(!confirm('Cancel your subscription? You keep access until the end of the current period.')){
        return;
      }

      this.busyText = 'Cancelling your subscription...';
      this.busy = true;
      this.error = null;

      try {
        await this.$axios.post('/api/v1/account/billing/cancel');
        window.location.reload();
      } catch (e) {
        this.error = e.response?.data?.error || 'Could not cancel.';
        this.busy = false;
      }
    },
    async resume(){
      this.busyText = 'Resuming your subscription...';
      this.busy = true;
      this.error = null;

      try {
        await this.$axios.post('/api/v1/account/billing/resume');
        window.location.reload();
      } catch (e) {
        this.error = e.response?.data?.error || 'Could not resume.';
        this.busy = false;
      }
    },
  },
}
</script>
