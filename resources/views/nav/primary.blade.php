<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
<a class="navbar-brand" href="/">
Heroes
<img src="/images/logo/heroesprofilelogo.png" width="34" height="30" class="d-inline-block align-top" alt="">
Profile
</a>
<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>

<div class="collapse navbar-collapse" id="navbarSupportedContent">
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
  </ul>

  <!-- Right Side Of Navbar -->
<ul class="navbar-nav ml-auto">
    <!-- Authentication Links -->
    @guest
        <li class="nav-item">
            <a class="nav-link" href="/login/battlenet">{{ __('Battlenet Login') }}</a>
        </li>
    @else
        <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}<span class="caret"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="/home">
                    {{ __('Profile') }}
                </a>

                <a class="dropdown-item" href=""
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>

                <form id="logout-form" action="" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    @endguest
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
