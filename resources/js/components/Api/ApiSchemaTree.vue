<template>
  <ul class="text-sm">
    <li v-for="row in rows" :key="row.name" class="py-1">
      <div class="flex flex-wrap items-baseline gap-2">
        <button
          v-if="row.children"
          class="text-lteal w-4 text-left"
          @click="toggle(row.name)"
          :aria-label="open[row.name] ? 'Collapse' : 'Expand'"
        >{{ open[row.name] ? '−' : '+' }}</button>
        <span v-else class="w-4"></span>

        <code class="text-lteal">{{ row.name }}</code>
        <span class="text-gray-medium">{{ row.type }}</span>
        <span v-if="row.required" class="text-yellow text-xs">required</span>
        <span v-if="row.description" class="text-xs">{{ row.description }}</span>
      </div>

      <div v-if="row.children && open[row.name]" class="ml-4 border-l border-lighten pl-3">
        <api-schema-tree :schema="row.schema"></api-schema-tree>
      </div>
    </li>
  </ul>
</template>

<script>
/*
 * Renders one JSON Schema. Recursive, so nested objects and arrays-of-objects
 * expand in place rather than being flattened into dotted paths.
 */
export default {
  name: 'ApiSchemaTree',
  props: {
    schema: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      open: {},
    }
  },
  computed: {
    /* An array's shape is its item shape — show that rather than a bare "array". */
    subject() {
      let node = this.schema;

      while (node && this.typeOf(node) === 'array' && node.items) {
        node = node.items;
      }

      return node || {};
    },
    rows() {
      const properties = this.subject.properties || {};
      const required = this.subject.required || [];

      return Object.keys(properties).map(name => {
        const child = properties[name];
        const inner = this.unwrap(child);

        return {
          name,
          type: this.label(child),
          // Set on the property itself, or on the item shape when the property
          // is an array of objects.
          description: child.description || inner.description || null,
          required: required.includes(name),
          schema: child,
          children: !!(inner.properties && Object.keys(inner.properties).length),
        }
      });
    },
  },
  methods: {
    toggle(name) {
      this.open[name] = !this.open[name];
    },

    typeOf(node) {
      return Array.isArray(node.type) ? node.type[0] : node.type;
    },

    unwrap(node) {
      let current = node;

      while (current && this.typeOf(current) === 'array' && current.items) {
        current = current.items;
      }

      return current || {};
    },

    /* `string`, `string or null`, `array of object` — readable rather than exact. */
    label(node) {
      const types = Array.isArray(node.type) ? node.type : [node.type];
      const name = types.filter(Boolean).join(' or ') || 'any';

      if (this.typeOf(node) === 'array' && node.items) {
        return 'array of ' + this.label(node.items);
      }

      return name;
    },
  },
}
</script>
