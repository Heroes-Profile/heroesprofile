<template>
  <div>
    <page-heading :heading="'FAQ'" :infoText1="'Frequently asked questions about Heroes Profile. Can\'t find your answer? Ask us below.'"></page-heading>

    <div class="mx-auto max-w-[1000px] px-4 mt-6">

      <!-- Search -->
      <div class="mb-8">
        <input
          v-model="search"
          type="text"
          placeholder="Search FAQ..."
          class="form-control search-input w-full text-black"
        />
      </div>

      <!-- No results -->
      <div v-if="filteredCategories.length === 0" class="text-center text-gray-400 py-10">
        No results found for "{{ search }}". Try a different keyword or ask us below.
      </div>

      <!-- FAQ Categories -->
      <div v-for="category in filteredCategories" :key="category.name" class="mb-8">
        <h2 class="bg-teal px-4 py-3 rounded-t-lg text-lg font-semibold">{{ category.name }}</h2>
        <div class="bg-lighten rounded-b-lg overflow-hidden">
          <div
            v-for="(item, index) in category.items"
            :key="index"
            class="border-b border-gray-700 last:border-b-0"
          >
            <button
              @click="toggle(category.name, index)"
              class="w-full text-left px-6 py-4 flex justify-between items-center hover:bg-white/5 transition-colors"
            >
              <span class="font-medium pr-4">{{ item.q }}</span>
              <span class="shrink-0 text-teal-400 text-xl leading-none">
                {{ isOpen(category.name, index) ? '−' : '+' }}
              </span>
            </button>
            <div v-if="isOpen(category.name, index)" class="px-6 pb-5 text-gray-300 leading-relaxed">
              <div v-html="item.a"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ask a Question -->
      <div class="mt-12 mb-8">
        <h2 class="bg-teal px-4 py-3 rounded-t-lg text-lg font-semibold">Still have a question?</h2>
        <div class="bg-lighten rounded-b-lg p-6">
          <p class="text-gray-300 mb-4">
            Can't find what you're looking for? Send us a message and we'll get back to you.
            You can also email us directly at
            <a href="mailto:Zemill@heroesprofile.com" class="link">Zemill@heroesprofile.com</a>.
          </p>

          <form @submit.prevent="submitQuestion" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block mb-1 text-sm text-gray-300">Battletag</label>
                <input
                  v-model="formData.battletag"
                  type="text"
                  class="form-control search-input w-full text-black"
                  required
                />
              </div>
              <div>
                <label class="block mb-1 text-sm text-gray-300">Email</label>
                <input
                  v-model="formData.email"
                  type="email"
                  class="form-control search-input w-full text-black"
                  required
                />
              </div>
            </div>
            <div>
              <label class="block mb-1 text-sm text-gray-300">Your Question</label>
              <textarea
                v-model="formData.message"
                class="form-control search-input w-full text-black"
                rows="4"
                required
              ></textarea>
            </div>

            <!-- Honeypot -->
            <input type="text" name="website" v-model="formData.website" style="display:none!important;visibility:hidden!important;position:absolute!important;left:-9999px!important;" tabindex="-1" autocomplete="off">

            <div class="flex items-center gap-4">
              <button
                :disabled="isLoading"
                type="submit"
                class="transition-colors text-white rounded text-center bg-blue hover:bg-lblue py-2 px-6"
              >
                {{ isLoading ? 'Sending...' : 'Send Question' }}
              </button>
              <span v-if="emailSent" class="text-teal-400">Message sent! We'll be in touch.</span>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'FAQ',
  props: {
    recaptchaSiteKey: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      search: '',
      openItems: {},
      isLoading: false,
      emailSent: false,
      formData: {
        battletag: '',
        email: '',
        message: '',
        website: '',
        recaptcha_token: '',
      },
      categories: [
        {
          name: 'Getting Started',
          items: [
            {
              q: 'What is Heroes Profile?',
              a: 'Heroes Profile is a community-built statistics site for Heroes of the Storm. We track player profiles, global hero win rates, talent builds, matchups, draft data, leaderboards, and much more — all based on replay files uploaded by the community. The site launched in June 2018 and is run by Zemill and a small team of volunteers.',
            },
            {
              q: 'How do I find my player profile?',
              a: 'Use the search bar at the top of the site and type in your Battletag (e.g. <code>Zemill#1940</code>). Once found, you can view your match history, hero stats, MMR, maps, matchups, and more. Make sure you\'ve uploaded your replays first so your data appears.',
            },
            {
              q: 'Is Heroes Profile free to use?',
              a: 'Yes, Heroes Profile is completely free. We are supported by the community through <a href="https://www.patreon.com/heroesprofile" target="_blank" class="link">Patreon</a>. Patreon supporters receive an ad-free experience and special site flair.',
            },
          ],
        },
        {
          name: 'Uploading Replays',
          items: [
            {
              q: 'How do I get my stats onto Heroes Profile?',
              a: 'You need to upload your replay files. The easiest way is to download the <a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploaderSetup.exe" class="link">Heroes Profile Uploader</a> which runs in the background and automatically uploads replays after each game. You can also manually upload replays at <a href="https://api.heroesprofile.com/upload" target="_blank" class="link">api.heroesprofile.com/upload</a>. After uploading, wait 5–10 minutes for processing.',
            },
            {
              q: 'Is the Heroes Profile Uploader safe?',
              a: 'Yes. The uploader is completely open source — you can review the full source code on GitHub: <a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader" target="_blank" class="link">HeroesProfile.Uploader</a>. It does not contain ads, bloatware, or tracking beyond basic page analytics. If you prefer, you can upload replays manually instead.<br><br>Some antivirus tools or browsers may flag the installer as suspicious. This is a false positive caused by the executable not being signed with a paid code-signing certificate — not because it contains malware. You can verify this yourself by reviewing the open source code. If you\'re uncomfortable, use the manual upload option at <a href="https://api.heroesprofile.com/upload" target="_blank" class="link">api.heroesprofile.com/upload</a> instead.',
            },
            {
              q: 'Why aren\'t all of my games showing up?',
              a: 'Heroes Profile only contains games that have been uploaded to our system. If you haven\'t uploaded your replays, or only recently started uploading, older games won\'t appear. You can upload past replay files manually at <a href="https://api.heroesprofile.com/upload" target="_blank" class="link">api.heroesprofile.com/upload</a>, or use the <a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploaderSetup.exe" class="link">Heroes Profile Uploader</a> to auto-upload going forward. Replay files are stored by Heroes of the Storm in your Documents folder.',
            },
            {
              q: 'Can I upload old replays, and will that affect my MMR?',
              a: 'Yes, you can upload old replays. However, the order you upload them does matter for MMR. MMR systems give larger adjustments early on as they try to place you where you belong. If you upload your recent (higher win rate) games before your older (lower win rate) games, your MMR may end up higher than if you uploaded them chronologically. For the most accurate MMR, upload all replays in the order they were played.',
            },
            {
              q: 'Does the uploader work on macOS or Linux?',
              a: 'Yes. We have an Electron-based uploader that supports macOS and Linux. See our <a href="https://github.com/Heroes-Profile/heroesprofile-electron-uploader" target="_blank" class="link">electron uploader on GitHub</a> for downloads and instructions.',
            },
            {
              q: 'How do I make my profile private?',
              a: 'Log in with your Battle.net account, go to <a href="/Profile/Settings" class="link">Profile Settings</a>, and set your Account Visibility to Private. Your Battletag will still appear in other players\' match history (since it is part of the replay file), but your individual profile page will not be publicly accessible.',
            },
          ],
        },
        {
          name: 'MMR & Rankings',
          items: [
            {
              q: 'How does MMR work on Heroes Profile?',
              a: 'Blizzard does not include MMR or rank data in replay files, so we cannot read your official in-game rank. Instead, we calculate our own MMR based on the wins and losses in your uploaded replays. This is an approximation — the more games you upload, the more accurate it becomes. New game modes (like when Storm League launched) start everyone fresh, so early MMR values have high uncertainty.',
            },
            {
              q: 'Why doesn\'t my Heroes Profile MMR match my in-game rank?',
              a: 'Our MMR is an independent calculation based only on the replays uploaded to our system. It is not sourced from Blizzard\'s servers and does not reflect your official in-game ranking. The more games you upload, the closer it will converge to your actual skill level. If you\'re new to uploading, play more games and the value will stabilize.',
            },
            {
              q: 'What do the league tiers on global stats pages represent?',
              a: 'On global stats pages, league tiers (Bronze, Silver, Gold, Platinum, Diamond, Master) are based on our Heroes Profile MMR calculation. Players whose MMR falls in a certain range are grouped into that tier. We use the tier splits provided by Blizzard as a reference for where ranges should fall. This means roughly 7% of our players are in Bronze, matching Blizzard\'s intended distribution.',
            },
            {
              q: 'What are the league tier distributions on Heroes Profile?',
              a: 'The league tiers on Heroes Profile are based on the following population distributions:<br><br><div style="display:inline-block;min-width:220px;border:1px solid rgba(255,255,255,0.15);border-radius:6px;overflow:hidden;font-size:0.875rem;"><div style="display:flex;background:rgba(255,255,255,0.1);font-weight:600;"><span style="flex:1;padding:8px 14px;">Tier</span><span style="width:120px;padding:8px 14px;text-align:right;">% of Players</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Bronze</span><span style="width:120px;padding:8px 14px;text-align:right;">12%</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Silver</span><span style="width:120px;padding:8px 14px;text-align:right;">23%</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Gold</span><span style="width:120px;padding:8px 14px;text-align:right;">26%</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Platinum</span><span style="width:120px;padding:8px 14px;text-align:right;">22%</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Diamond</span><span style="width:120px;padding:8px 14px;text-align:right;">12%</span></div><div style="display:flex;border-top:1px solid rgba(255,255,255,0.1);"><span style="flex:1;padding:8px 14px;">Master</span><span style="width:120px;padding:8px 14px;text-align:right;">5%</span></div></div><br><br>These distributions are used to set the MMR thresholds for each tier. Players with fewer than 50 games played are placed in an "Unknown" tier (sometimes called Wood) until the system has enough data to place them accurately.',
            },
            {
              q: 'Why are MMR recalculations done periodically?',
              a: 'We periodically recalculate MMR across all uploaded games to improve accuracy. As our algorithms improve and more data is uploaded, recalculation gives all players a more accurate rating. We announce these recalculations when they happen — check our Reddit posts or GitHub for updates.',
            },
            {
              q: 'Why did my MMR go down after a win (or up after a loss)?',
              a: 'Heroes Profile MMR is not an infinitely scaling metric — it\'s a measure of your skill relative to everyone else. If your MMR dropped after a win, it means the system detected you were playing against players with significantly lower MMR than yours. The algorithm sees a high-MMR player winning against low-MMR opponents and interprets that as a sign your current rating may be too high, so it nudges it down slightly. The reverse is also true — losing to much higher-MMR players can push your rating up. This is by design: the system is always trying to find the bracket you truly belong in, not just reward you for winning.',
            },
            {
              q: 'What is Hero MMR?',
              a: 'Hero MMR is a per-hero MMR rating. Instead of a single overall rating, you have a different MMR for each hero you play. This rating compares you against other players\' performance on that same hero. Being a high-level Storm League player doesn\'t automatically mean you have a high Hero MMR on a hero you rarely play.',
            },
          ],
        },
        {
          name: 'Global Stats & Data',
          items: [
            {
              q: 'How accurate is the data on Heroes Profile?',
              a: 'The data is only as accurate as the number of replays uploaded to our system. We do not have access to every game played — only games where players have used our uploader or submitted replays manually. That said, we consistently have one of the largest replay databases in the community, with tens of millions of games.',
            },
            {
              q: 'Why are mirror matches excluded from matchup win rates?',
              a: 'When a hero faces itself in a mirror match, the result is always one win and one loss for that hero in the same game. Including mirrors would always push the average win rate toward 50% for every hero, giving you no useful information. Excluding them lets the matchup data actually reflect meaningful competitive dynamics.',
            },
            {
              q: 'How does the Performance Score work?',
              a: 'The Performance Score on match pages is calculated by taking each stat from a game (damage, healing, kills, etc.), ranking all 10 players in that game from best to worst, and assigning a score based on rank position. It is a relative measure within that single game — not a global benchmark.',
            },
            {
              q: 'What do the time frame filters mean (major patch vs. minor patch)?',
              a: 'Major patches on Heroes Profile generally align with hero releases or major reworks — when Blizzard changes the first digit after the decimal (e.g. 2.44 → 2.45). Minor patches are smaller balance updates. You can filter global stats to specific patches or date ranges to see how the meta evolved over time.',
            },
            {
              q: 'Why is global data sometimes empty after a new patch?',
              a: 'Right after Blizzard pushes a patch, there isn\'t enough uploaded data yet for that patch to display meaningful results. Broaden your timeframe filter to include multiple patches, or check back after a few days once more games have been uploaded.',
            },
            {
              q: 'What is "Win Chance" on talent builds, and how is it different from win rate?',
              a: 'Win Chance is our calculated probability that a full talent build leads to a win, from level 1 through level 20. It is not a simple win rate. Because many games end before level 20, we cannot always see the complete build a player intended to take. We use backend logic to infer what build a player was heading toward based on the talents they did pick, and we factor in both games that reached level 20 and games that did not. This gives a more accurate picture of a build\'s overall viability than just counting wins in completed games. You can explore build Win Chance on the <a href="/Global/Talents" class="link">Global Talents</a> page and the <a href="/Global/Talents/Builder" class="link">Talent Builder</a>.',
            },
            {
              q: 'What does the "Influence" stat mean?',
              a: 'Influence is a value between -1000 and 1000 that measures how much a hero actually influences the outcome of games. It combines win rate, pick rate, and games played, then scales the result across that range. A hero with a high win rate but very few games played will have a lower influence score than a hero with a slightly lower win rate but a much larger sample of games — because the second hero is actually shaping more matches. Think of it as win rate weighted by relevance.',
            },
            {
              q: 'What is the Talent Builder?',
              a: 'The <a href="/Global/Talents/Builder" class="link">Talent Builder</a> lets you construct a specific talent build and see win rate data for exactly that combination. It\'s great for comparing two builds head-to-head with real game data.',
            },
          ],
        },
        {
          name: 'Site Features',
          items: [
            {
              q: 'What is the PreMatch feature?',
              a: 'PreMatch is a feature built into the <a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploaderSetup.exe" class="link">Heroes Profile Uploader</a>. When enabled, it opens a browser page during the loading screen of a match showing information about all 10 players in your game — their stats, most-played heroes, MMR, and more. This helps you understand your teammates and opponents before the match starts. You can see an example at <a href="/PreMatch/Results/" class="link">PreMatch Results</a>.',
            },
            {
              q: 'What is the Friends & Foes page?',
              a: 'The Friends &amp; Foes page on your player profile shows your win rate when playing <em>with</em> or <em>against</em> specific players. For example, see <a href="/Player/Zemill/67280/1/FriendFoe" class="link">Zemill\'s Friends &amp; Foes</a>. Upload your replays and check the Friends &amp; Foes section of your own profile to see yours.',
            },
            {
              q: 'What is the Match Prediction Game?',
              a: 'The Match Prediction Game lets you look at a draft and try to predict which team will win before seeing the result. It\'s a fun way to test your game knowledge and see how your predictions compare to the actual outcome.',
            },
            {
              q: 'Does Heroes Profile support esports data?',
              a: 'Yes! We host data for several amateur and community leagues including NGS (Next Generation Series), CCL (Community Clash League), Masters Clash, and others. You can find these under the <a href="/Esports" class="link">Esports</a> section of the site.',
            },
            {
              q: 'What is the Heroes Profile Drafter?',
              a: 'The <a href="https://drafter.heroesprofile.com/" target="_blank" class="link">Heroes Profile Drafter</a> is a drafting tool that uses Heroes Profile win rate data to help you evaluate compositions during the draft phase. It is not intended to be a perfect predictor — use it to supplement your draft knowledge and investigate how compositions perform. Documentation is available at <a href="https://drafter.heroesprofile.com/docs" target="_blank" class="link">drafter.heroesprofile.com/docs</a>.',
            },
            {
              q: 'Can I copy a talent build from the site into the game?',
              a: 'Yes. On Global Hero pages, the Single Match page, and several other places around the site you will find a "Copy Build" button next to talent builds. Clicking it copies the build to your clipboard in a format that Heroes of the Storm accepts, so you can paste it directly into the game\'s loadout screen.',
            },
          ],
        },
        {
          name: 'Leaderboards',
          items: [
            {
              q: 'What are the requirements to appear on the leaderboard?',
              a: 'To be eligible for a leaderboard, you must meet all three of these conditions:<ul class="list-disc list-inside mt-2 space-y-1"><li>Account level of 250 or higher</li><li>A win rate of 50% or greater for that season</li><li>A minimum number of games played — specifically 5 games per week (for Player leaderboards) or 2 games per week (for Hero/Role leaderboards) multiplied by the number of weeks since the season started. So in week 4, a Player leaderboard requires 20 games played that season.</li></ul>If fewer than 10 players meet the requirements, the games-played threshold is automatically reduced until at least 10 players qualify.',
            },
            {
              q: 'What is the Heroes Profile Rating and how is it calculated?',
              a: 'The HP Rating is a custom score used to rank players on the leaderboard. It is intentionally not a pure MMR ranking — a pure MMR leaderboard would quickly be dominated by smurfs and players who reach a high MMR and stop playing, making it stale and uninteresting.<br><br>Instead, HP Rating combines three factors: your <strong>MMR</strong>, your <strong>win rate</strong>, and your <strong>games played</strong> for the season — all scaled relative to other players on that leaderboard. Games played is capped relative to the top players so that spamming games alone cannot carry you to the top. The result rewards players who are actively playing, winning, and performing at a high level. You can see the exact formula by clicking "Show Leaderboard Requirements" on the <a href="/Global/Leaderboard" class="link">Leaderboard page</a>.',
            },
            {
              q: 'Why does Heroes Profile use HP Rating instead of just showing MMR?',
              a: 'A pure MMR leaderboard has a fundamental flaw: once a player reaches a high MMR, it barely changes. That means the same accounts would sit at the top of every leaderboard indefinitely — especially smurfs, who can artificially inflate MMR and then stop playing. HP Rating solves this by factoring in active play each season. If you stop playing, your ranking falls even if your MMR stays high. There are tangible things you can do to climb: win more games, play consistently throughout the season.',
            },
            {
              q: 'Why can I filter leaderboards by party size?',
              a: 'A 5-stack premade team has a significant coordination advantage over solo players. Putting them on the same leaderboard makes it hard for solo players to compete at the top. Filtering by party size (Solo, 2-Stack, 3-Stack, etc.) lets you see how you rank against players who queued under the same conditions as you.',
            },
            {
              q: 'When are leaderboards updated?',
              a: 'Leaderboards are recalculated daily, starting at 2am EST. The calculation takes roughly 12–14 hours to complete across all leaderboard types. If you played games today, they may appear on some leaderboards the same day, but check back the following afternoon to ensure all leaderboards are fully updated.',
            },
          ],
        },
        {
          name: 'Supporting Heroes Profile',
          items: [
            {
              q: 'How can I support Heroes Profile?',
              a: 'The best ways to support us are: <ul class="list-disc list-inside mt-2 space-y-1"><li>Subscribe on <a href="https://www.patreon.com/heroesprofile" target="_blank" class="link">Patreon</a> (any amount gets you ad-free + site flair)</li><li>Tell other players about the site — word of mouth is our biggest growth driver</li><li>Upload your replays consistently to help grow the dataset</li><li>Submit bug reports and feedback on <a href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank" class="link">GitHub</a></li></ul>',
            },
            {
              q: 'Is Heroes Profile open source?',
              a: 'Yes! The site code is publicly available on GitHub at <a href="https://github.com/Heroes-Profile/heroesprofile" target="_blank" class="link">Heroes-Profile/heroesprofile</a>. The uploader is also open source at <a href="https://github.com/Heroes-Profile/HeroesProfile.Uploader" target="_blank" class="link">HeroesProfile.Uploader</a>. Contributions and feedback are welcome.',
            },
            {
              q: 'How do I report a bug or request a feature?',
              a: 'Submit bug reports at <a href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank" class="link">GitHub Issues</a>. For feature requests and ideas, open a discussion at <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" class="link">GitHub Discussions</a>. You can also use the contact form below or email <a href="mailto:Zemill@heroesprofile.com" class="link">Zemill@heroesprofile.com</a>.',
            },
            {
              q: 'Does Heroes Profile have an API?',
              a: 'Yes, we provide a public API. Documentation is available at <a href="https://api.heroesprofile.com" target="_blank" class="link">api.heroesprofile.com</a>. The API has rate limits and is intended for reasonable use — not for bulk downloading the entire replay database.',
            },
          ],
        },
      ],
    };
  },
  mounted() {
    this.loadRecaptcha();
  },
  computed: {
    filteredCategories() {
      if (!this.search.trim()) return this.categories;
      const q = this.search.toLowerCase();
      return this.categories
        .map(cat => ({
          ...cat,
          items: cat.items.filter(
            item =>
              item.q.toLowerCase().includes(q) ||
              item.a.toLowerCase().includes(q)
          ),
        }))
        .filter(cat => cat.items.length > 0);
    },
  },
  methods: {
    toggle(catName, index) {
      const key = `${catName}-${index}`;
      this.openItems = { ...this.openItems, [key]: !this.openItems[key] };
    },
    isOpen(catName, index) {
      return !!this.openItems[`${catName}-${index}`];
    },
    loadRecaptcha() {
      if (!window.grecaptcha && this.recaptchaSiteKey) {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${this.recaptchaSiteKey}`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
      }
    },
    async getRecaptchaToken() {
      return new Promise((resolve) => {
        if (window.grecaptcha && window.grecaptcha.ready && this.recaptchaSiteKey) {
          window.grecaptcha.ready(() => {
            window.grecaptcha.execute(this.recaptchaSiteKey, { action: 'contact_form' })
              .then(resolve)
              .catch(() => resolve(null));
          });
        } else {
          resolve(null);
        }
      });
    },
    async submitQuestion() {
      this.isLoading = true;
      this.emailSent = false;
      try {
        const recaptchaToken = await this.getRecaptchaToken();
        this.formData.recaptcha_token = recaptchaToken;

        const response = await this.$axios.post('/api/v1/contact', {
          battletag: this.formData.battletag,
          email: this.formData.email,
          message: this.formData.message,
          website: this.formData.website,
          recaptcha_token: this.formData.recaptcha_token,
        });

        if (response.data === 'success') {
          this.emailSent = true;
          this.formData.battletag = '';
          this.formData.email = '';
          this.formData.message = '';
        }
      } catch (e) {
        // silent
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>
