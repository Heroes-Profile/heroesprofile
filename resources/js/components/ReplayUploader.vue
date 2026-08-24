<template>
  <div>
    <page-heading
      heading="Upload Replays"
      :infoText1="'Upload replays from your browser, or install the uploader and have it done for you as you play.'"
    ></page-heading>

    <div class="mx-auto max-w-[1300px] px-4 mt-6 grid gap-6 md:grid-cols-2 items-start">

      <div class="bg-lighten p-6">
        <h2 class="text-2xl mb-4">Replay Uploader</h2>

        <img
          src="/images/miscellaneous/windowsUploader.PNG"
          alt="The Heroes Profile replay uploader running on Windows"
          class="w-full mb-4"
        />

        <p class="text-sm mb-4">
          Watches your replay folder and uploads each game as you finish it, with pre-match and
          post-match analysis. Games uploaded this way count towards the leaderboards.
        </p>

        <div class="mb-4">
          <custom-button
            :href="windowsInstaller"
            text="Download for Windows"
            alt="Download the Windows replay uploader"
            color="teal"
            :targetblank="true"
          ></custom-button>
        </div>

        <p class="text-sm mb-2">
          macOS and Linux
          <a class="link block" :href="electronRelease" target="_blank" rel="noopener">{{ electronRelease }}</a>
        </p>

        <p class="text-sm">
          Source
          <a class="link block" :href="repository" target="_blank" rel="noopener">{{ repository }}</a>
        </p>
      </div>

      <div class="bg-lighten p-6">
        <h2 class="text-2xl mb-4">Web Uploader</h2>

        <p class="text-sm mb-4 text-yellow">
          Games uploaded through the web uploader may not be eligible for leaderboard consideration.
        </p>

        <div
          class="border-2 border-dashed p-6 text-center mb-4 transition-colors"
          :class="dragging ? 'border-lteal bg-darken' : 'border-gray-medium'"
          @dragenter.prevent="dragging = true"
          @dragover.prevent="dragging = true"
          @dragleave.prevent="dragging = false"
          @drop.prevent="onDrop"
        >
          <p class="mb-4 text-sm">Drop replays here, or choose them below.</p>

          <div class="flex flex-wrap gap-3 justify-center">
            <label class="bg-teal hover:bg-lteal transition-colors text-white rounded text-center py-2 px-4 cursor-pointer text-sm">
              <span>Select replays</span>
              <input type="file" class="hidden" multiple accept=".StormReplay" @change="onSelect" />
            </label>

            <label
              v-if="directorySupported"
              class="bg-teal hover:bg-lteal transition-colors text-white rounded text-center py-2 px-4 cursor-pointer text-sm"
            >
              <span>Select a folder</span>
              <input type="file" class="hidden" multiple webkitdirectory directory @change="onSelect" />
            </label>
          </div>

          <p class="text-xs text-gray-medium mt-3">
            .StormReplay files, up to {{ formatSize(maxBytes) }} each. Uploading more than about 700
            at once is easier by folder.
          </p>
        </div>

        <div v-if="ignoredCount" class="text-xs text-gray-medium mb-3">
          Ignored {{ ignoredCount }} file{{ ignoredCount === 1 ? '' : 's' }} that {{ ignoredCount === 1 ? 'was' : 'were' }} not a .StormReplay.
        </div>

        <div v-if="files.length" class="mb-4">
          <div class="flex flex-wrap gap-4 text-sm mb-2">
            <span>{{ finishedCount }} of {{ files.length }} done</span>
            <span class="text-lteal">{{ counts.Success || 0 }} uploaded</span>
            <span class="text-yellow">{{ counts.Duplicate || 0 }} already had</span>
            <span class="text-lred">{{ failedCount }} failed</span>
          </div>

          <div class="h-1 bg-darken">
            <div class="h-1 bg-teal transition-all" :style="{ width: overallProgress + '%' }"></div>
          </div>
        </div>

        <div v-if="waitingSeconds" class="bg-darken p-3 text-sm mb-4">
          Rate limited by the API — waiting {{ waitingSeconds }}s, then carrying on.
        </div>

        <table v-if="files.length" class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Replay</th>
              <th class="py-2 px-3 text-left text-sm">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="file in files" :key="file.id">
              <td class="py-2 px-3 break-all">
                {{ file.name }}
                <div v-if="file.message" class="text-xs">{{ file.message }}</div>
                <div class="h-1 bg-darken mt-1" v-if="file.status === 'Uploading'">
                  <div class="h-1 bg-teal" :style="{ width: file.progress + '%' }"></div>
                </div>
              </td>
              <td class="py-2 px-3 text-right align-top">
                <a v-if="file.replayID" class="link" :href="'/Match/Single/' + file.replayID">{{ file.status }}</a>
                <span v-else>{{ file.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
/*
 * Posts one replay at a time to the public upload endpoint. Sequential on
 * purpose: every upload costs a parser call, and the endpoint allows 60 a minute
 * per address — firing a folder off in parallel just earns a wall of 429s.
 */
export default {
  name: 'ReplayUploader',
  props: {
    uploadUrl: String,
    maxBytes: Number,
  },
  data() {
    return {
      files: [],
      nextId: 1,
      dragging: false,
      running: false,
      ignoredCount: 0,
      waitingSeconds: 0,
      directorySupported: false,
      windowsInstaller: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest/download/HeroesProfileUploaderSetup.exe',
      electronRelease: 'https://github.com/Heroes-Profile/heroesprofile-electron-uploader/releases',
      repository: 'https://github.com/Heroes-Profile/HeroesProfile.Uploader',
    }
  },
  created() {
    const input = document.createElement('input');
    this.directorySupported = 'webkitdirectory' in input || 'directory' in input;
  },
  computed: {
    counts() {
      return this.files.reduce((totals, file) => {
        totals[file.status] = (totals[file.status] || 0) + 1;
        return totals;
      }, {});
    },
    finishedCount() {
      return this.files.filter(file => this.isFinished(file.status)).length;
    },
    failedCount() {
      return this.files.filter(file => this.isFinished(file.status) && file.status !== 'Success' && file.status !== 'Duplicate').length;
    },
    overallProgress() {
      if (!this.files.length) return 0;
      return Math.round((this.finishedCount / this.files.length) * 100);
    },
  },
  methods: {
    onSelect(event) {
      this.enqueue(Array.from(event.target.files || []));
      event.target.value = '';
    },

    onDrop(event) {
      this.dragging = false;
      this.enqueue(Array.from(event.dataTransfer.files || []));
    },

    enqueue(selected) {
      const replays = selected.filter(file => file.name.toLowerCase().endsWith('.stormreplay'));
      this.ignoredCount += selected.length - replays.length;

      replays.forEach(file => {
        this.files.push({
          id: this.nextId++,
          file,
          name: file.name,
          status: file.size > this.maxBytes ? 'Too large' : 'Queued',
          message: file.size > this.maxBytes ? 'Over ' + this.formatSize(this.maxBytes) : '',
          progress: 0,
          attempts: 0,
          replayID: null,
        });
      });

      this.run();
    },

    async run() {
      if (this.running) return;
      this.running = true;

      let next;
      while ((next = this.files.find(file => file.status === 'Queued'))) {
        await this.send(next);
      }

      this.running = false;
    },

    async send(entry) {
      entry.status = 'Uploading';
      entry.progress = 0;
      entry.attempts++;

      const body = new FormData();
      body.append('file', entry.file);

      try {
        const response = await this.$axios.post(this.uploadUrl, body, {
          onUploadProgress: event => {
            if (event.total) {
              entry.progress = Math.round((event.loaded / event.total) * 100);
            }
          },
        });

        this.applyResult(entry, response.data);
      } catch (error) {
        await this.applyFailure(entry, error);
      }
    },

    applyResult(entry, data) {
      // The endpoint answers 200 for rejections too, with a body carrying no
      // status — the same shape the desktop client treats as an upload error.
      if (!data || !data.status) {
        entry.status = 'UploadError';
        entry.message = (data && data.Error) ? data.Error : 'The server did not return a status.';
        return;
      }

      entry.replayID = data.replayID || null;
      entry.message = data.status.startsWith('Failure') ? data.status : '';
      entry.status = data.status.startsWith('Failure') ? 'Failure' : data.status;
    },

    async applyFailure(entry, error) {
      const status = error.response ? error.response.status : 0;

      // Its own per-IP ceiling, not the shared API one. Waiting it out is the
      // whole difference between a folder of replays landing and not.
      if (status === 429 && entry.attempts <= 3) {
        const retryAfter = parseInt((error.response.headers || {})['retry-after'], 10);
        await this.countdown(Number.isNaN(retryAfter) ? 60 : retryAfter);
        entry.status = 'Queued';
        return;
      }

      if (status === 429) {
        entry.status = 'Throttled';
        entry.message = 'Still rate limited after three attempts. Try this one again later.';
        return;
      }

      entry.status = status >= 500 ? 'ServerError' : 'UploadError';
      entry.message = status ? 'HTTP ' + status : 'The upload did not reach the server.';
    },

    countdown(seconds) {
      this.waitingSeconds = seconds;

      return new Promise(resolve => {
        const tick = setInterval(() => {
          this.waitingSeconds--;

          if (this.waitingSeconds <= 0) {
            clearInterval(tick);
            this.waitingSeconds = 0;
            resolve();
          }
        }, 1000);
      });
    },

    isFinished(status) {
      return status !== 'Queued' && status !== 'Uploading';
    },

    formatSize(bytes) {
      return Math.round(bytes / (1024 * 1024)) + ' MB';
    },
  },
}
</script>
