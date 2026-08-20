<template>
  <div>
    <page-heading :infoText1="infoText" heading="Developer Tier Request"></page-heading>

    <div class="mx-auto max-w-[1000px] p-4">
      <div class="bg-lighten p-6 mb-8">
        <p class="mb-4">
          Thanks for your interest in the Developer Tier. Email us at
          <a :href="mailto" class="underline hover:text-lteal">zemill@heroesprofile.com</a>
          with the details below. We review each request to make sure Developer Tier
          access fits your use case and our guidelines.
        </p>

        <h2 class="text-lg mb-2">What To Include</h2>
        <ul class="text-sm list-disc list-inside space-y-1">
          <li>What data you are pulling and how frequently</li>
          <li>How your app, bot, overlay or other tool works in real time</li>
          <li>Expected request volume and scale</li>
          <li>A quick workflow diagram or step-by-step flow from start to finish</li>
        </ul>
      </div>

      <div class="bg-lighten p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
          <h2 class="text-lg">Email Template</h2>
          <custom-button @click="copyTemplate" :text="copied ? 'Copied' : 'Copy'" :alt="'Copy template'" :size="'small'" :ignoreclick="true"></custom-button>
        </div>
        <p class="text-sm text-gray-medium mb-3">Copy this and fill in the details.</p>
        <pre class="bg-darken p-4 text-sm overflow-x-auto whitespace-pre-wrap">{{ template }}</pre>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ApiDeveloperTier',
  components: {
  },
  data(){
    return {
      infoText: "The Developer Tier is approved per project rather than bought from the billing page, so tell us what you are building.",
      mailto: 'mailto:zemill@heroesprofile.com?subject=Developer%20Tier%20Request',
      copied: false,
      template: `Subject: Developer Tier Request - [Project Name]

Hi Zemill,

Project name:
[Your project name]

Summary of what I am building:
[1-3 sentence summary]

What data I am pulling and how frequently:
[Endpoints/data types]
[Request cadence, polling/trigger model, update intervals]

How my app/bot/overlay/tool works in real time:
[Describe how data is consumed and shown to users in your workflow]
[Any user interactions or automation behavior]

Expected request volume / scale:
[Estimated requests per minute/hour/day]
[Estimated concurrent users or matches]

End-to-end workflow:
1) [Step 1]
2) [Step 2]
3) [Step 3]

Anything else relevant:
[Optional details]

Thanks,
[Your name]`,
    }
  },
  methods: {
    copyTemplate(){
      navigator.clipboard.writeText(this.template).then(() => {
        this.copied = true;
        setTimeout(() => { this.copied = false; }, 3000);
      });
    },
  },
}
</script>
