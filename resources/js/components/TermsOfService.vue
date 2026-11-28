<template>
  <doc-page
    :heading="$t('Terms of Service')"
    :subheading="$t('Last updated: November 2026')"
    :sections="[
      { id: 'acceptance', title: $t('1. Acceptance of Terms') },
      { id: 'blizzard', title: $t('2. Not Affiliated with Blizzard Entertainment') },
      { id: 'data-source', title: $t('3. Data Source') },
      { id: 'profiles', title: $t('4. Player Profiles and Privacy') },
      { id: 'leaderboards', title: $t('5. Leaderboards and Account Bans') },
      { id: 'acceptable-use', title: $t('6. Acceptable Use') },
      { id: 'warranties', title: $t('7. Disclaimer of Warranties') },
      { id: 'liability', title: $t('8. Limitation of Liability') },
      { id: 'changes', title: $t('9. Changes to These Terms') },
      { id: 'contact', title: $t('10. Contact') }
    ]"
    :links="[
      { text: $t('Privacy Policy'), href: '/Privacy/Policy' },
      { text: $t('Heroes Profile API Terms of Service'), href: '/Api/Terms' },
      { text: $t('FAQ'), href: '/FAQ' },
    ]"
    :footer="$t('Skill Tree Development, LLC | Heroes Profile')"
  >
    <template #notice><legal-translation-notice></legal-translation-notice></template>
    <template #acceptance>
      <p>
        {{ $t('By accessing or using Heroes Profile at heroesprofile.com (the "Site"), you agree to be bound by these Terms of Service. If you do not agree, please do not use the Site. Heroes Profile is operated by Skill Tree Development, LLC.') }}
      </p>
    </template>
    <template #blizzard>
      <p>
        {{ $t('Heroes Profile is a fan-made, community-driven project. We are not affiliated with, endorsed by, or in any way officially connected to Blizzard Entertainment or Heroes of the Storm. All game data, hero names, and related assets are the property of Blizzard Entertainment.') }}
      </p>
    </template>
    <template #data-source>
      <p>
        {{ $t('All statistics and player data displayed on this Site are derived from replay files voluntarily uploaded by the community through the') }}
        <a class="link" href="/Api">{{ $t('Heroes Profile API') }}</a>{{ $t('. By uploading replays, users grant Heroes Profile permission to store, process, and display the data contained in those replays. Replay uploads are subject to the') }}
        <a class="link" href="/Api/Terms">{{ $t('Heroes Profile API Terms of Service') }}</a>.
      </p>
    </template>
    <template #profiles>
      <p>
        {{ $t('Player profiles are generated automatically from uploaded replay data and are publicly visible by default. Players who wish to hide their profile may set their account visibility to Private in their') }}
        <a class="link" href="/Profile/Settings">{{ $t('Profile Settings') }}</a>
        {{ $t('after logging in with Battle.net.') }}
      </p>
    </template>
    <template #leaderboards>
      <p>
        {{ $t('Participation in Heroes Profile leaderboards is a privilege, not a right. Heroes Profile reserves the right to remove any account from leaderboard rankings at our sole discretion, including accounts found to be cheating, exploiting, or otherwise acting in bad faith. Leaderboard bans do not affect access to the rest of the Site.') }}
      </p>
    </template>
    <template #acceptable-use>
      <p>{{ $t('You agree not to:') }}</p>
      <ul class="flex flex-col gap-2 text-white text-sm">
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Scrape, crawl, or systematically extract data from the Site using bots or automated scripts') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Attempt to reverse-engineer, copy, or reproduce Site code or database contents') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Use data from this Site to harass or harm other players') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Use data from this Site to link accounts to each other or to a person, such as matching a smurf or alternate account to a main account, or a BattleTag to a Discord account, social media account or real-world identity') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Use data from this Site to build profiles, lists or watch lists of particular players, or to monitor their games') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Use data from this Site to name, accuse or expose players for cheating, botting, win-trading, account sharing or any other misconduct. Report suspected misconduct to Blizzard') }}</span></li>
        <li class="flex items-start gap-3 rounded bg-surface-1 px-4 py-2.5"><span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-red/20 text-lred flex items-center justify-center"><basil-icon name="cross" class="text-xs"></basil-icon></span><span>{{ $t('Attempt to circumvent any rate limiting, access controls, or security measures') }}</span></li>
      </ul>
      <p class="flex items-center gap-3 rounded bg-quest/10 border-l-4 border-quest px-4 py-3 text-sm font-semibold text-white"><basil-icon name="info" class="shrink-0 text-lg"></basil-icon>
        {{ $t('Data on this Site cannot prove that a player has cheated or broken any rule, and must not be presented as if it could.') }}
      </p>
      <p>
        {{ $t('Violation of these rules may result in your access to the Site being restricted or terminated.') }}
      </p>
    </template>
    <template #warranties>
      <p>
        {{ $t('The Site and all data provided are offered on an "as is" basis without warranties of any kind. We do not guarantee the accuracy, completeness, or timeliness of any statistics or player data. Data may be incomplete, delayed, or affected by changes made by Blizzard Entertainment to Heroes of the Storm.') }}
      </p>
    </template>
    <template #liability>
      <p>
        {{ $t('To the fullest extent permitted by law, Skill Tree Development, LLC shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of or inability to use the Site, even if we have been advised of the possibility of such damages.') }}
      </p>
    </template>
    <template #changes>
      <p>
        {{ $t('We may update these Terms of Service at any time. Continued use of the Site after changes are posted constitutes your acceptance of the revised terms.') }}
      </p>
    </template>
    <template #contact>
      <p>
        {{ $t('If you have any questions about these Terms, please contact us via the') }}
        <a class="link" href="/Contact">{{ $t('Contact page') }}</a>
        {{ $t('or email') }} <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>.
      </p>
        <custom-button class="self-start font-semibold" href="/Contact" text="Contact us" alt="Contact us" color="teal"></custom-button>
    </template>
  </doc-page>
</template>

<script>
export default {
  name: 'TermsOfService',
}
</script>
