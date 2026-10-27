<template>
  <div>
    <page-heading :heading="poll.title" :infoText1="statusText"></page-heading>

    <div class="max-w-[900px] mx-auto px-4 mt-4 mb-10">
      <p v-if="poll.description" class="mb-6 whitespace-pre-line">{{ poll.description }}</p>

      <p class="mb-3 text-sm italic">{{ poll.allow_multiple ? 'Pick as many as you like.' : 'Pick one.' }}</p>

      <div class="flex flex-col gap-3 mb-6">
        <label
          v-for="option in poll.options"
          :key="option.id"
          class="flex items-start gap-3 bg-lighten rounded p-3"
          :class="{ 'cursor-pointer': canVote }"
        >
          <input
            v-if="poll.allow_multiple"
            v-model="selected"
            type="checkbox"
            class="mt-1"
            :value="option.id"
            :disabled="!canVote || saving"
          />
          <input
            v-else
            v-model="selectedSingle"
            type="radio"
            class="mt-1"
            name="poll-option"
            :value="option.id"
            :disabled="!canVote || saving"
          />
          <div class="flex-1 min-w-0">
            <div class="font-bold">{{ option.label }}</div>
            <div v-if="option.description" class="text-sm">{{ option.description }}</div>
            <stat-bar
              v-if="results"
              class="mt-2"
              :value="percent(option.id)"
              :displayText="`${percent(option.id)}% (${count(option.id)})`"
              :color="selectedIds.includes(option.id) ? 'teal' : 'blue'"
            ></stat-bar>
          </div>
        </label>
      </div>

      <p v-if="results" class="mb-6 text-sm">{{ results.total }} {{ results.total === 1 ? 'vote' : 'votes' }}</p>

      <template v-if="canVote">
        <label for="poll-comment">Comment (optional, only Heroes Profile sees it):</label>
        <textarea
          id="poll-comment"
          v-model="comment"
          class="form-control search-input text-black w-full mt-2 mb-4"
          rows="4"
          maxlength="2000"
          :disabled="saving"
        ></textarea>

        <div class="flex justify-end">
          <custom-button
            :text="vote ? 'Update vote' : 'Vote'"
            :alt="'Submit your vote'"
            :disabled="selectedIds.length === 0"
            :loading="saving"
            :ignoreclick="true"
            @click="submit"
          ></custom-button>
        </div>
        <p v-if="saved" class="bg-teal p-4 text-center mt-4">Your vote is saved. You can change it until the poll closes.</p>
      </template>

      <div v-else-if="poll.is_open" class="flex justify-center">
        <custom-button :href="'/Authenticate/Battlenet'" :text="'Log in with Battle.net to vote'" :alt="'Log in with Battle.net to vote'" :size="'big'"></custom-button>
      </div>

      <p v-if="error" class="bg-red p-4 text-center mt-4">{{ error }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CommunityPoll',
  props: {
    poll: { type: Object, required: true },
    loggedIn: Boolean,
    initialVote: { type: Object, default: null },
    initialResults: { type: Object, default: null },
  },
  data() {
    const options = this.initialVote?.options ?? [];
    return {
      vote: this.initialVote,
      results: this.initialResults,
      selected: [...options],
      selectedSingle: options[0] ?? null,
      comment: this.initialVote?.comment ?? '',
      saving: false,
      saved: false,
      error: null,
    };
  },
  computed: {
    canVote() {
      return this.loggedIn && this.poll.is_open;
    },
    selectedIds() {
      if (this.poll.allow_multiple) return this.selected;
      return this.selectedSingle === null ? [] : [this.selectedSingle];
    },
    statusText() {
      if (!this.poll.is_open) return 'This poll is closed.';
      if (!this.poll.closes_at) return 'Community poll';
      return `Closes ${new Date(this.poll.closes_at).toLocaleString()}`;
    },
  },
  methods: {
    count(optionId) {
      return this.results?.counts?.[optionId] ?? 0;
    },
    percent(optionId) {
      if (!this.results?.total) return 0;
      return Math.round((this.count(optionId) / this.results.total) * 100);
    },
    async submit() {
      if (this.saving || this.selectedIds.length === 0) return;
      this.saving = true;
      this.saved = false;
      this.error = null;
      try {
        const response = await this.$axios.post(`/api/v1/poll/${encodeURIComponent(this.poll.slug)}/vote`, {
          options: this.selectedIds,
          comment: this.comment,
        });
        this.vote = response.data.myVote;
        this.results = response.data.results;
        this.saved = true;
      } catch (e) {
        if (e.response?.status === 401) {
          this.error = 'Your login has expired. Log in again to vote.';
        } else if (e.response?.status === 429) {
          this.error = 'Too many attempts. Wait a minute and try again.';
        } else {
          this.error = e.response?.data?.message ?? 'Your vote could not be saved. Try again.';
        }
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>
