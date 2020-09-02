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
-->
  <?php


  if (Auth::check()) {
      $user = Auth::user();
      //print_r(json_encode($user, true));
      print_r(json_decode(json_encode($user),true)["battletag"]);
  }


   ?>
</div>

</nav>
