<template>
  <div>
    <page-heading :heading="'Match Prediction Game'" :subheading="'Guess the winner from the draft'"></page-heading>

    <dynamic-banner-ad :patreon-user="patreonUser"></dynamic-banner-ad>

    <div class="max-w-[120rem] mx-auto px-4 md:px-8 flex flex-col gap-6">
      <!-- Game type and Next match -->
      <div class="bg-surface rounded-panel p-4 md:px-6 flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-col gap-2 min-w-0 max-w-[44rem]">
          <span v-if="user && practicemode" class="flex flex-wrap items-center gap-3">
            <span class="px-1.5 py-0.5 rounded-[0.25rem] bg-red/20 text-[0.625rem]">
              <span class="font-bold uppercase tracking-wider text-lred">{{ $t('Practice mode') }}</span>
              <span class="text-subtext"> · {{ $t(':count practices left', { count: practicesLeft }) }}</span>
            </span>
            <button type="button" class="text-xs text-subtext hover:text-white hover:underline" @click="skipPracticeOpen = true">{{ $t('Skip practice') }}</button>
          </span>
          <p class="text-sm text-subtext">{{ $t('How good are you at predicting the winner from the picks? Every match is a real game, so results are based on actual data.') }}</p>
          <span class="flex flex-wrap gap-x-4 gap-y-1">
            <a :href="leaderboardUrl" target="_blank" class="group/bt inline-flex items-center text-xs text-lteal"><span class="group-hover/bt:underline">{{ $t('See the leaderboard') }}</span><basil-icon name="arrow-small" class="text-xs ml-1"></basil-icon></a>
            <!-- Back to How it works and Your record -->
            <a v-if="user && (data || isLoading)" href="#" class="group/bt inline-flex items-center text-xs text-lteal" @click.prevent="showStats"><span class="group-hover/bt:underline">{{ $t('See your stats') }}</span><basil-icon name="arrow-small" class="text-xs ml-1"></basil-icon></a>
          </span>
        </div>
        <div class="flex flex-wrap items-end gap-3 max-sm:w-full">
          <div class="w-full sm:w-[18.75rem]">
            <single-select-filter :values="gametypes" :text="'Game type'" :compact="true" :nosearch="true" :placeholder="'Choose game type'" :defaultValue="gametype" @input-changed="handleInputChange"></single-select-filter>
          </div>
          <custom-button color="teal" class="font-semibold px-8 max-sm:flex-1" :ignoreclick="true" :disabled="disableFilterInput" :alt="'Next match'" @click="applyFilter">{{ $t('Next match') }}</custom-button>
        </div>
      </div>

      <!-- The result, once a team is picked -->
      <div v-if="userchoiceresult" :class="['rounded border p-4 md:px-6 flex flex-wrap items-center gap-4', guessedRight ? 'bg-teal/10 border-teal' : 'bg-red/10 border-red']">
        <span :class="['w-9 h-9 shrink-0 rounded-full flex items-center justify-center', guessedRight ? 'bg-teal' : 'bg-red']"><i :class="guessedRight ? 'fas fa-check' : 'fas fa-times'"></i></span>
        <div class="flex flex-col min-w-0 mr-auto">
          <span class="font-semibold">{{ guessedRight ? $t('Correct — Team :team won', { team: winnerTeam + 1 }) : $t('Wrong guess — Team :team won', { team: winnerTeam + 1 }) }}</span>
          <span class="text-xs text-subtext">{{ $t('You picked Team :team. Check the replay to see how the game went.', { team: userchoiceteam + 1 }) }}</span>
        </div>
        <div class="flex flex-wrap gap-3">
          <custom-button :href="'/Match/Single/' + userchoiceresult.replayID" :targetblank="true" class="font-semibold" :alt="'See replay'">{{ $t('See replay :replay', { replay: userchoiceresult.replayID }) }}</custom-button>
          <custom-button color="teal" class="font-semibold" :ignoreclick="true" :disabled="disableFilterInput" :alt="'Next match'" @click="applyFilter">{{ $t('Next match') }}</custom-button>
        </div>
      </div>

      <!-- Loading a match -->
      <div v-if="isLoading && !data" class="flex flex-col gap-6">
        <loading-component :compact="true" :textoverride="true">{{ $t('Loading match...') }}</loading-component>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <skeleton-box v-for="tile in 4" :key="tile" class="h-[3.75rem] rounded-control"></skeleton-box>
        </div>
        <div class="grid md:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-6 items-center">
          <skeleton-box class="h-[25rem] rounded-control"></skeleton-box>
          <span class="max-md:hidden w-8"></span>
          <skeleton-box class="h-[25rem] rounded-control"></skeleton-box>
        </div>
      </div>

      <!-- The match -->
      <template v-else-if="data">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <stat-box class="!m-0 !min-w-0" :title="'Map'" :value="$mapName(data.replayData.game_map)"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'HP rank'" :value="$t(data.rank)"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'Region'" :value="data.replayData.regionString"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'Game length'" :value="data.replayData.timeFormat"></stat-box>
        </div>

        <h2 v-if="!userchoiceresult" class="text-xl font-bold text-center">{{ $t('Who won the match?') }}</h2>

        <div class="grid md:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] gap-6 items-center">
          <template v-for="team in [0, 1]" :key="team">
            <span v-if="team === 1" class="max-md:hidden w-8 h-8 rounded-full bg-surface flex items-center justify-center text-[0.625rem] font-bold text-subtext">VS</span>
            <div :class="['bg-surface rounded-panel border-2 p-4 md:p-6 flex flex-col gap-4 min-w-0', teamBorder(team)]">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="flex items-center gap-2">
                  <span :class="['w-1 h-5 rounded-full', team === 0 ? 'bg-teal' : 'bg-red']"></span>
                  <span class="text-lg font-bold">{{ $t('Team :team', { team: team + 1 }) }}</span>
                  <span v-if="userchoiceresult && userchoiceteam === team && winnerTeam !== team" class="px-1.5 py-0.5 rounded-[0.25rem] bg-red/20 text-lred text-[0.625rem] font-bold uppercase">{{ $t('Your pick') }}</span>
                  <span v-if="userchoiceresult && winnerTeam === team" class="px-1.5 py-0.5 rounded-[0.25rem] bg-teal/20 text-lteal text-[0.625rem] font-bold uppercase">{{ userchoiceteam === team ? $t('Your pick · Winner') : $t('Winner') }}</span>
                </span>
                <span v-if="bans(team).length" class="flex items-center gap-2">
                  <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ $t('Bans') }}</span>
                  <!-- Struck through in the banning team's colour, as on the Drafter -->
                  <span v-for="(ban, index) in bans(team)" :key="index" class="relative w-8 h-8 shrink-0" :title="ban.hero ? $heroName(ban.hero) : $t('No Ban')">
                    <span v-if="ban.hero" class="absolute inset-0 rounded-full bg-cover bg-center opacity-60" :style="{ backgroundImage: 'url(/images/heroes/' + ban.hero.short_name + '.png)' }"></span>
                    <span :class="['absolute inset-0 rounded-full border-2', team === 0 ? 'border-[rgba(0,153,148,0.8)]' : 'border-[rgba(228,74,35,0.8)]']"></span>
                    <span :class="['absolute inset-0 rounded-full', team === 0 ? 'ban-strike-teal' : 'ban-strike-red']"></span>
                  </span>
                </span>
              </div>

              <div class="border-t border-surface-3 pt-3 flex justify-between text-[0.625rem] font-bold uppercase tracking-wider text-subtext">
                <span>{{ $t('Picks') }}</span>
                <span>{{ $t('Talents to level 10') }}</span>
              </div>

              <div class="flex flex-col gap-2">
                <div v-for="(player, index) in players(team)" :key="index" class="flex items-center gap-3 rounded-lg bg-surface-1 px-3 py-2 min-w-0">
                  <hero-image-wrapper :hero="player.hero" class="shrink-0"></hero-image-wrapper>
                  <span class="text-sm font-semibold truncate min-w-0">{{ $heroName(player.hero) }}</span>
                  <span v-if="player.talent" class="ml-auto flex gap-1.5 shrink-0">
                    <talent-image-wrapper v-for="level in ['level_one', 'level_four', 'level_seven', 'level_ten']" :key="level" :talent="player.talent[level]" :popup-hoverable="false"></talent-image-wrapper>
                  </span>
                </div>
              </div>

              <custom-button v-if="!userchoiceresult" :color="team === 0 ? 'teal' : 'blue'" class="font-semibold text-center" :ignoreclick="true" :disabled="choosing && userchoiceteam !== team" :alt="'Team won'" @click="chooseWinner(team)">
                <span v-if="choosing && userchoiceteam === team" class="inline-flex items-center gap-2"><img class="logo-spin h-5 w-auto" src="/images/logo/heroesprofilelogo.png" alt="">{{ $t('Checking who won...') }}</span>
                <template v-else>{{ $t('Team :team won', { team: team + 1 }) }}</template>
              </custom-button>
            </div>
          </template>
        </div>
      </template>

      <!-- Before the first match -->
      <template v-else>
        <panel-box title="How it works" :subtitle="user ? 'Three steps per match. Your score goes to the leaderboard once practice is over.' : 'Three steps per match.'" :divider="false">
          <div class="grid md:grid-cols-3 gap-3">
            <div v-for="(step, index) in steps" :key="step.title" class="rounded-control bg-surface-1 p-4 flex flex-col gap-2">
              <span class="w-7 h-7 rounded-full bg-teal/20 text-lteal text-xs font-bold flex items-center justify-center">{{ index + 1 }}</span>
              <span class="font-semibold">{{ $t(step.title) }}</span>
              <span class="text-xs text-subtext">{{ $t(step.text) }}</span>
            </div>
          </div>
        </panel-box>
      </template>

      <!-- The player's record, or a prompt to log in -->
      <panel-box v-if="user && !data && !isLoading" title="Your record" subtitle="Practice games don't count." :divider="false">
        <template #right>
          <div class="w-full sm:w-[11rem]">
            <single-select-filter :values="statsSeasons" :text="'Season'" :compact="true" :nosearch="true" :defaultValue="season" @input-changed="handleStatsSeasonChange"></single-select-filter>
          </div>
        </template>
        <tab-button :small="true" :tabs="recordModes" :ignoreclick="true" :overridedefaultside="recordMode" @tab-click="recordMode = $event" class="self-start"></tab-button>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <stat-box class="!m-0 !min-w-0" :title="'Predictions'" :value="record.predictions.toLocaleString('en-US')"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'Correct'" :value="record.correct.toLocaleString('en-US')"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'Accuracy'" :value="record.predictions ? (record.correct / record.predictions * 100).toFixed(1) + '%' : '—'"></stat-box>
          <stat-box class="!m-0 !min-w-0" :title="'Best streak this visit'" :value="bestStreak"></stat-box>
        </div>
      </panel-box>
      <div v-else-if="!user && !data && !isLoading" class="bg-surface rounded-panel p-4 md:px-6 flex flex-wrap items-center justify-between gap-4">
        <span class="text-sm text-subtext">{{ $t('Log in to track your predictions and rank on the leaderboard.') }}</span>
        <custom-button :href="'/Authenticate/Battlenet'" class="font-semibold" :alt="'Log in'">{{ $t('Log in') }}</custom-button>
      </div>
    </div>

    <modal-box v-if="skipPracticeOpen" :title="$t('Skip the rest of practice?')" width="max-w-[34rem]" @close="skipPracticeOpen = false">
      <p class="text-center">{{ $t('Every prediction from now on counts toward the leaderboard. Practice can\'t be started again.') }}</p>
      <template #footer>
        <div class="flex flex-wrap justify-center gap-3">
          <custom-button :ignoreclick="true" :alt="'Keep practicing'" @click="skipPracticeOpen = false">{{ $t('Keep practicing') }}</custom-button>
          <custom-button color="red" class="font-semibold" :ignoreclick="true" :loading="skippingPractice" :alt="'Skip practice'" @click="skipPractice">{{ $t('Skip practice') }}</custom-button>
        </div>
      </template>
    </modal-box>
  </div>
