<template>

  <div class="loading-container z-40 w-full min-h-[50vh] ">
    <img v-if="overrideimage" loading="eager" :src="overrideimage" alt="loading" />
    <img v-else loading="eager" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
    <div class="loading-text">
      <div v-if="textoverride">
        <slot></slot>
        <span v-if="timer">~{{ countdown }} seconds remaining</span>
      </div>

      <span v-else>Loading data...</span>
    </div>
    <!-- Unforunately this only cancels the front end request, and I have found no way to cancel the backend request -->
    <!--<custom-button @click="cancelRequest" text="Cancel Request" alt="Cancel Request" size="small" :ignoreclick="true">Cancel Request</custom-button>-->
  </div>
</template>
<script>
  export default {
    name: 'LoadingComponent',
    components: {
    },
    props: {
      textoverride: Boolean,
      overrideimage: String,
      timer: Boolean,
      starttime: Number,
    },
    data(){
      return {
        countdown: this.starttime, // Initialize countdown with starttime
        timerInterval: null, // Store the interval reference
      }
    },
    created(){
      if (this.timer) {
        this.startTimer(this.starttime);
      }
    },
    mounted() {
    },
    computed: {
    },
    watch: {
    },
    methods: {
      startTimer() {
        if (this.countdown > 0) {
          this.timerInterval = setInterval(() => {
            this.countdown -= 1; // Decrement countdown
            if (this.countdown <= 0) {
              clearInterval(this.timerInterval); // Clear the interval when countdown reaches 0
            }
          }, 1000); // Update countdown every 1 second (1000 milliseconds)
        }
      },
      cancelRequest() {
        this.$emit('cancel-request');
      },
    },
    beforeDestroy() {
      // Clear the timer interval to prevent memory leaks when the component is destroyed
      clearInterval(this.timerInterval);
    },
  }
</script>

<style scoped>

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding-top: 5%; /* Adjust as needed to only occupy the top half */
  gap: 20px;
  background-color: rgba(0, 0, 0, 0.95); /* Translucent black background */
  width: 100%; /* Take full width of the parent container */
  height: 100%; /* Take full height of the parent container */
  
}

.loading-text {
  font-family: 'Ruslan Display', cursive;
  font-size: 24px;
  text-align: center;
  color: #fff;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.loading-container img {
  animation: spin 2.25s linear infinite;
  width: 121.5;
  height: 108px;
}
</style>

