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
            var closeButton = document.getElementById('mobile-nav-close');

            // Add a click event listener to the button
            toggleButton.addEventListener('click', function () {
                // Toggle the display property of the element
                if (elementToToggle.style.display === 'none' || elementToToggle.style.display === '') {
                    elementToToggle.style.display = 'block';
                    closeButton.style.display = "block";
                } else {
                    elementToToggle.style.display = 'none';
                }
            });
            closeButton.addEventListener('click', function () {
               
                    elementToToggle.style.display = 'none';
                    closeButton.style.display = 'none';
                
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