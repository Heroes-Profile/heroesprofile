



<nav class="primary-nav navbar navbar-dark bg-dark">
<a class="navbar-brand" href="/">
Heroes
<img src="/images/logo/heroesprofilelogo.png" width="34" height="30" class="d-inline-block align-top" alt="">
Profile
</a>

<div class="header-buttons">
    <a class="header-button btn btn-sm btn-primary" href="https://api.heroesprofile.com" style="background-color:#007272; margin-right:auto;" target="_blank">Heroes Profile API</a>
    <a class="header-button btn btn-sm btn-secondary" href="/Series" style="background-color:#315bb6; margin-right:auto;" target="_blank">Amateur Series</a>
    <a class="header-button btn btn-sm btn-danger" href="https://www.patreon.com/heroesprofile" target="_blank">Become a Patreon</a>
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
                <a class="dropdown-item" href="/home">
                    {{ __('Profile') }}
                </a>

                <a class="dropdown-item" href="/logout/battlenet">
                    {{ __('Log Out') }}
                </a>
            </div>
        </li>
    @endguest
</ul>
</nav>
<nav class="secondary-nav navbar navbar-expand-lg navbar-dark">

<button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse ml-auto" id="navbarSupportedContent">
  <ul class="navbar-nav ml-auto">

    <li class="nav-item">
      <a class="nav-link active" href="/Global/Leaderboard">Leaderboard</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats">Hero Stats</a>
    </li>


    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats/Maps">Hero Map Stats</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats/Matchups">Matchups</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats/Talents">Hero Talents</a>
    </li>
    <form class="form-inline my-2 my-lg-0 header-search-form">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-sm btn-primary my-2 my-sm-0" type="submit">Search</button>
    </form>
  </ul>

    {{--
    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats/Talents/Builder">Talent Builder</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/Global/Stats/Compositions">Compositions</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" href="/Compare">Compare</a>
    </li>

    <li class="nav-item">
      <a class="nav-link" target="_blank" href="https://api.heroesprofile.com/upload">Replay Uploader</a>
    </li>
  </ul>
  <form class="form-inline my-2 my-lg-0">
    <input class="form-control mr-sm-2" type="search" placeholder="Battletag" aria-label="Battletag">
    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Find a Player</button>
  </form>
  --}}
</div>

</nav>
