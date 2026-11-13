<template>
  <div ref="root" class="relative" @keydown.esc="open = false">
    <!-- The box: same look as the select filters -->
    <div
      class="relative h-[2.5rem] w-[9.5rem] max-md:w-full rounded-control bg-surface-3 hover:bg-surface-4 text-white px-3 flex items-center gap-2 cursor-pointer"
      tabindex="0"
      role="button"
      :aria-label="$t(label)"
      :aria-expanded="open"
      @click="toggle"
      @keydown.enter.prevent="toggle"
      @keydown.space.prevent="toggle"
    >
      <span class="font-bold uppercase whitespace-nowrap">{{ modelValue ? formatDate(modelValue) : placeholder }}</span>
      <button v-if="modelValue" type="button" class="relative z-[1] ml-auto" :aria-label="$t('Clear :field', { field: $t(label) })" @click.stop="pick(null)"><i class="fa-solid fa-xmark"></i></button>
      <i v-else class="fa-solid fa-chevron-down ml-auto text-xs"></i>
    </div>

    <!-- The calendar: same panel as the filter dropdowns -->
    <div v-if="open" class="expandable-dropdown absolute left-0 mt-2 !w-[17.5rem] max-w-[calc(100vw-2rem)] !min-w-0 rounded p-3 z-50 text-sm font-normal normal-case">
      <div class="flex items-center justify-between mb-2">
        <button type="button" class="w-8 h-8 rounded-control hover:bg-surface-4 disabled:opacity-30 disabled:hover:bg-transparent" :disabled="!canGoBack" :aria-label="$t('Previous')" @click="step(-1)"><i class="fa-solid fa-chevron-left text-xs"></i></button>
        <button type="button" class="px-3 h-8 rounded-control hover:bg-surface-4 font-bold" @click="zoomOut">{{ heading }}</button>
        <button type="button" class="w-8 h-8 rounded-control hover:bg-surface-4 disabled:opacity-30 disabled:hover:bg-transparent" :disabled="!canGoForward" :aria-label="$t('Next')" @click="step(1)"><i class="fa-solid fa-chevron-right text-xs"></i></button>
      </div>

      <!-- Days -->
      <div v-if="view === 'days'" class="grid grid-cols-7 gap-0.5 text-center">
        <span v-for="name in weekdayNames" :key="name" class="h-8 flex items-center justify-center text-xs text-subtext">{{ name }}</span>
        <span v-for="n in leadingBlanks" :key="'blank' + n"></span>
        <button
          v-for="day in daysInMonth"
          :key="day"
          type="button"
          class="h-8 rounded-control disabled:opacity-30 disabled:cursor-default"
          :class="dayClass(day)"
          :disabled="!inRange(iso(viewYear, viewMonth, day))"
          @click="pick(iso(viewYear, viewMonth, day))"
        >{{ day }}</button>
      </div>

      <!-- Months -->
      <div v-else-if="view === 'months'" class="grid grid-cols-3 gap-1">
        <button
          v-for="(name, index) in monthNames"
          :key="name"
          type="button"
          class="h-10 rounded-control disabled:opacity-30 disabled:cursor-default"
          :class="index === viewMonth ? 'bg-teal hover:bg-lteal' : 'hover:bg-surface-4'"
          :disabled="!monthInRange(viewYear, index)"
          @click="viewMonth = index; view = 'days'"
        >{{ name }}</button>
      </div>

      <!-- Years -->
      <div v-else class="grid grid-cols-3 gap-1">
        <button
          v-for="year in yearPage"
          :key="year"
          type="button"
          class="h-10 rounded-control disabled:opacity-30 disabled:cursor-default"
          :class="year === viewYear ? 'bg-teal hover:bg-lteal' : 'hover:bg-surface-4'"
          :disabled="!yearInRange(year)"
          @click="viewYear = year; view = 'months'"
        >{{ year }}</button>
      </div>

      <div class="flex justify-end mt-2 pt-2 border-t border-white/10">
        <button type="button" class="px-3 py-1 rounded-control hover:bg-surface-4 text-lteal font-semibold disabled:opacity-30 disabled:hover:bg-transparent" :disabled="!inRange(todayIso)" @click="pick(todayIso)">{{ $t('Today') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
  // The site's language when one was picked; on English pages the browser's own date style, as before
  const SITE_LOCALE = document.documentElement.lang === 'en' ? undefined : document.documentElement.lang;
  // Day, month and year in the visitor's own order, e.g. 10/28/26 in the US and 28.10.26 in most of Europe
  const DATE_FORMAT = new Intl.DateTimeFormat(SITE_LOCALE, { day: '2-digit', month: '2-digit', year: '2-digit' });
  const pad = n => String(n).padStart(2, '0');

  // First day of the week for the visitor's locale (1 = Monday ... 7 = Sunday); Sunday where the browser can't tell
  function firstDayOfWeek() {
    try {
      const locale = new Intl.Locale(navigator.language);
      const info = locale.getWeekInfo ? locale.getWeekInfo() : locale.weekInfo;
      if (info && info.firstDay) return info.firstDay % 7;
    } catch (error) {
      // fall through
    }
    return 0;
  }

  export default {
    name: 'DatePickerField',
    props: {
      // YYYY-MM-DD or null, like a date input's value
      modelValue: { type: String, default: null },
      min: { type: String, default: null },
      max: { type: String, default: null },
      label: { type: String, default: 'Date' },
    },
    emits: ['update:modelValue', 'change'],
    data() {
      const today = new Date();
      return {
        open: false,
        view: 'days',
        viewYear: today.getFullYear(),
        viewMonth: today.getMonth(),
        weekStart: firstDayOfWeek(),
        todayIso: `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`,
      };
    },
    mounted() {
      document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
      document.removeEventListener('click', this.handleClickOutside);
    },
    computed: {
      placeholder() {
        return DATE_FORMAT.formatToParts(new Date(2000, 0, 2))
          .map(part => ({ day: 'DD', month: 'MM', year: 'YY' }[part.type] || part.value))
          .join('');
      },
      heading() {
        if (this.view === 'days') {
          return new Intl.DateTimeFormat(SITE_LOCALE, { month: 'long', year: 'numeric' }).format(new Date(this.viewYear, this.viewMonth, 1));
        }
        if (this.view === 'months') return String(this.viewYear);
        return `${this.yearPage[0]} – ${this.yearPage[this.yearPage.length - 1]}`;
      },
      weekdayNames() {
        const format = new Intl.DateTimeFormat(SITE_LOCALE, { weekday: 'narrow' });
        // 2023-01-01 was a Sunday
        return Array.from({ length: 7 }, (_, i) => format.format(new Date(2023, 0, 1 + ((this.weekStart + i) % 7))));
      },
      monthNames() {
        const format = new Intl.DateTimeFormat(SITE_LOCALE, { month: 'short' });
        return Array.from({ length: 12 }, (_, i) => format.format(new Date(2000, i, 1)));
      },
      daysInMonth() {
        return new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
      },
      leadingBlanks() {
        return (new Date(this.viewYear, this.viewMonth, 1).getDay() - this.weekStart + 7) % 7;
      },
      // Twelve years, ending on a multiple of 12 boundary that holds viewYear
      yearPage() {
        const first = this.viewYear - (this.viewYear % 12);
        return Array.from({ length: 12 }, (_, i) => first + i);
      },
      canGoBack() {
        if (!this.min) return true;
        const [y, m] = this.min.split('-').map(Number);
        if (this.view === 'days') return this.viewYear * 12 + this.viewMonth > y * 12 + (m - 1);
        if (this.view === 'months') return this.viewYear > y;
        return this.yearPage[0] > y;
      },
      canGoForward() {
        if (!this.max) return true;
        const [y, m] = this.max.split('-').map(Number);
        if (this.view === 'days') return this.viewYear * 12 + this.viewMonth < y * 12 + (m - 1);
        if (this.view === 'months') return this.viewYear < y;
        return this.yearPage[11] < y;
      },
    },
    methods: {
      iso(year, month, day) {
        return `${year}-${pad(month + 1)}-${pad(day)}`;
      },
      // Values are YYYY-MM-DD, so plain string comparison orders them
      inRange(value) {
        return (!this.min || value >= this.min) && (!this.max || value <= this.max);
      },
      monthInRange(year, month) {
        const first = this.iso(year, month, 1);
        const last = this.iso(year, month, new Date(year, month + 1, 0).getDate());
        return (!this.min || last >= this.min) && (!this.max || first <= this.max);
      },
      yearInRange(year) {
        return (!this.min || `${year}-12-31` >= this.min) && (!this.max || `${year}-01-01` <= this.max);
      },
      dayClass(day) {
        const value = this.iso(this.viewYear, this.viewMonth, day);
        if (value === this.modelValue) return 'bg-teal hover:bg-lteal font-bold';
        if (value === this.todayIso) return 'ring-1 ring-inset ring-lteal hover:bg-surface-4';
        return 'hover:bg-surface-4';
      },
      toggle() {
        if (this.open) {
          this.open = false;
          return;
        }
        // Open on the chosen date's month, or today's
        const [y, m] = (this.modelValue || this.todayIso).split('-').map(Number);
        this.viewYear = y;
        this.viewMonth = m - 1;
        this.view = 'days';
        this.open = true;
      },
      step(direction) {
        if (this.view === 'days') {
          const next = new Date(this.viewYear, this.viewMonth + direction, 1);
          this.viewYear = next.getFullYear();
          this.viewMonth = next.getMonth();
        } else {
          this.viewYear += direction * (this.view === 'months' ? 1 : 12);
        }
      },
      zoomOut() {
        this.view = this.view === 'days' ? 'months' : 'years';
      },
      pick(value) {
        this.open = false;
        if (value === this.modelValue) return;
        this.$emit('update:modelValue', value);
        this.$emit('change', value);
      },
      formatDate(value) {
        const [year, month, day] = value.split('-');
        return DATE_FORMAT.format(new Date(year, month - 1, day));
      },
      // composedPath is fixed when the click starts, so a month or year button that re-renders the panel still counts as inside
      handleClickOutside(event) {
        if (this.open && this.$refs.root && !event.composedPath().includes(this.$refs.root)) {
          this.open = false;
        }
      },
    },
  };
</script>
