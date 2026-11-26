<template>
  <doc-page
    :heading="$t('Streamer Code of Conduct')"
    :subheading="$t('For channels listed on Heroes Profile · Version :version', { version: version })"
    :sections="[
      { id: 'stream', title: $t('On your stream and in your chat') },
      { id: 'twitch', title: $t('On Twitch and in game') },
      { id: 'broken', title: $t('What happens if these are broken') },
      { id: 'changes', title: $t('Changes') },
    ]"
    :links="[
      { text: $t('Streamers on Twitch'), href: '/Twitch' },
      { text: $t('Terms of Service'), href: '/Terms/Of/Service' },
      { text: $t('Contact'), href: '/Contact' },
    ]"
    :footer="$t('Version :version', { version: version }) + ' · ' + $t('Skill Tree Development, LLC | Heroes Profile')"
  >
    <template #intro>
      <div class="flex items-center gap-4 rounded bg-surface border-l-4 border-[#9145ff] px-5 py-4">
        <span class="shrink-0 w-10 h-10 rounded bg-[#9145ff]/20 text-[#9145ff] flex items-center justify-center"><i class="fas fa-play"></i></span>
        <p>{{ $t('Listing your channel on the Heroes Profile streamer page is optional. If you choose to be listed, you agree to the following while your listing is active.') }}</p>
      </div>
    </template>
    <template #stream>
      <ul class="flex flex-col gap-2 text-white text-sm">
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No racism, or hateful conduct based on ethnicity, nationality or religion.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No sexism or misogyny, and no hateful conduct based on sex or gender.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No homophobia, transphobia, or hateful conduct based on sexual orientation or gender identity.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No hateful conduct based on disability, age or any other protected characteristic.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No harassment, threats, or targeting of other players, streamers or viewers.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('No slurs, whether aimed at someone or not.') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-teal/20 text-lteal flex items-center justify-center"><basil-icon name="check" class="text-xs"></basil-icon></span><span>{{ $t('Moderate your chat so that it follows these rules too.') }}</span></li>
      </ul>
    </template>
    <template #twitch>
      <ul class="flex flex-col gap-2 text-white text-sm">
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-teal/20 text-lteal flex items-center justify-center"><basil-icon name="check" class="text-xs"></basil-icon></span><span>Follow <a href="https://safety.twitch.tv/s/article/Community-Guidelines" target="_blank" rel="noopener" class="link">{{ $t("Twitch's Community Guidelines") }}</a>.</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-teal/20 text-lteal flex items-center justify-center"><basil-icon name="check" class="text-xs"></basil-icon></span><span>{{ $t("Follow Blizzard's Terms of Use: no cheating, exploits, account sharing or win trading.") }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t("Do not use the extension's player information to harass anyone in your games.") }}</span></li>
      </ul>
    </template>
    <template #broken>
      <p>
          Heroes Profile can hide a listing, or switch the extension off for a channel, at its discretion.
          Hiding a listing does not cancel your API plan. If you think a listing was hidden in error,
          write to <a href="mailto:zemill@heroesprofile.com" class="link">zemill@heroesprofile.com</a>.
        </p>
    </template>
    <template #changes>
      <p>
          {{ $t('If these rules change you will be asked to accept the new version on your') }}
          <a href="/Api/Account#twitch" class="link">{{ $t('Twitch extension settings') }}</a> before your
          channel is listed again.
        </p>
    </template>
  </doc-page>
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
