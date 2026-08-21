<template>
  <section id="variables" class="mb-10 scroll-mt-4">
    <h2 class="text-2xl mb-2">Variables</h2>
    <p class="text-sm text-gray-medium mb-4 max-w-[70ch]">
      What every parameter accepts, read from the same tables the validators check
      against. Values are case-sensitive and must match exactly; anything else is
      rejected with a 422 naming the parameter.
    </p>

    <article
      v-for="variable in variables"
      :key="variable.name"
      :id="'variable-' + variable.name"
      class="bg-lighten p-6 mb-4 scroll-mt-4"
    >
      <div class="flex flex-wrap items-baseline gap-3 mb-1">
        <code class="text-lg text-lteal">{{ variable.name }}</code>
        <span v-if="variable.also" class="text-xs text-gray-medium">also {{ variable.also }}</span>
      </div>

      <p class="text-xs text-gray-medium mb-2">{{ variable.used_by }}</p>
      <p class="text-sm mb-4">{{ variable.summary }}</p>

      <!-- Paired values carry a meaning the value itself does not show: `1` is
           NA, `sl` is Storm League. Bare lists just need to be readable. -->
      <table v-if="variable.pairs" class="min-w-0 w-full responsive-table">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left text-sm">Pass</th>
            <th class="py-2 px-3 text-left text-sm">Means</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(label, value) in variable.pairs" :key="value">
            <td class="py-2 px-3">{{ value }}</td>
            <td class="py-2 px-3">{{ label }}</td>
          </tr>
        </tbody>
      </table>

      <div v-else class="flex flex-wrap gap-2 items-center">
        <code
          v-for="value in shown(variable)"
          :key="value"
          class="bg-darken px-2 py-1 text-xs"
        >{{ value }}</code>

        <button
          v-if="variable.values.length > limit"
          class="text-xs text-lteal underline"
          @click="toggle(variable.name)"
        >
          {{ expanded[variable.name] ? 'show fewer' : 'show all ' + variable.values.length }}
        </button>
      </div>
    </article>
  </section>
</template>

<script>
/*
 * Every accepted value for every parameter, supplied by the server from the same
 * source the validation rules read. Nothing here is written by hand, so a new
 * hero or patch appears without anyone remembering to add it.
 */
export default {
  name: 'ApiVariables',
  props: {
    variables: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      expanded: {},
      // Enough to show the shape of the list without a wall of 90 hero names.
      limit: 24,
    }
  },
  methods: {
    shown(variable) {
      const values = variable.values || [];

      return this.expanded[variable.name] ? values : values.slice(0, this.limit);
    },

    toggle(name) {
      this.expanded[name] = !this.expanded[name];
    },
  },
}
</script>
