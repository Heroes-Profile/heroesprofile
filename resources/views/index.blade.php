@extends('layouts.app')
@section('title', "Landing Page")
@section('content')

    <div class="byline"><h3>Heroes of the Storm statistics and comparison</h3></div>
        <div class="main-content home-page">

<section class="search-wrapper">

  <p class="section-callout">Enter your battletag and we'll show you your stats and how you compare on Heroes of the Storm.  This will be your active battletag used throughout the site.</p>



<div class="search-bar-wrapper">
<div class="search-bar">
  <form id="battletag-search" action="">
            <input type="text" id="main-search" name="searched_battletag" placeholder="Enter your battletag"/>
            <input type="submit" value="Show My Stats"/>
          </form>

</div>
<div class="another-player">or <div class="main-link"><a href="/Search">Find another player's profile</a></div></div>
<div class="form-errors">
<div id="form_error"><?php if(isset($_GET['searched_battletag']) && count($foundBattletags) == 0){echo "No battletag found. Please try another search.";}else if(isset($_GET['searched_battletag']) && count($foundBattletags) >1){echo "<p>Multiple battletags were found. Choose from the options below</p>"; }?></div>
<div id="choose_battletag"><?php if(isset($_GET['searched_battletag'])){
  if(count($foundBattletags) > 1) {
    foreach($foundBattletags as $value){

    echo '<a href="?primary_blizz_id='.$value['blizz_id'].'&primary_battletag='.$value['battletag'].'&primary_region='.$value['regionid'].'">'.urldecode($value['battletag']).' ('.$value['region'].') | '.$value['gamesplayed'].' Games Played ' .
    " <div class='search-details-header'>Latest Match</div><div class='search-details'> " . $value["game_date"] . " - " . $value["game_map"] . " - " . $value["hero"] . '</div></a>';

    }
  }




}


  ?></div>
</div>


        </div>

</div>

</section>
  <div class="standard-page-content">
    <section>


<script>
var fullData = '';

</script>



</section>
</div>

<div class="main-content standard-page-content">


<section class="icon-section">

  <div class="icon-box">
    <div class="icon"><i class="fas fa-users"></i>
    </div>
  <h3>In-depth player comparison</h3>
  <div class="icon-text">
    <p>See how you compare to other players or to a certain league tier. You can compare up to four players at one time.</p>


  </div>
  <a class="choose-button" href="/Compare">Compare</a>

</div>

<div class="icon-box">
  <div class="icon"><i class="fas fa-address-card"></i></div>
  <h3>Extensive player profile</h3>
  <div class="icon-text">
    <p>See all of your stats in one place: see data for individual maps or heroes played, match history and comparisons all from within a streamlined profile. </p>
  </div>
  <a class="choose-button" href="/">Find your Profile</a>
</div>


<div class="icon-box">
  <div class="icon"><i class="fas fa-align-left"></i></div>
  <h3>Variety of Leaderboards</h3>
  <div class="icon-text">
    <p>View leaderboards based on Player, Hero, or Role using Heroes Profile Rating.  Get talent builds, and navigate directly to player's profiles.</p>
  </div>
  <a class="choose-button" href="/Global/Leaderboard">View Leaderboards</a>
</div>

</section>


  <section>
  <p class="section-callout">Heroes Profile uses data from Heroes Profile and HotsApi.  HotsApi is an open Heroes of the Storm replay database with user uploaded replay data.
  Currently, Heroes Profile has pulled {{ $maxReplayID }} replays up to and including data from patch {{ $maxGameVersion }} and date/time {{ $getMaxGameDate }}
  and incorporated them into our dataset.</p><p class="section-callout">
  For more information on HotsAPI navigate to <a style="color:white;" href="https://hotsapi.net/">https://hotsapi.net/</a></p>
  <!--<div class="hotsapi-logo-wrapper">
  <a href="https://hotsapi.net/">
  <img src="/includes/images/logo/hotsapi.png"/></a></div>-->
</section>
</div>

    </body>


</html>

@endsection
@section('scripts')
@endsection
