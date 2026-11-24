<template>
  <!-- A placeholder until the redesign's write-up is ready -->
  <div>
    <page-heading :heading="'The 2026 Redesign'" :subheading="'Coming soon'"></page-heading>
    <div class="max-w-[120rem] mx-auto px-4 md:px-8">
      <panel-box :divider="false">
        <p>{{ $t('The story of the 2026 site redesign is on its way.') }}</p>
        <a href="/Team" class="group/bt inline-flex items-center w-fit text-teal"><span class="group-hover/bt:underline">{{ $t('Meet the team') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
      </panel-box>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RedesignPage',
}
</script>
