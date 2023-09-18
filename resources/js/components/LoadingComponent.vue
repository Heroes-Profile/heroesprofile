<template>
  <div class="loading-container">
    <img src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
    <div class="loading-text">Loading data...</div>
  </div>
</template>

<script>
export default {
  name: 'LoadingComponent',
};
</script>

<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding-top: 10%; /* Adjust as needed to only occupy the top half */
  gap: 20px;
  background-color: rgba(0, 0, 0, 0.95); /* Translucent black background */
  width: 100%; /* Take full width of the parent container */
  height: 100%; /* Take full height of the parent container */
  position: absolute; /* Positioning relative to the nearest positioned ancestor */
}

.loading-text {
  font-family: 'Ruslan Display', cursive;
  font-size: 24px;
  text-align: center;
  color: #fff; /* White color text */
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
  animation: spin 2.5s linear infinite;
  width: 121.5; /* Adjust width as needed */
  height: 108px; /* Adjust height as needed */
}
</style>

