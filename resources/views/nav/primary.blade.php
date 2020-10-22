@if(checkAlertForHeader())
  <div class="heading-alert alert alert-danger">{!! getAlertHeader() !!}</div>
@endif
<nav class="primary-nav navbar navbar-dark bg-dark">
  <a class="navbar-brand" href="/">
    Heroes
    <img src="/images/logo/heroesprofilelogo.png" width="34" height="30" class="d-inline-block align-top" alt="">
    Profile
  </a>

  <div class="header-buttons">
    <a class="header-button btn btn-sm btn-primary" href="https://api.heroesprofile.com"  target="_blank">Heroes Profile API</a>
    <a class="header-button btn btn-sm btn-secondary" href="/Series" target="_blank">Amateur Series</a>
    <a class="header-button btn btn-sm btn-danger" href="https://www.patreon.com/heroesprofile" target="_blank">Become a Patreon</a>
  </div>


</nav>
<nav class="secondary-nav navbar navbar-expand-lg navbar-dark">

  <button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse ml-auto" id="navbarSupportedContent">
    <ul class="navbar-nav ml-auto">

      <li class="nav-item">
        <a class="nav-link active" href="/Drafter" id="drafter-link">Drafter</a>
      </li>

      <li class="nav-item">
        <a class="nav-link active" href="/docs" id="drafter-link">Dcoumentation</a>
      </li>


      <li class="nav-item">
        <a class="nav-link active" href="/Global/Leaderboard" id="leaderboard-link">Leaderboard</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/Global/Stats" id="hero-stats-link">Hero Stats</a>
      </li>


      <li class="nav-item">
        <a class="nav-link" href="/Global/Stats/Maps" id="hero-map-stats-link">Hero Map Stats</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/Global/Stats/Matchups" id="hero-matchups-link">Matchups</a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/Global/Stats/Talents" id="hero-talents-link">Hero Talents</a>
      </li>
      <form class="form-inline my-2 my-lg-0 header-search-form">
        <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-sm btn-primary my-2 my-sm-0" type="submit">Search</button>
      </form>
    </ul>
</div>
<!-- Right Side Of Navbar -->
<ul class="navbar-nav">
  <!-- Authentication Links -->
  @guest
    <li class="nav-item">
      <a class="nav-link" href="/login/battlenet">{{ __('Battlenet Login') }}</a>
    </li>
  @else
    <li class="nav-item dropdown">
      <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
        {{ Auth::user()->battletag }}<span class="caret"></span>
      </a>

      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">

        <a class="dropdown-item" href="/Profile?blizz_id={{ getBlizzID(Auth::user()->battletag, Auth::user()->region) }}&battletag={{ substr(Auth::user()->battletag, 0, strpos(Auth::user()->battletag, "#")) }}&region={{ Auth::user()->region }}">
          {{ __('Profile') }}
        </a>

        <a class="dropdown-item" href="/Account">
          {{ __('Settings') }}
        </a>

        <a class="dropdown-item" href="/logout/battlenet">
          {{ __('Log Out') }}
        </a>
      </div>
    </li>
  @endguest
</ul>

</nav>
