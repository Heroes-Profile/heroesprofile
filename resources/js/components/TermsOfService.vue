<template>
  <div>
    <page-heading :heading="'Terms of Service'"></page-heading>

    <div class="max-w-[93.75rem] mx-auto mt-10 px-4">
      <legal-translation-notice></legal-translation-notice>
      <p>{{ $t('Last updated: November 2026') }}</p>
      <br/>

      <h3>{{ $t('1. Acceptance of Terms') }}</h3>
      <p>
        {{ $t('By accessing or using Heroes Profile at heroesprofile.com (the "Site"), you agree to be bound by these Terms of Service. If you do not agree, please do not use the Site. Heroes Profile is operated by Skill Tree Development, LLC.') }}
      </p>
      <br/>

      <h3>{{ $t('2. Not Affiliated with Blizzard Entertainment') }}</h3>
      <p>
        {{ $t('Heroes Profile is a fan-made, community-driven project. We are not affiliated with, endorsed by, or in any way officially connected to Blizzard Entertainment or Heroes of the Storm. All game data, hero names, and related assets are the property of Blizzard Entertainment.') }}
      </p>
      <br/>

      <h3>{{ $t('3. Data Source') }}</h3>
      <p>
        {{ $t('All statistics and player data displayed on this Site are derived from replay files voluntarily uploaded by the community through the') }}
        <a class="link" href="/Api">{{ $t('Heroes Profile API') }}</a>{{ $t('. By uploading replays, users grant Heroes Profile permission to store, process, and display the data contained in those replays. Replay uploads are subject to the') }}
        <a class="link" href="/Api/Terms">{{ $t('Heroes Profile API Terms of Service') }}</a>.
      </p>
      <br/>

      <h3>{{ $t('4. Player Profiles and Privacy') }}</h3>
      <p>
        {{ $t('Player profiles are generated automatically from uploaded replay data and are publicly visible by default. Players who wish to hide their profile may set their account visibility to Private in their') }}
        <a class="link" href="/Profile/Settings">{{ $t('Profile Settings') }}</a>
        {{ $t('after logging in with Battle.net.') }}
      </p>
      <br/>

      <h3>{{ $t('5. Leaderboards and Account Bans') }}</h3>
      <p>
        {{ $t('Participation in Heroes Profile leaderboards is a privilege, not a right. Heroes Profile reserves the right to remove any account from leaderboard rankings at our sole discretion, including accounts found to be cheating, exploiting, or otherwise acting in bad faith. Leaderboard bans do not affect access to the rest of the Site.') }}
      </p>
      <br/>

      <h3>{{ $t('6. Acceptable Use') }}</h3>
      <p>{{ $t('You agree not to:') }}</p>
      <ul class="list-disc ml-6 mt-2 space-y-1">
        <li>{{ $t('Scrape, crawl, or systematically extract data from the Site using bots or automated scripts') }}</li>
        <li>{{ $t('Attempt to reverse-engineer, copy, or reproduce Site code or database contents') }}</li>
        <li>{{ $t('Use data from this Site to harass or harm other players') }}</li>
        <li>
          {{ $t('Use data from this Site to link accounts to each other or to a person, such as matching a smurf or alternate account to a main account, or a BattleTag to a Discord account, social media account or real-world identity') }}
        </li>
        <li>{{ $t('Use data from this Site to build profiles, lists or watch lists of particular players, or to monitor their games') }}</li>
        <li>
          {{ $t('Use data from this Site to name, accuse or expose players for cheating, botting, win-trading, account sharing or any other misconduct. Report suspected misconduct to Blizzard') }}
        </li>
        <li>{{ $t('Attempt to circumvent any rate limiting, access controls, or security measures') }}</li>
      </ul>
      <p class="mt-2">
        {{ $t('Data on this Site cannot prove that a player has cheated or broken any rule, and must not be presented as if it could.') }}
      </p>
      <p class="mt-2">
        {{ $t('Violation of these rules may result in your access to the Site being restricted or terminated.') }}
      </p>
      <br/>

      <h3>{{ $t('7. Disclaimer of Warranties') }}</h3>
      <p>
        {{ $t('The Site and all data provided are offered on an "as is" basis without warranties of any kind. We do not guarantee the accuracy, completeness, or timeliness of any statistics or player data. Data may be incomplete, delayed, or affected by changes made by Blizzard Entertainment to Heroes of the Storm.') }}
      </p>
      <br/>

      <h3>{{ $t('8. Limitation of Liability') }}</h3>
      <p>
        {{ $t('To the fullest extent permitted by law, Skill Tree Development, LLC shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of or inability to use the Site, even if we have been advised of the possibility of such damages.') }}
      </p>
      <br/>

      <h3>{{ $t('9. Changes to These Terms') }}</h3>
      <p>
        {{ $t('We may update these Terms of Service at any time. Continued use of the Site after changes are posted constitutes your acceptance of the revised terms.') }}
      </p>
      <br/>

      <h3>{{ $t('10. Contact') }}</h3>
      <p>
        {{ $t('If you have any questions about these Terms, please contact us via the') }}
        <a class="link" href="/Contact">{{ $t('Contact page') }}</a>
        {{ $t('or email') }} <a class="link" href="mailto:zemill@heroesprofile.com">zemill@heroesprofile.com</a>.
      </p>
      <br/>

      <div class="copyright text-sm">{{ $t('Skill Tree Development, LLC | Heroes Profile') }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TermsOfService',
}
</script>
