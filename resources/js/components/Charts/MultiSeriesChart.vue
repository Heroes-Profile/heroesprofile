<template>
  <div>
    <canvas ref="canvas" width="1500" height="600"></canvas>
  </div>
</template>

<script>
import { markRaw } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Like LineChart, but several series on one axis (e.g. you vs global).
// series: [{ label, data: [numbers aligned with labels], color: 'teal' | 'yellow' | 'red' | 'blue' | 'green' | 'gray',
//            dashed?, hidden? (off until clicked in the legend), axis?: 'right' (own scale, y2Label),
//            n?: [games behind each value, shown in the tooltip],
//            points?: [a colour name per value, drawn as a dot on a line, e.g. win/loss] }]
// format / y2Format: 'number' | 'percent' | 'mmss' | 'pts', for axis ticks and tooltips.
// reference: a dashed line across the chart at that value (50 for win rates, 0 for leads), named referenceLabel.
const COLORS = {
  teal: '0, 139, 139',
  yellow: '217, 140, 64',
  red: '179, 54, 22',
  blue: '49, 91, 182',
  green: '64, 160, 24',
  gray: '140, 140, 150',
};

const PHONE = '(max-width: 767px)';

export function formatValue(value, format) {
  if (value === null || value === undefined || Number.isNaN(value)) return '-';
  if (format === 'percent') return round(value) + '%';
  if (format === 'pts') return (value > 0 ? '+' : '') + round(value) + ' pts';
  if (format === 'mmss') {
    const sign = value < 0 ? '-' : '';
    const s = Math.round(Math.abs(value));
    return sign + Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
  }
  return round(value).toLocaleString('en-US');
}

function round(value) {
  return Math.round(value * 10) / 10;
}

export default {
  name: 'MultiSeriesChart',
  props: {
    type: { type: String, default: 'line' },
    labels: Array,
    series: Array,
    title: String,
    xLabel: String,
    yLabel: String,
    y2Label: String,
    format: { type: String, default: 'number' },
    y2Format: { type: String, default: 'number' },
    reference: { type: Number, default: null },
    referenceLabel: { type: String, default: '' },
    stacked: { type: Boolean, default: false },
    horizontal: { type: Boolean, default: false },
    // Fixed value axis bounds, e.g. 0-100 for stacked percentages
    min: { type: Number, default: null },
    max: { type: Number, default: null },
  },
  data() {
    return {
      chart: null,
    };
  },
  computed: {
    // What the chart draws, so a parent re-render with the same numbers doesn't redraw it
    content() {
      return JSON.stringify([this.labels, this.series, this.reference]);
    },
  },
  mounted() {
    const phone = window.matchMedia(PHONE).matches;
    const valueAxis = this.horizontal ? 'x' : 'y';
    const categoryAxis = this.horizontal ? 'y' : 'x';
    const format = this.format;
    const y2Format = this.y2Format;
    const scales = {
      [categoryAxis]: {
        stacked: this.stacked,
        grid: { display: true },
        ticks: { display: true, ...(phone && !this.horizontal ? { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 } : {}) },
        title: { display: !!this.xLabel, text: this.xLabel },
      },
      [valueAxis]: {
        stacked: this.stacked,
        // Counts never go negative; also keeps an all-zero series from centring on 0
        beginAtZero: true,
        ...(this.min !== null ? { min: this.min } : {}),
        ...(this.max !== null ? { max: this.max } : {}),
        grid: { display: true },
        ticks: { display: true, callback: value => formatValue(value, format) },
        title: { display: !!this.yLabel, text: this.yLabel },
      },
    };
    if (!this.horizontal) {
      scales.y2 = {
        display: this.series.some(s => s.axis === 'right'),
        position: 'right',
        beginAtZero: true,
        grid: { display: false },
        ticks: { callback: value => formatValue(value, y2Format) },
        title: { display: !!this.y2Label, text: this.y2Label },
      };
    }
    this.chart = markRaw(new Chart(this.$refs.canvas.getContext('2d'), {
      type: this.type,
      data: { labels: this.labels, datasets: this.datasets() },
      options: {
        responsive: true,
        // Phones: a taller plot so it isn't a thin strip
        aspectRatio: phone ? 1.3 : (this.horizontal ? 2 : 2.5),
        indexAxis: this.horizontal ? 'y' : 'x',
        interaction: { mode: 'index', intersect: false },
        plugins: {
          title: { display: !!this.title, text: this.title },
          legend: { labels: { filter: (item, data) => !data.datasets[item.datasetIndex].reference || !!item.text } },
          tooltip: {
            filter: item => !item.dataset.reference,
            callbacks: {
              label: item => {
                const value = this.horizontal ? item.parsed.x : item.parsed.y;
                const n = item.dataset.n ? item.dataset.n[item.dataIndex] : null;
                const f = item.dataset.yAxisID === 'y2' ? y2Format : format;
                return item.dataset.label + ': ' + formatValue(value, f) + (n !== null && n !== undefined ? ' (' + n.toLocaleString('en-US') + ' games)' : '');
              },
            },
          },
        },
        scales,
      },
    }));
  },
  watch: {
    content() {
      if (!this.chart) {
        return;
      }
      this.chart.data.labels = this.labels;
      this.chart.data.datasets = this.datasets();
      this.chart.update();
    },
  },
  beforeUnmount() {
    this.chart?.destroy();
  },
  methods: {
    datasets() {
      const sets = this.series.map(s => {
        const rgb = COLORS[s.color] || COLORS.teal;
        return {
          label: s.label,
          data: s.data,
          n: s.n || null,
          backgroundColor: `rgba(${rgb}, ${this.type === 'bar' ? 0.7 : 0.2})`,
          borderColor: `rgba(${rgb}, 1)`,
          borderWidth: this.type === 'bar' ? 0 : 2,
          pointRadius: s.points ? 4 : 0,
          pointHoverRadius: s.points ? 5 : 3,
          ...(s.points ? { pointBackgroundColor: s.points.map(c => `rgba(${COLORS[c] || rgb}, 1)`), pointBorderColor: s.points.map(c => `rgba(${COLORS[c] || rgb}, 1)`) } : {}),
          spanGaps: true,
          borderDash: s.dashed ? [6, 4] : [],
          hidden: !!s.hidden,
          ...(this.horizontal ? {} : { yAxisID: s.axis === 'right' ? 'y2' : 'y' }),
        };
      });
      if (this.reference !== null && !this.horizontal) {
        sets.push({
          type: 'line',
          reference: true,
          label: this.referenceLabel,
          data: (this.labels || []).map(() => this.reference),
          borderColor: 'rgba(160, 160, 170, 0.8)',
          borderWidth: 1,
          borderDash: [4, 4],
          pointRadius: 0,
          pointHoverRadius: 0,
          fill: false,
          yAxisID: 'y',
          order: -1,
        });
      }
      return sets;
    },
  },
};
</script>
