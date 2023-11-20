<template>
</template>

<script>
export default {
  name: 'MobileNavHack',
  components: {
  },
  props: {
  },
  data(){
    return {
    }
  },
  created(){

      document.addEventListener('DOMContentLoaded', function () {
            // Get the button and the element to toggle
            var toggleButton = document.getElementById('mobile-toggle');
            var elementToToggle = document.getElementById('main-menu');

            // Add a click event listener to the button
            toggleButton.addEventListener('click', function () {
                // Toggle the display property of the element
                if (elementToToggle.style.display === 'none' || elementToToggle.style.display === '') {
                    elementToToggle.style.display = 'block';
                } else {
                    elementToToggle.style.display = 'none';
                }
            });
        });
  },
  mounted() {
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>