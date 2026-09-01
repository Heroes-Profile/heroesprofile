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

      <p class="text-sm text-gray-medium mb-6">Last updated: September 2026</p>

      <p class="mb-6">
        Skill Tree Development, LLC (referred to as Heroes Profile in this document) operates the
        Heroes Profile API. These terms cover the API and the developer portal. Use of the rest of
        www.heroesprofile.com is covered by the
        <a class="link" href="/Terms/Of/Service">site terms of service</a>.
      </p>

      <h3 class="text-xl mb-2">1. Terms</h3>
      <p class="mb-3">
        By accessing the developer portal at https://www.heroesprofile.com/Api and creating an account,
        you are agreeing to be bound by these terms of service, all applicable laws and regulations,
        and agree that you are responsible for compliance with any applicable local laws. If you do
        not agree with any of these terms, you are prohibited from using or accessing the API. The
        materials contained in this website are protected by applicable copyright and trademark law.
      </p>
      <p class="mb-6">
        You must be 18 or over to register for an API account. This requirement applies to API
        accounts, API keys and the developer portal. It does not apply to the keyless replay upload
        endpoints used by the Heroes Profile uploader, which need no account.
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
      <p class="mb-3">
        While your subscription is active and you are complying with these terms, Heroes Profile
        grants you a limited, non-exclusive, non-transferable, revocable licence to:
      </p>
      <ul class="list-disc ml-6 mb-3 space-y-1">
        <li>Call the API and retrieve data from it, within the limits of your plan.</li>
        <li>Store and cache the data you retrieve on your own systems.</li>
        <li>
          Publicly display that data, and analysis derived from it, on the surfaces you operate —
          your website, application, bot, overlay or stream — subject to the attribution
          requirements in section 4 and the privacy requirements in section 5.
        </li>
      </ul>
      <p class="mb-3">
        This licence is for display on your own surfaces only. It is not a licence to redistribute
        the data itself. You may not:
      </p>
      <ul class="list-disc ml-6 mb-6 space-y-1">
        <li>Expose Heroes Profile data through an API, feed, webhook or endpoint of your own.</li>
        <li>Offer the data as a bulk download, dataset, dump or export to anyone else.</li>
        <li>Sell, licence, sublicense, rent or otherwise transfer the data to a third party.</li>
        <li>
          Pass your access on. The licence is granted to you for the project you registered, and
          cannot be assigned or shared with another party without our written agreement.
        </li>
      </ul>
      <p class="mb-6">
        Aggregate statistics you compute from our data, and your own commentary, remain yours. This
        restriction is on passing on our data, not on your having a view about it.
      </p>

      <h3 class="text-xl mb-2">4. Attribution</h3>
      <p class="mb-3">
        Every page, screen, dataset, visualisation or other output that uses Heroes Profile data must
        be attributed to Heroes Profile. Attribution must:
      </p>
      <ul class="list-disc ml-6 mb-3 space-y-1">
        <li>
          Read "Data provided by Heroes Profile", or wording that states as plainly that the data
          comes from Heroes Profile.
        </li>
        <li>Include a working, visible link to https://www.heroesprofile.com/.</li>
        <li>
          Appear on the same page or screen as the data itself, and be visible without the reader
          having to open a menu, hover, scroll past unrelated content or visit a separate credits or
          about page.
        </li>
        <li>
          Accompany the data wherever it goes. If your surface offers the data to your own users as
          an image, embed, export or shared link, the attribution must travel with it.
        </li>
      </ul>
      <p class="mb-3">
        You may use the Heroes Profile name and logo for the purpose of this attribution. You may not
        use our name, logo or branding in a way that suggests Heroes Profile endorses, sponsors,
        partners with, operates or has reviewed your project, and you may not use them as, or as part
        of, your own product name, logo, domain or app icon.
      </p>
      <p class="mb-6">
        Attribution is a condition of the licence in section 3, not a courtesy. Data displayed
        without it is displayed without a licence.
      </p>

      <h3 class="text-xl mb-2">5. Player Privacy</h3>
      <p class="mb-3">
        Players can hide their Heroes Profile profile by setting it to private. The API will not
        serve data for a private player: player endpoints refuse the request, and match endpoints
        remove the player from the response. That protects players going forward, but it cannot
        reach data you retrieved and cached before they went private.
      </p>
      <p class="mb-3">Because you are permitted to cache, you take on the other half of it:</p>
      <ul class="list-disc ml-6 mb-3 space-y-1">
        <li>
          You must poll our privacy change feed at least once every 24 hours and act on what it
          returns.
        </li>
        <li>
          Within 24 hours of a player becoming private, you must stop displaying their data on every
          surface you operate and remove it from your caches, stores and derived records that
          identify them.
        </li>
        <li>
          You must honour a takedown request we pass on from a player within 7 days, whether or not
          it came through the feed.
        </li>
        <li>
          You must not use Heroes Profile data to re-identify a player who has gone private, to
          reconstruct a hidden profile, or to work around the privacy setting by any other route.
        </li>
      </ul>
      <p class="mb-6">
        Aggregate statistics that do not identify an individual player are not affected by a player
        going private.
      </p>

      <h3 class="text-xl mb-2">6. Acceptable Use</h3>
      <p class="mb-3">In order to use our API, you also agree to the following:</p>
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
          Our public endpoints are provided for general informational use only and are not intended
          for mass data aggregation, bulk extraction, or dataset mirroring.
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
          Your project may not directly compete with Heroes Profile — that is, it may not use our
          data to reproduce Heroes Profile itself as a substitute for it. For Developer Tier we
          reserve the right to deny, suspend, or remove access at any time, including after approval.
        </li>
        <li>Don't bully or harass others with our data.</li>
        <li>Do not reproduce our code or reverse-engineer our API — our code and database are our property.</li>
      </ul>

      <h3 class="text-xl mb-2">7. Your Users</h3>
      <p class="mb-6">
        You are responsible for how the people who use your project use the Heroes Profile data you
        show them. Your own terms must bind your users to at least the restrictions in sections 3, 4
        and 6 — in particular, no redistribution of the data, no scraping your surface to extract it,
        and no using it to harass anyone. If your users breach those restrictions, we will look to
        you.
      </p>

      <h3 class="text-xl mb-2">8. Blizzard Content</h3>
      <p class="mb-6">
        Heroes Profile is a fan-made project and is not affiliated with, endorsed by, or officially
        connected to Blizzard Entertainment or Heroes of the Storm. Hero names, map names, artwork,
        icons and other game assets are the property of Blizzard Entertainment. The licence in
        section 3 covers Heroes Profile data only and grants you no rights in anything belonging to
        Blizzard Entertainment. If your project uses Blizzard assets or names it is your
        responsibility to comply with Blizzard's own terms, and your project must carry the same
        statement that it is unaffiliated with Blizzard Entertainment.
      </p>

      <h3 class="text-xl mb-2">9. Termination</h3>
      <p class="mb-3">
        The licence in section 3 terminates automatically if you violate these terms, and may be
        terminated by Heroes Profile (Skill Tree Development, LLC) at any time. It also ends when
        your subscription ends, whether you cancel it or we do.
      </p>
      <p class="mb-6">
        Within 30 days of the licence ending you must stop displaying Heroes Profile data on every
        surface you operate and delete the data you retrieved from our API from your systems,
        including caches and backups, so far as your systems allow. Aggregate statistics you had
        already computed, which do not identify an individual player and from which our underlying
        data cannot be reconstructed, may be kept. Sections 5, 8, 10, 11 and 12 survive termination.
      </p>

      <h3 class="text-xl mb-2">10. Indemnification</h3>
      <p class="mb-6">
        You agree to indemnify and hold harmless Heroes Profile (Skill Tree Development, LLC), and
        anyone working on it, from any claim, demand, loss, liability or expense — including
        reasonable legal fees — arising out of your use of the API or the data, your project, the
        conduct of your users, or your breach of these terms. This includes claims brought by a
        player about how their data was displayed on your surface, and claims brought by a third
        party about intellectual property used in your project.
      </p>

      <h3 class="text-xl mb-2">11. Disclaimer</h3>
      <p class="mb-3">
        The data provided with the API and on our website are provided on an "as is" basis.
        Heroes Profile makes no warranties, expressed or implied, and hereby disclaims and negates all
        other warranties including, without limitation, implied warranties or conditions of
        merchantability, fitness for a particular purpose, or non-infringement of intellectual
        property or other violation of rights. We do not guarantee accurate or up-to-date data, and
        we do not guarantee any level of availability or uptime.
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

      <h3 class="text-xl mb-2">12. Limitations</h3>
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

      <h3 class="text-xl mb-2">13. Accuracy of Materials</h3>
      <p class="mb-6">
        The materials appearing on www.heroesprofile.com could include technical, typographical, or
        photographic errors. Heroes Profile (Skill Tree Development, LLC) does not warrant that any of
        the materials on its website are accurate, complete or current. Heroes Profile (Skill Tree
        Development, LLC) may make changes to the materials contained on its website at any time
        without notice. However Heroes Profile (Skill Tree Development, LLC) does not make any
        commitment to update the materials. We may change the functionality of the API at any time.
      </p>

      <h3 class="text-xl mb-2">14. Links</h3>
      <p class="mb-6">
        Heroes Profile (Skill Tree Development, LLC) has not reviewed all of the sites linked to its
        website and is not responsible for the contents of any such linked site. The inclusion of any
        link does not imply endorsement by Heroes Profile (Skill Tree Development, LLC) of the site.
        Use of any such linked website is at the user's own risk.
      </p>

      <h3 class="text-xl mb-2">15. Modifications</h3>
      <p class="mb-6">
        Heroes Profile (Skill Tree Development, LLC) may revise these terms of service at any time
        without notice. By using the API you are agreeing to be bound by the then current version of
        these terms of service. Where the change is material we may require you to accept the revised
        terms in the developer portal before you can carry on using your account.
      </p>

      <h3 class="text-xl mb-2">16. Governing Law</h3>
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
