<template>
  <div>
    <page-heading heading="API Privacy Policy" :infoText1="infoText"></page-heading>

    <div class="mx-auto max-w-[1000px] px-4 mt-6 pb-16">
      <p class="text-sm text-gray-medium mb-6">Last updated: September 2026</p>

      <p class="mb-6">
        Skill Tree Development, LLC (referred to as Heroes Profile in this document) operates the
        Heroes Profile API. This policy covers the API developer portal and API accounts. Cookies
        and analytics on the rest of www.heroesprofile.com are covered by the
        <a class="link" href="/Privacy/Policy">site privacy policy</a>.
      </p>

      <h3 class="text-xl mb-2">1. Account Information</h3>
      <p class="mb-3">
        When registering for an account, we store a minimal amount of information from you. This
        includes your name and contact email. We do not store any credit card or payment
        information. Payment, billing address collection and all credit card processing is handled
        by Stripe's API.
      </p>
      <p class="mb-6">
        We hold this information so that we can operate your account, authenticate you, take payment
        and contact you about the account. We keep it for as long as your account exists, and for up
        to 12 months after you close it so that we can deal with billing questions and abuse. Invoice
        records are kept for as long as tax and accounting rules require.
      </p>

      <h3 class="text-xl mb-2">2. API Keys</h3>
      <p class="mb-6">
        Keys are stored as a one-way hash. The key itself is shown to you once, when it is created,
        and cannot be recovered afterwards by us or by you. If you lose a key, revoke it and create
        another.
      </p>

      <h3 class="text-xl mb-2">3. Usage Tracking</h3>
      <p class="mb-3">
        We record the calls made by each key in order to enforce the per-minute and per-week limits
        of your plan, and to see which endpoints are under load. For each call we record the key it
        was made with, the endpoint, the time, and the calling IP address. IP addresses are recorded
        because rate limiting and abuse handling cannot work without them, including for
        unauthenticated callers who have no key.
      </p>
      <p class="mb-6">
        Usage records are kept for 90 days, other than aggregate counts, which we keep for longer so
        that quota and billing history stay correct. We don't sell any data to third parties.
      </p>

      <h3 class="text-xl mb-2">4. Cookies</h3>
      <p class="mb-6">
        The developer portal sets a session cookie so that you stay signed in while you use it.
        Calls to the API itself are authenticated by key and set no cookies.
      </p>

      <h3 class="text-xl mb-2">5. Who Else Processes This Data</h3>
      <p class="mb-3">
        We use a small number of providers to run the service. They process account data on our
        behalf and only as needed to provide their part of it:
      </p>
      <ul class="list-disc ml-6 mb-6 space-y-1">
        <li>Stripe — payment, billing address collection and card processing</li>
        <li>Google Cloud — hosting, databases and file storage</li>
        <li>Mailgun — account, billing and password reset email</li>
      </ul>

      <h3 class="text-xl mb-2">6. Data Served By The API</h3>
      <p class="mb-3">
        Statistics and profile data served by the API come from replay files voluntarily uploaded by
        the community, which we then store and aggregate in our database. Most of what the API
        returns is aggregate statistics that identify no one. The rest — battletags, player profiles,
        match history and per-player match performance — is tied to a named account.
      </p>
      <p class="mb-3">
        Players can hide their profile by setting it to private in
        <a class="link" href="/Profile/Settings">Profile Settings</a>. The API will not serve data
        for a private player: player endpoints refuse the request, and match endpoints remove the
        player from the response.
      </p>
      <p class="mb-6">
        We do not link your API account information with battletags or with anything else returned by
        the API. Your account and the data you retrieve are held separately.
      </p>

      <h3 class="text-xl mb-2">7. Your Responsibilities As An API Subscriber</h3>
      <p class="mb-3">
        When you retrieve player data through the API and display or store it, you decide why and how
        that data is used on your own surfaces. You are not acting on our behalf when you do, and we
        are not responsible for what you do with it. Meeting whatever obligations apply to your own
        project is down to you.
      </p>
      <p class="mb-6">
        The <a class="link" href="/Api/Terms">API terms of service</a> also require you to poll our
        privacy change feed and to remove a player's data within 24 hours of them going private. This
        policy describes what we do with data; it does not cover what you do with it once you have
        retrieved it.
      </p>

      <h3 class="text-xl mb-2">8. Security And Breaches</h3>
      <p class="mb-6">
        We take reasonable measures to protect account data, including hashing API keys and
        passwords, and processing payment entirely through Stripe so that card details never reach
        our servers. No system is perfectly secure. If a breach affects your account data we will
        tell you, and any regulator we are required to tell, without undue delay.
      </p>

      <h3 class="text-xl mb-2">9. Changes To This Policy</h3>
      <p class="mb-6">
        We may update this policy. The date at the top of this page shows when it last changed. Where
        a change materially affects how we handle your account data we will tell you by email.
      </p>

      <h3 class="text-xl mb-2">10. Contact</h3>
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
