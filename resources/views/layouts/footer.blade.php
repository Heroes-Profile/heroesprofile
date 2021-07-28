<footer class="mt-3">
  <div class="container-fluid grey-background">
    <div class="container">
      <div class="row">
        <div class="footer-logo col-2">
          <img class="footer-logo" src="/images/logo/heroesprofilelogo.png">
        </div>
        <div class="footer-info col">
          <nav class="navbar">  <div class="header-buttons">
            <a class="header-button btn btn-sm btn-secondary" href="https://www.heroesprofile.com/" target="_blank">Heroes Profile</a>
            <a class="header-button btn btn-sm btn-primary" href="https://api.heroesprofile.com"  target="_blank">Heroes Profile API</a>
          {{--  <a class="header-button btn btn-sm btn-secondary" href="/Series" target="_blank">Amateur Series</a>--}}
            <a class="header-button btn btn-sm btn-danger" href="https://www.patreon.com/heroesprofile" target="_blank">Remove Ads/Patreon</a>
          </div>
          <ul class="footer-menu">
            <li>
              <a href="/Privacy-Policy" rel="nofollow">Privacy Policy</a></li>
            </ul>
          </nav>

          <p class="primary-background primary-content">Heroes Profile provides individual player statistics and information for Heroes of the Storm.
            View Leaderboard data, player comparisons, Calculated MMR, Match History, Extensive Match statistics, and Player statistics for each hero and map.
          </p>
        </div>
      </div>

      <div class="replay-stats-header"><p>{{ number_format(getMaxReplayID()) }} replays | Patch {{ getMaxGameVersion() }} | Up to date as of:  <span class="date-format-2">{{ getMaxGameDate() }}</span></p>
        <p><a href="https://skilltreedevelopment.com">Skill Tree Development, LLC</a></p>

      </div>

      <div class="disclaimer"><p>Heroes Profile is funded by its developers. If you like the site, consider donating to our <a href="https://www.patreon.com/heroesprofile" target="_blank">Patreon</a>.  Even $1 helps. Heroes Profile has no affiliation with Blizzard or Heroes of the Storm. Data for the site provided by <a href="https://api.heroesprofile.com/upload" target="_blank">Heroes Profle API</a>. Upload your replays to Heroes Profile API for most accurate data.</p></div>
    </div>
  </div>
</footer>

<script>
  $(document).ready(function() {

    $('.date-format-2, .date-format').each(function(){
      var originaldatehtml = $(this).html();
      var originaldate = moment.tz(originaldatehtml,'Atlantic/Reykjavik');

      $(this).html(originaldate.clone().local().format('MM/DD/YYYY h:mm:ss a'));
      $(this).show();
      $(this).removeClass('date-format-2, date-format');
    });
  });
</script>
