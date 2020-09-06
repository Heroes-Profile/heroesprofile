@extends('layouts.app')
@section('title', "Landing Page")
@section('content')
  <div class="container">
<div class="deathwing_logo"><img src="/images/logo/full_deathwing.png" /></div>
    <div class="byline"><h3>Heroes of the Storm statistics and comparison</h3></div>
  </div>
<div class="card-group">
<div class="card">
  <div class="card-header"><h3>In-depth player comparison</h3></div>
  <div class="card-body">

    <p class="card-text">See how you compare to other players or to a certain league tier. You can compare up to four players at one time.</p>
    <a href="/Compare" class="btn btn-primary">Compare</a>
  </div>
</div>
<div class="card">
<div class="card-header"><h3>Extensive player profile</h3></div>
  <div class="card-body">

    <p class="card-text">See all of your stats in one place: see data for individual maps or heroes played, match history and comparisons all from within a streamlined profile.</p>
    <a href="/" class="btn btn-primary">Find your Profile</a>
  </div>
</div>
<div class="card">
<div class="card-header"><h3>Variety of Leaderboards</h3></div>
  <div class="card-body">

    <p class="card-text">View leaderboards based on Player, Hero, or Role using Heroes Profile Rating.  Get talent builds, and navigate directly to player's profiles.</p>
    <a href="/Global/Leaderboard" class="btn btn-primary">View Leaderboards</a>
  </div>
</div>
</div>
<div class="container">



  <p>Heroes Profile uses data from Heroes Profile and HotsApi.  HotsApi is an open Heroes of the Storm replay database with user uploaded replay data.
  Currently, Heroes Profile has pulled {{ $maxReplayID }} replays up to and including data from patch {{ $maxGameVersion }} and date/time {{ $getMaxGameDate }}
  and incorporated them into our dataset.</p>
  <p >
  For more information on HotsAPI navigate to <a href="https://hotsapi.net/">https://hotsapi.net/</a>
</p>

</div>

@endsection
@section('scripts')
@endsection
