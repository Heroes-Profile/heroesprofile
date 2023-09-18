<template>
  <div id="hero-bubble-chart">
    <canvas ref="chartRef" width="1500" height="750"></canvas>
  </div>
</template>

<script>
  import { Chart, BubbleController, CategoryScale, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
  Chart.register(BubbleController, CategoryScale, LinearScale, PointElement, Title, Tooltip);

  export default {
    name: 'BubbleChart',
    props: {
      heroData: Array,
    },
    data() {
      return {
        chartRef: null,
      };
    },
    mounted() {
      this.chartRef = this.$refs.chartRef;
      const ctx = this.chartRef.getContext('2d');
      let images = {};

    // Preload images
      Promise.all(
        this.heroData.map((hero) => {
          return new Promise((resolve) => {
            const img = new Image();
            img.src = `/images/heroes/circular/${hero.short_name}.png`;
            let radius = this.calculateRadius(hero);
            img.height = radius;
            img.width = radius;
            img.style.borderRadius = "50%";

            console.log(img);


            img.onload = () => {
              images[hero.short_name] = img;
              resolve();
            };

          });
        })
        ).then(() => {
          const chartdata = this.heroData.map((hero) => ({
            label: hero.name,
            pointStyle: images[hero.short_name],
            radius: this.calculateRadius(hero),
            data: [
            {
              x: parseFloat(hero.win_rate).toFixed(2),
              y: hero.games_played,
              r: this.calculateRadius(hero)
            }
            ]
          }));

          new Chart(ctx, {
            type: 'bubble',
            data: {
              labels: "",
              datasets: chartdata
            },
            options: {
              responsive: true,
              legend: {
                display: false
              },
              title: {
                display: true,
                text: 'Games Played vs. Win Rate (Win Rate vs. Pick Rate vs. Ban Rate)'
              },
              scales: {
                x: {
                  title: {
                    display: true,
                    text: 'Win Rate'
                  }
                },
                y: {
                  title: {
                    display: true,
                    text: 'Games Played'
                  }
                }
              },
              plugins: {
                title: {
                  display: true,
                  text: 'Games Played vs. Win Rate (Win Rate vs. Pick Rate vs. Ban Rate)'
                },
                tooltip: {
                  mode: 'point',
                  displayColors: false
                },
                legend: {
                  display: false
                }
              }
            }
          });
        });
      },
      methods: {
        calculateRadius(hero) {
          //This works for initial load of page, but if the page is resized without a reload, the images dont change.  This can be fixed through recreating the chart any time the page changes size, but there are performance issues with this.
          
          const baseWidth = 1500;
          const baseHeight = 750;
          const currentWidth = this.chartRef.clientWidth;
          const currentHeight = this.chartRef.clientHeight;

          const scaleFactor = Math.min(currentWidth / baseWidth, currentHeight / baseHeight);

          let radius;

          if(hero.ban_rate > 0){
            radius = (((hero.win_rate + hero.pick_rate + hero.ban_rate) / 300) * 200) * scaleFactor;
          }else{
            radius = ((hero.win_rate + (hero.pick_rate / 200)) * 200) * scaleFactor;
          }

          return parseFloat(radius.toFixed(2));
        }


      }
    }
  </script>

  <style scoped>
    #hero-bubble-chart {
      width: 100%;
      height: 100%;
      max-width: 1500px;
      max-height: 750px;
    }
    #hero-bubble-chart canvas {
      width: 100% !important;
      height: 100% !important;
    }
  </style>
