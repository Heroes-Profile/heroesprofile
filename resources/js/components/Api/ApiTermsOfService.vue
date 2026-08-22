<template>
  <div>
    <page-heading heading="API Terms of Service" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1000px] px-4 mt-6 pb-16">

      <div v-if="reviewrequired" class="bg-lighten border-l-4 border-yellow p-4 mb-8">
        <p class="text-sm mb-3">
          These terms have changed since you last accepted them. Read them through and accept to
          carry on using your account.
        </p>
        <form method="POST" action="/Api/Terms/Accept">
          <input type="hidden" name="_token" :value="csrf">
          <button type="submit" class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-4">
            I Accept These Terms
          </button>
        </form>
      </div>

      <p class="text-sm text-gray-medium mb-6">Last updated: August 2026</p>

      <p class="mb-6">
        Skill Tree Development, LLC (referred to as Heroes Profile in this document) operates the
        Heroes Profile API. These terms cover the API and the developer portal. Use of the rest of
        www.heroesprofile.com is covered by the
        <a class="link" href="/Terms/Of/Service">site terms of service</a>.
      </p>

      <h3 class="text-xl mb-2">1. Terms</h3>
      <p class="mb-6">
        By accessing the developer portal at https://www.heroesprofile.com/Api and creating an account,
        you are agreeing to be bound by these terms of service, all applicable laws and regulations,
        and agree that you are responsible for compliance with any applicable local laws. If you do
        not agree with any of these terms, you are prohibited from using or accessing the API. The
        materials contained in this website are protected by applicable copyright and trademark law.
        You may not use the API if you are under the age of 18.
      </p>

      <h3 class="text-xl mb-2">2. Subscriptions</h3>
      <p class="mb-3">
        In order to access the API, you are required to register for an account and provide
        additional information, such as contact information and a valid email address. We will
        contact you at this email address only with important information about your account.
        Heroes Profile (Skill Tree Development, LLC) will store this information in accordance with
        our <a class="link" href="/Api/Privacy">privacy policy</a>.
      </p>
      <p class="mb-3">
        A paid subscription is required in order to use the API. Pricing is listed on the
        <a class="link" href="/Api">API home page</a>. We may change pricing or features of pricing
        tiers at any time, but will give you advance notice of 30 days via email if changes will
        affect your monthly subscription costs.
      </p>
      <p class="mb-3">
        By making payment to us for a subscription plan, you authorize us to charge your payment
        method monthly at the subscription cost for the plan you chose. Your subscription will
        auto-renew until terminated. You can cancel your membership at any time from the
        <a class="link" href="/Api/Account/Billing">billing page</a> or by emailing us at
        <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>. You must
        cancel before the subscription renews in order to avoid fees for the next month. Your past
        invoices are listed on the billing page.
      </p>
      <p class="mb-3">
        Some payment methods may incur additional fees from your payment provider; we are not
        responsible for any additional fees.
      </p>
      <p class="mb-6">
        All payment is made via Stripe. We don't store any credit card or payment information on our
        servers. All payment collection is done via Stripe's API. There may be times when we need to
        regenerate keys or the API may be down. While we make all efforts to keep the API up for an
        acceptable use, we do not give refunds in the event the API is down.
      </p>

      <h3 class="text-xl mb-2">3. Use License</h3>
      <p class="mb-3">In order to use our API, you agree to the following:</p>
      <ul class="list-disc ml-6 mb-6 space-y-1">
        <li>
          You will not abuse our data or server resources. Each plan sets a limit on calls per minute
          and on calls per week by endpoint category; if you abuse this, your account may be
          terminated.
        </li>
        <li>
          You will keep your API key private. It authenticates as your account, and calls made with
          it count against your allowance and are your responsibility.
        </li>
        <li>
          OpenAPI endpoints are provided for general informational use only and are not intended for
          mass data aggregation, bulk extraction, or dataset mirroring.
        </li>
        <li>If you need large-scale or ongoing data aggregation, you must use an appropriate paid API tier.</li>
        <li>
          Direct endpoint integration is only allowable with the developer subscription. Any accounts
          found to be violating this may be subject to termination without refund.
        </li>
        <li>
          Developer Tier is intended for large dataset-driven analysis and similar high-scale use
          cases. Access requires independent
          <a class="link" href="/Api/DeveloperTier">review and approval of your project</a>
          by Heroes Profile.
        </li>
        <li>
          Developer Tier projects may not directly compete with Heroes Profile. We reserve the right
          to deny, suspend, or remove Developer Tier access at any time, including after approval.
        </li>
        <li>
          For all tiers: if your project is public-facing and uses Heroes Profile data, your
          public-facing tool must clearly state that it uses Heroes Profile data and include a
          visible link to https://www.heroesprofile.com/.
        </li>
        <li>Don't bully or harass others with our data.</li>
        <li>Do not reproduce our code or reverse-engineer our API — our code and database are our property.</li>
      </ul>
      <p class="mb-6">
        This license shall automatically terminate if you violate any of these restrictions and may
        be terminated by Heroes Profile (Skill Tree Development, LLC) at any time.
      </p>

      <h3 class="text-xl mb-2">4. Disclaimer</h3>
      <p class="mb-3">
        The data provided with the API and on our website are provided on an "as is" basis.
        Heroes Profile makes no warranties, expressed or implied, and hereby disclaims and negates all
        other warranties including, without limitation, implied warranties or conditions of
        merchantability, fitness for a particular purpose, or non-infringement of intellectual
        property or other violation of rights. We do not guarantee accurate or up-to-date data.
      </p>
      <p class="mb-6">
        Further, Heroes Profile (Skill Tree Development, LLC) does not warrant or make any
        representations concerning the accuracy, likely results, or reliability of the use of the
        materials on its website or otherwise relating to such materials or on any sites linked to
        this site. All data originates from replay files voluntarily uploaded by the community, which
        we then parse using our proprietary system. We are not affiliated with Blizzard or Heroes of
        the Storm, and cannot control changes in the game which may affect the accuracy, quality, or
        availability of the data we provide.
      </p>

      <h3 class="text-xl mb-2">5. Limitations</h3>
      <p class="mb-6">
        In no event shall Heroes Profile (Skill Tree Development, LLC) be liable for any damages
        (including, without limitation, damages for loss of data or profit, or due to business
        interruption) arising out of the use or inability to use the materials on Heroes Profile
        (Skill Tree Development, LLC)'s website, even if Heroes Profile (Skill Tree Development, LLC)
        or a Heroes Profile (Skill Tree Development, LLC) authorized representative has been notified
        orally or in writing of the possibility of such damage. Because some jurisdictions do not
        allow limitations on implied warranties, or limitations of liability for consequential or
        incidental damages, these limitations may not apply to you.
      </p>

      <h3 class="text-xl mb-2">6. Accuracy of Materials</h3>
      <p class="mb-6">
        The materials appearing on www.heroesprofile.com could include technical, typographical, or
        photographic errors. Heroes Profile (Skill Tree Development, LLC) does not warrant that any of
        the materials on its website are accurate, complete or current. Heroes Profile (Skill Tree
        Development, LLC) may make changes to the materials contained on its website at any time
        without notice. However Heroes Profile (Skill Tree Development, LLC) does not make any
        commitment to update the materials. We may change the functionality of the API at any time.
      </p>

      <h3 class="text-xl mb-2">7. Links</h3>
      <p class="mb-6">
        Heroes Profile (Skill Tree Development, LLC) has not reviewed all of the sites linked to its
        website and is not responsible for the contents of any such linked site. The inclusion of any
        link does not imply endorsement by Heroes Profile (Skill Tree Development, LLC) of the site.
        Use of any such linked website is at the user's own risk.
      </p>

      <h3 class="text-xl mb-2">8. Modifications</h3>
      <p class="mb-6">
        Heroes Profile (Skill Tree Development, LLC) may revise these terms of service at any time
        without notice. By using the API you are agreeing to be bound by the then current version of
        these terms of service. Where the change is material we may require you to accept the revised
        terms in the developer portal before you can carry on using your account.
      </p>

      <h3 class="text-xl mb-2">9. Governing Law</h3>
      <p class="mb-6">
        These terms and conditions are governed by and construed in accordance with the laws of MI,
        USA and you irrevocably submit to the exclusive jurisdiction of the courts in that State or
        location.
      </p>

      <div class="copyright text-sm text-gray-medium">Skill Tree Development, LLC | Heroes Profile</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiTermsOfService',
  props: {
    csrf: String,
    reviewrequired: {
      type: Boolean,
      default: false,
    },
  },
  data(){
    return {
      infoText: "The terms your API account and API key are used under.",
    }
  },
}
</script>