</template>

<script>
const PRACTICE_GAMES = 10;

export default {
  name: 'MatchPredictionGame',
  props: {
    filters: { type: Object, required: true },
    gametypes: Array,
    user: Object,
    predictionstatspractice: [Object, Array],
    predictionstatsall: Array,
    patreonUser: Boolean,
    season: Number,
  },
  data() {
    return {
      gametype: null,
      isLoading: false,
      choosing: false,
      data: null,
      userchoiceresult: null,
      userchoiceteam: null,
      totalGamesPlayedPractice: 0,
      practicemode: true,
      predictionstatsallupdated: [],
      currentSeason: this.season,
      statsSeason: this.season,
      recordMode: 'all',
      recordModes: [
        { key: 'all', text: 'All' },
        { key: '5', text: 'Storm League' },
        { key: '1', text: 'Quick Match' },
        { key: '6', text: 'ARAM' },
      ],
      loadRequest: 0,
      skipPracticeOpen: false,
      skippingPractice: false,
      streak: 0,
      bestStreak: 0,
      steps: [
        { title: 'Choose a game type', text: 'Storm League, Quick Match or ARAM. Then press Next match.' },
        { title: 'Study the draft', text: 'Map, rank, bans, picks and every hero\'s talents up to level 10.' },
        { title: 'Pick the winner', text: 'Choose the team you think won. You\'ll see the real result and the replay.' },
      ],
    };
  },
  created() {
    this.predictionstatsallupdated = this.predictionstatsall ?? [];
    const practice = Array.isArray(this.predictionstatspractice) ? this.predictionstatspractice : Object.values(this.predictionstatspractice || {});
    this.totalGamesPlayedPractice = practice.reduce((total, stat) => total + stat.games_played, 0);
    this.practicemode = this.totalGamesPlayedPractice < PRACTICE_GAMES;
  },
  computed: {
    disableFilterInput() {
      return !this.gametype || this.isLoading;
    },
    practicesLeft() {
      return Math.max(0, PRACTICE_GAMES - this.totalGamesPlayedPractice);
    },
    leaderboardUrl() {
      return '/Global/Leaderboard?type=Match%20Prediction&season=' + this.currentSeason;
    },
    statsSeasons() {
      return [{ code: 'All', name: 'All Seasons' }, ...(this.filters.match_prediction_seasons ?? []).filter(season => season.name !== 'Preseason')];
    },
    // 1 when the picked team won
    guessedRight() {
      return this.userchoiceresult?.data == 1;
    },
    winnerTeam() {
      if (!this.userchoiceresult) return null;
      return this.guessedRight ? this.userchoiceteam : 1 - this.userchoiceteam;
    },
    record() {
      const rows = this.predictionstatsallupdated.filter(stat =>
        (this.statsSeason === 'All' || stat.season == this.statsSeason)
        && (this.recordMode === 'all' || String(stat.game_type) === this.recordMode));
      return {
        predictions: rows.reduce((total, stat) => total + (Number(stat.win) || 0) + (Number(stat.loss) || 0), 0),
        correct: rows.reduce((total, stat) => total + (Number(stat.win) || 0), 0),
      };
    },
  },
  methods: {
    teamBorder(team) {
      if (!this.userchoiceresult) return 'border-surface';
      if (this.winnerTeam === team) return 'border-teal';
      if (this.userchoiceteam === team) return 'border-red';
      return 'border-surface';
    },
    bans(team) {
      return this.data?.replayBans?.[team] || [];
    },
    // In draft order when the draft is known, each with its talents
    players(team) {
      const players = this.data?.playerData?.[team] || [];
      const picks = this.data?.draftData?.[team]?.picks;
      if (!picks) return players;
      return picks.filter(pick => pick.hero).map(pick => players.find(player => player.hero?.id === pick.hero.id) || { hero: pick.hero, talent: null });
    },
    async getData() {
      // A match that arrives after See your stats is dropped
      const request = ++this.loadRequest;
      this.isLoading = true;
      this.data = null;
      try {
        const response = await this.$axios.post('/api/v1/match/prediction/game', { gametype: this.gametype });
        if (response.data.status == 'failure to validate inputs') {
          throw new Error('Failure to validate inputs');
        }
        if (request === this.loadRequest) this.data = response.data;
      } catch (error) {
        // Nothing loaded; Next match tries again
      } finally {
        if (request === this.loadRequest) this.isLoading = false;
      }
    },
    showStats() {
      this.loadRequest += 1;
      this.isLoading = false;
      this.data = null;
      this.userchoiceresult = null;
      this.userchoiceteam = null;
    },
    applyFilter() {
      // The button's click still arrives while it's disabled
      if (this.disableFilterInput) return;
      this.userchoiceresult = null;
      this.userchoiceteam = null;
      this.getData();
    },
    handleInputChange(eventPayload) {
      this.gametype = eventPayload.value;
    },
    async chooseWinner(team) {
      if (this.choosing || this.userchoiceresult) return;
      this.userchoiceteam = team;
      this.choosing = true;
      try {
        const response = await this.$axios.post('/api/v1/match/prediction/game/choose/winner', {
          team,
          fingerprint: this.data.fingerprint,
        });
        if (response.data.status == 'failure to validate inputs') {
          throw new Error('Failure to validate inputs');
        }
        this.userchoiceresult = response.data;

        if (this.guessedRight) {
          this.streak += 1;
          this.bestStreak = Math.max(this.bestStreak, this.streak);
        } else {
          this.streak = 0;
        }

        const stats = response.data.predictionstats ?? [];
        if (this.practicemode) {
          this.totalGamesPlayedPractice = stats.reduce((total, stat) => total + stat.games_played, 0);
        }
        this.predictionstatsallupdated = response.data.predictionstatsall ?? this.predictionstatsallupdated;
        this.currentSeason = response.data.season ?? this.currentSeason;
        // The server decides when practice is over
        this.practicemode = response.data.practicemode;
      } catch (error) {
        // This replay can no longer be answered (answered already, or a newer one was
        // served in another tab): move on to a fresh one.
        if (error.response?.status === 409) {
          this.applyFilter();
        }
      } finally {
        this.choosing = false;
      }
    },
    async skipPractice() {
      if (this.skippingPractice) return;
      this.skippingPractice = true;
      try {
        const response = await this.$axios.post('/api/v1/match/prediction/game/skip/practice');
        this.practicemode = response.data.practicemode;
        this.skipPracticeOpen = false;
      } catch (error) {
        // Left open, so Skip can be pressed again
      } finally {
        this.skippingPractice = false;
      }
    },
    handleStatsSeasonChange(eventPayload) {
      this.statsSeason = eventPayload.value || this.currentSeason;
    },
  },
};
</script>
