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

      <p class="text-sm text-gray-medium mb-6">Last updated: October 2026</p>

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
        A subscription is required in order to use the API, apart from the keyless replay upload
        endpoints described in section 1. Pricing is listed on the
        <a class="link" href="/Api">API home page</a>. We may change pricing or features of pricing
        tiers at any time, but will give you advance notice of 30 days via email if changes will
        affect your monthly subscription costs.
      </p>
      <p class="mb-3">
        Access we grant directly, or through a Patreon pledge, counts as a subscription for these
        terms. It starts when we grant it and ends when we withdraw it or the pledge stops.
      </p>
      <p class="mb-3">
        Before subscribing you must describe, on your account page, the project you will use the
        API for: its name, what it does and who uses it. That description is the project the
        licence in section 3 is granted for. Keep it accurate, and update it before your project
        changes significantly — for example before it becomes publicly available, or before you
        start charging its users. API calls from an account without a project description may be
        refused.
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
        <li>
          Charge for access to it. Putting Heroes Profile data, or statistics, analysis or features
          derived from it, behind a paywall, subscription, paid tier or any other fee requires our
          written agreement.
        </li>
        <li>
          Use it to build a competing data source. You may store the data in your own database to run
          the project you registered, but you may not use it to build, seed or fill a match database,
          replay collection or statistics service that offers features similar to Heroes Profile's.
          That includes combining our data with replays you collect yourself or through your own
          uploader, whether or not the result is ever made public. Research projects and personal
          projects that keep their own database for their own analysis are not affected.
        </li>
      </ul>
      <p class="mb-3">
        Statistics and analysis you compute from our data — win rates, tier lists, builds, matchups,
        ratings and the like — are covered by this licence in the same way as the data itself. You
        may display them while the licence is in force, and must stop as set out in section 9. Your own commentary
        and opinions remain yours. Research projects have different rules for published results,
        set out in section 10.
      </p>
      <p class="mb-6">
        Values Heroes Profile computes — MMR, hero and role MMR, league tiers and anything else
        produced by our rating and ranking systems — are covered in the same way. You may display
        them, but you may not use them to calibrate or seed a rating or ranking system of your own.
      </p>

      <h3 class="text-xl mb-2">4. Attribution</h3>
      <p class="mb-3">
        Every page, screen, visualisation or other output that uses Heroes Profile data must
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
          Be at least as prominent as the ordinary text around it. The attribution text must be no
          smaller than the page's main body text, in a colour that clearly contrasts with its
          background, and must not be styled as fine print or made less noticeable than the content
          around it.
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
          Within 24 hours of the privacy change feed reporting that a player has gone private, you
          must stop displaying their data on every
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
          You will not abuse our data or server resources. Each plan sets a limit on calls per minute,
          a weekly allowance for each endpoint, and a limit on active jobs (uncached calls your
          account has still running); if you abuse this, your account may be
          terminated.
        </li>
        <li>
          We measure the server and database time your calls use, including queries that keep
          running after a call has answered <code>202</code>. Usage that puts sustained load on the
          service may be treated as abuse even when it stays within your plan's limits.
        </li>
        <li>
          You will keep your API key private. It authenticates as your account, and calls made with
          it count against your allowance and are your responsibility.
        </li>
        <li>
          The keyless endpoints are provided for the Heroes Profile uploader and similar replay
          upload tools. They are not for collecting data, mass aggregation or mirroring; collecting
          data requires a subscription.
        </li>
        <li>
          Cache what you retrieve and serve your own users from your cache. Unless you are on
          Developer Tier, you may not call our API each time one of your users loads a page or
          clicks a button, passing their requests straight through to us. Accounts doing this may
          be terminated without refund.
        </li>
        <li>
          Developer Tier is intended for large dataset-driven analysis and similar high-scale use
          cases. Access requires independent
          <a class="link" href="/Api/DeveloperTier">review and approval of your project</a>
          by Heroes Profile. Approval is for the project described on your account page (section 2).
          A significant change to that project needs approval again, and we may ask you how you
          store and use the data.
        </li>
        <li>
          Your project may not compete with Heroes Profile. It may not be a website, app or service
          whose main purpose is to offer, in place of Heroes Profile, the statistics, profiles, match
          history or analysis Heroes Profile offers, using our data. That covers reproducing the site
          as a whole, and also presenting our statistics in a different way as a stats site of its
          own. Bots, overlays, tools and other projects that use our data as one part of something
          else are not affected. Projects that fall within this restriction need our agreement. We
          may give it informally, and we may withdraw it at any time by giving you 30 days' notice,
          after which you must stop the parts of your project that compete with Heroes Profile.
          For Developer Tier we reserve the right to deny, suspend, or remove access at any time,
          including after approval.
        </li>
        <li>Don't bully or harass others with our data.</li>
        <li>
          Do not reverse-engineer the API beyond its published documentation. The Heroes Profile
          site code is covered by its own licence, and our data is licensed to you only as set out
          in section 3.
        </li>
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
      <p class="mb-3">
        Within 30 days of the licence ending you must stop displaying Heroes Profile data, and the
        statistics and analysis derived from it, on every surface you operate, and delete the data
        you retrieved from our API from your systems, including caches. Backups must be deleted or
        allowed to expire within a further 30 days. If our data has been combined with data from
        other sources and cannot be separated, the combined data must be deleted. On request you
        must confirm in writing that the deletion is complete. Research projects may keep their
        data as set out in section 10.
      </p>
      <p class="mb-6">
        Sections 5, 8, 10, 11, 12 and 13 survive termination, except that once the licence ends you
        no longer have to poll the privacy change feed. Instead, you must not display any player's
        data, as set out above. The restriction on building a
        competing data source in section 3 and the restriction on competing with Heroes Profile in
        section 6 continue to apply for 12 months after the licence ends.
      </p>

      <h3 class="text-xl mb-2">10. Research Use</h3>
      <p class="mb-3">
        This section applies when the project description on your account page (section 2) says
        the project is non-commercial research, for example academic study, a thesis or
        dissertation, or a paper for publication. Non-commercial means the research, its data and
        its results are not sold or used in a paid product. Publishing in a journal, conference or
        book that charges its readers does not make research commercial. Where this section differs
        from sections 3, 4, 5 and 9, this section applies to your research.
      </p>
      <p class="mb-3">
        <strong>Published results.</strong> You may publish aggregate statistics and analysis
        derived from Heroes Profile data in papers, theses, preprints, posters and talks, and in
        the tables and figures they contain. These results may stay published permanently, and you
        may publish them after your licence has ended. They must not identify individual players,
        so battletags, account IDs and player-level rows must not appear in them.
      </p>
      <p class="mb-3">
        <strong>Attribution.</strong> In place of section 4, a research publication must cite
        Heroes Profile, with a link to https://www.heroesprofile.com/, as the source of the data
        in its data or methods section. Any table or figure built from the data must also credit
        Heroes Profile in its caption or source note.
      </p>
      <p class="mb-3">
        <strong>Replication material.</strong> You may publish your code, analysis scripts and
        aggregate results, including on OSF or a similar repository. You may not publish or share
        row-level data retrieved from the API, such as match, replay or player data, without our
        written agreement. Anyone reproducing your work can retrieve the data from the API under
        their own account.
      </p>
      <p class="mb-3">
        <strong>Private players.</strong> Data that you hold only for research and never display
        does not have to be deleted when a player goes private. Instead, within 24 hours of the
        privacy change feed reporting the change, you must replace that player's battletag, account
        ID and any other identifier with a random identifier, and keep no lookup table or any other
        means of reversing the replacement. Takedown requests are handled the same way, within 7
        days. Data you display on any surface remains fully subject to section 5.
      </p>
      <p class="mb-6">
        <strong>Keeping your data.</strong> When your subscription is cancelled or lapses, you may
        keep the data you have already retrieved in place of deleting it under section 9, and keep
        using it for non-commercial research. You must still never redistribute it, and the rules
        above on published results, attribution and replication material continue to apply. After
        the subscription ends you no longer have to follow the privacy change feed. If you
        subscribe again and resume pulling data, you must first check your retained data against
        the feed and handle any player who went private in the meantime, as set out above. This
        does not apply if we terminate your licence because you breached these terms. In that case
        section 9 applies in full.
      </p>

      <h3 class="text-xl mb-2">11. Indemnification</h3>
      <p class="mb-6">
        You agree to indemnify and hold harmless Heroes Profile (Skill Tree Development, LLC), and
        anyone working on it, from any claim, demand, loss, liability or expense — including
        reasonable legal fees — arising out of your use of the API or the data, your project, the
        conduct of your users, or your breach of these terms. This includes claims brought by a
        player about how their data was displayed on your surface, and claims brought by a third
        party about intellectual property used in your project.
      </p>

      <h3 class="text-xl mb-2">12. Disclaimer</h3>
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

      <h3 class="text-xl mb-2">13. Limitations</h3>
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

      <h3 class="text-xl mb-2">14. Accuracy of Materials</h3>
      <p class="mb-6">
        The materials appearing on www.heroesprofile.com could include technical, typographical, or
        photographic errors. Heroes Profile (Skill Tree Development, LLC) does not warrant that any of
        the materials on its website are accurate, complete or current. Heroes Profile (Skill Tree
        Development, LLC) may make changes to the materials contained on its website at any time
        without notice. However Heroes Profile (Skill Tree Development, LLC) does not make any
        commitment to update the materials. We may change the functionality of the API at any time.
      </p>

      <h3 class="text-xl mb-2">15. Links</h3>
      <p class="mb-6">
        Heroes Profile (Skill Tree Development, LLC) has not reviewed all of the sites linked to its
        website and is not responsible for the contents of any such linked site. The inclusion of any
        link does not imply endorsement by Heroes Profile (Skill Tree Development, LLC) of the site.
        Use of any such linked website is at the user's own risk.
      </p>

      <h3 class="text-xl mb-2">16. Modifications</h3>
      <p class="mb-3">
        Heroes Profile (Skill Tree Development, LLC) may revise these terms of service at any time
        without notice. By using the API you are agreeing to be bound by the then current version of
        these terms of service. Where the change is material we may require you to accept the revised
        terms in the developer portal before you can carry on using your account, and calls made with
        your API keys will be refused until you have.
      </p>
      <p class="mb-6">
        If we don't enforce any part of these terms, or allow a project for a time, that is not a
        waiver of our right to enforce it later, against you or anyone else.
      </p>

      <h3 class="text-xl mb-2">17. Governing Law</h3>
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
