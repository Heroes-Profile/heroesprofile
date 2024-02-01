<template>
  <div >
    <page-heading  :heading="'Privacy Policy'"></page-heading>
   
    <div class="max-w-[1500px] mx-auto mt-10">
    <p>
      This site uses cookies to enhance your experience and provide personalized features.
      By using our site, you consent to the use of cookies in accordance with this policy.
    </p>
    <br/>

    <h3>1. Cookies Usage</h3>

    <p>
      We use cookies to save your most recently viewed pages and to remember your selected
      "primary" battletag. Additionally, cookies help us determine whether you've seen the
      initial site popup message. These small pieces of data are stored on your local computer
      to remember your settings. We do not store personal data in cookies, and the information
      stored is solely for enhancing your site experience.
    </p>
    <br/>
    <h3>2. Google Analytics</h3>

    <p>
      We utilize Google Analytics to collect anonymous data about your session, helping us
      analyze site traffic and improve our services. Google Analytics may use cookies for tracking
      purposes. For more information on how Google uses data, you can refer to
      <a class="link" href="https://policies.google.com/technologies/partner-sites?hl=en-US" target="_blank">
        How Google uses data.
      </a>
    </p>
    <br/>

    <h3>3. Data Source and Ownership</h3>

    <p>
      Data for statistics and profiles is obtained from the Heroes Profile API, where users
      voluntarily upload their replay data from their computers. This data is stored in our site
      database and is not of a personal nature. We claim ownership over the aggregated data used for the site.
    </p>
    <br/>

    <h3>4. User Responsibilities</h3>

    <p>
      Users are expected to adhere to the terms of service and refrain from scraping data through
      the use of bots or scripts. Any misuse or abuse of the site may result in a ban.
    </p>
  </div>
</div>
</template>


<script>
export default {
  name: 'PrivacyPolicy',
  components: {
  },
  props: {
  },
  data(){
    return {
    }
  },
  created(){
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