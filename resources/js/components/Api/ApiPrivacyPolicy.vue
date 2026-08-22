<template>
  <div>
    <page-heading heading="API Privacy Policy" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1000px] px-4 mt-6 pb-16">
      <p class="text-sm text-gray-medium mb-6">Last updated: August 2026</p>

      <p class="mb-6">
        Skill Tree Development, LLC (referred to as Heroes Profile in this document) operates the
        Heroes Profile API. This policy covers the API developer portal and API accounts. Cookies
        and analytics on the rest of www.heroesprofile.com are covered by the
        <a class="link" href="/Privacy/Policy">site privacy policy</a>.
      </p>

      <h3 class="text-xl mb-2">1. Account Information</h3>
      <p class="mb-6">
        When registering for an account, we store a minimal amount of information from you. This
        includes your name and contact email. We do not store any credit card or payment
        information. Payment, billing address collection and all credit card processing is handled
        by Stripe's API.
      </p>

      <h3 class="text-xl mb-2">2. API Keys</h3>
      <p class="mb-6">
        Keys are stored as a one-way hash. The key itself is shown to you once, when it is created,
        and cannot be recovered afterwards by us or by you. If you lose a key, revoke it and create
        another.
      </p>

      <h3 class="text-xl mb-2">3. Usage Tracking</h3>
      <p class="mb-6">
        We record the calls made by each key in order to enforce the per-minute and per-week limits
        of your plan, and to see which endpoints are under load. We don't sell any data to third
        parties.
      </p>

      <h3 class="text-xl mb-2">4. Cookies</h3>
      <p class="mb-6">
        The developer portal sets a session cookie so that you stay signed in while you use it.
        Calls to the API itself are authenticated by key and set no cookies.
      </p>

      <h3 class="text-xl mb-2">5. Data Served By The API</h3>
      <p class="mb-6">
        Statistics and profile data served by the API come from replay files voluntarily uploaded by
        the community, which we then store and aggregate in our database. That data is not personal
        in nature. We do not link your account information with Battletags or with anything else
        returned by the API.
      </p>

      <h3 class="text-xl mb-2">6. Contact</h3>
      <p class="mb-6">
        If you have any questions or concerns, please contact us at <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>.
      </p>

      <div class="copyright text-sm text-gray-medium">Skill Tree Development, LLC | Heroes Profile</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiPrivacyPolicy',
  data(){
    return {
      infoText: "What we store for an API account, and what we do with it.",
    }
  },
}
</script>
