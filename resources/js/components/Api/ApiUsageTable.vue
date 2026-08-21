<template>
  <div>
    <template v-if="compact">
      <p v-if="!activeRows.length" class="text-sm text-gray-medium mb-3">
        No calls have counted against your quota in the current window.
      </p>

      <table v-else class="min-w-0 w-full responsive-table mb-3">
        <thead>
          <tr>
            <th class="py-2 px-3 text-left text-sm">Endpoint</th>
            <th class="py-2 px-3 text-left text-sm">Calls Used</th>
            <th class="py-2 px-3 text-left text-sm">Calls Left</th>
            <th class="py-2 px-3 text-left text-sm">Calls Reset</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in activeRows" :key="row.endpoint">
            <td class="py-2 px-3">{{ row.name }}</td>
            <td class="py-2 px-3">{{ format(row.used) }}</td>
            <td class="py-2 px-3">{{ format(row.remaining) }}</td>
            <td class="py-2 px-3">{{ row.resets_at }}</td>
          </tr>
        </tbody>
      </table>

      <a href="/Api/Account/Billing" class="text-sm underline hover:text-lteal">
        View every endpoint limit
      </a>
    </template>

    <template v-else>
      <p v-if="!usage.length" class="text-sm text-gray-medium">
        No endpoints are registered yet.
      </p>

      <div v-for="group in usage" :key="group.title" class="mb-6">
        <h3 class="text-lteal mb-2">{{ group.title }}</h3>

        <table class="min-w-0 w-full responsive-table">
          <thead>
            <tr>
              <th class="py-2 px-3 text-left text-sm">Name</th>
              <th class="py-2 px-3 text-left text-sm">Calls Used</th>
              <th class="py-2 px-3 text-left text-sm">Calls Left</th>
              <th class="py-2 px-3 text-left text-sm">Available Calls</th>
              <th class="py-2 px-3 text-left text-sm">Calls Reset</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in group.endpoints" :key="row.endpoint" :class="{ 'text-gray-medium': !row.included }">
              <td class="py-2 px-3">{{ row.name }}</td>

              <template v-if="row.included">
                <td class="py-2 px-3">{{ format(row.used) }}</td>
                <td class="py-2 px-3">{{ format(row.remaining) }}</td>
                <td class="py-2 px-3">{{ format(row.limit) }}</td>
                <td class="py-2 px-3">{{ row.resets_at || 'Not started' }}</td>
              </template>

              <td v-else class="py-2 px-3" colspan="4">Not included in your plan</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script>
export default {
  name: 'ApiUsageTable',
  components: {
  },
  props: {
    usage: {
      type: Array,
      default: () => [],
    },
    // Short list of what has actually been used, for the account page.
    compact: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    activeRows(){
      return this.usage
        .flatMap(group => group.endpoints)
        .filter(row => row.used > 0)
        .sort((a, b) => b.used - a.used)
        .slice(0, 5);
    },
  },
  methods: {
    format(value){
      return Number(value).toLocaleString();
    },
  },
}
</script>
