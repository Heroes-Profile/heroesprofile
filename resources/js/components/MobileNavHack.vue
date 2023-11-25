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
    function isSmallScreen() {
  return window.innerWidth <= 768; // You can adjust the threshold as needed
}

      document.addEventListener('DOMContentLoaded', function () {
            // Get the button and the element to toggle
            var toggleButton = document.getElementById('mobile-toggle');
            var elementToToggle = document.getElementById('main-menu');
            var closeButton = document.getElementById('mobile-nav-close');
            const buttons = document.querySelectorAll('.close-secondary-nav');

// Get all elements with the class "mobile-secondary-nav-open"
const openButtons = document.querySelectorAll('.mobile-secondary-nav-open');
const mobileNavButtons = document.querySelectorAll('.nav-item');
const dropdowns = document.querySelectorAll('nav-dropdown');


// Add a click event listener to each button

openButtons.forEach(function(button) {
  button.addEventListener('click', function(event) {
    if(isSmallScreen()){
    // Get the data-battletag value from the clicked link
    var battletag = button.getAttribute('data-battletag');
    
    // Find the corresponding ".nav-dropdown" using the data-battletag value
    var navDropdown = document.querySelector(`.nav-dropdown[data-battletag="${battletag}"]`);

    
      navDropdown.style.display = 'block';
    }
  });
});


mobileNavButtons.forEach(function(button){
    button.addEventListener('click', function(){
        if(isSmallScreen()){
            dropdowns.forEach(function(secondaryNav) {
                  dropdowns.style.display = 'none'; // or any other method to hide it
              });

                
        // Find the ".dropdown" element within the clicked ".click-me" container
        var dropdown = event.currentTarget.querySelector('.nav-dropdown');
            if(dropdown){
        // Toggle the visibility of the found ".dropdown"
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        } else {
          dropdown.style.display = 'block';
        }
            }
      
        }
    })

});




  // Add a click event listener to each button
        buttons.forEach(function(button) {
          button.addEventListener('click', function() {
             const secondaryNavs = document.querySelectorAll('.nav-dropdown-secondary-nav');
        
              // Iterate through and close each "secondary-nav" element
              secondaryNavs.forEach(function(secondaryNav) {
                  secondaryNav.style.display = 'none'; // or any other method to hide it
              });
               });
          });

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
    const openButtons = document.querySelectorAll('.mobile-secondary-nav-open');
  },
  computed: {
  },
  watch: {
  },
  methods: {
  }
}
</script>