<template>
  <div>
    <!-- Heading descriptions hidden site-wide for now; may be added back somewhere else on the page. :infoText1="infoText" -->
    <page-heading :heading="'Streamer Code of Conduct'"></page-heading>

    <div class="mx-auto max-w-[56.25rem] px-4 mt-6 mb-10">
      <div class="bg-lighten rounded p-6 text-sm space-y-4">
        <p>
          {{ $t('Listing your channel on the Heroes Profile streamer page is optional. If you choose to be listed, you agree to the following while your listing is active.') }}
        </p>

        <h2 class="text-lg">{{ $t('On your stream and in your chat') }}</h2>
        <ul class="list-disc list-inside space-y-1">
          <li>{{ $t('No racism, or hateful conduct based on ethnicity, nationality or religion.') }}</li>
          <li>{{ $t('No sexism or misogyny, and no hateful conduct based on sex or gender.') }}</li>
          <li>{{ $t('No homophobia, transphobia, or hateful conduct based on sexual orientation or gender identity.') }}</li>
          <li>{{ $t('No hateful conduct based on disability, age or any other protected characteristic.') }}</li>
          <li>{{ $t('No harassment, threats, or targeting of other players, streamers or viewers.') }}</li>
          <li>{{ $t('No slurs, whether aimed at someone or not.') }}</li>
          <li>{{ $t('Moderate your chat so that it follows these rules too.') }}</li>
        </ul>

        <h2 class="text-lg">{{ $t('On Twitch and in game') }}</h2>
        <ul class="list-disc list-inside space-y-1">
          <li>Follow <a href="https://safety.twitch.tv/s/article/Community-Guidelines" target="_blank" rel="noopener" class="link">{{ $t("Twitch's Community Guidelines") }}</a>.</li>
          <li>{{ $t("Follow Blizzard's Terms of Use: no cheating, exploits, account sharing or win trading.") }}</li>
          <li>{{ $t("Do not use the extension's player information to harass anyone in your games.") }}</li>
        </ul>

        <h2 class="text-lg">{{ $t('What happens if these are broken') }}</h2>
        <p>
          Heroes Profile can hide a listing, or switch the extension off for a channel, at its discretion.
          Hiding a listing does not cancel your API plan. If you think a listing was hidden in error,
          write to <a href="mailto:zemill@heroesprofile.com" class="link">zemill@heroesprofile.com</a>.
        </p>

        <h2 class="text-lg">{{ $t('Changes') }}</h2>
        <p>
          {{ $t('If these rules change you will be asked to accept the new version on your') }}
          <a href="/Api/Account#twitch" class="link">{{ $t('Twitch extension settings') }}</a> before your
          channel is listed again.
        </p>

        <p class="text-xs text-gray-medium">{{ $t('Version :version', { version: version }) }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TwitchGuidelines',
  props: {
    version: {
      type: Number,
      default: 1,
    },
  },
  data(){
    return {
      infoText: "The rules streamers agree to when they list their channel on Heroes Profile.",
    }
  },
}
</script>
