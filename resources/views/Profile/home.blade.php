@extends('layouts.app')
@section('title', 'Profile')

@section('content')
  <div class="container center">
      <h1 class="display-1">Zemill</h1>
  </div>

  @include('nav.profile')

  @include('filters.profile')

    <div class="container">
      MAIN STATS SECTION
      <div class="card">
        <p id='wins'>Wins</p>
        <p id='losses'>Losses</p>
        <p id='first_to_ten_win_rate'>First To Ten Win Rate</p>
        <p id='second_to_ten_win_rate'>Second To Ten Win Rate</p>
        <p id='kdr'>KDR</p>
        <p id='kda'>KDA</p>
        <p id='account_level'>Account Level</p>
        <p id='win_rate'>Win Rate</p>
        <p id='bruiser_win_rate'>Bruiser Win Rate</p>
        <p id='support_win_rate'>Support Win Rate</p>
        <p id='ranged_assassin_win_rate'>Ranged Assassin Win Rate</p>
        <p id='melee_assassin_win_rate'>Melee Assassin Win Rate</p>
        <p id='healer_win_rate'>Healer Win Rate</p>
        <p id='tank_win_rate'>Tank Win Rate</p>
        <p id='game_length_total'>Total Time Played</p>
      </div>
    </div>

    <div class="container">
      HEROES SECTION
      <div class="card">
        MOST PLAYED HEROES
        <p id='three_highest_games_played_heroes_1'>Hero 1</p>
        <p id='three_highest_games_played_heroes_1_win_rate'>Win Rate 1</p>
        <p id='three_highest_games_played_heroes_1_games_played'>Games Played 1</p>

        <p id='three_highest_games_played_heroes_2'>Hero 2</p>
        <p id='three_highest_games_played_heroes_2_win_rate'>Win Rate 2</p>
        <p id='three_highest_games_played_heroes_2_games_played'>Games Played 2</p>

        <p id='three_highest_games_played_heroes_3'>Hero 3</p>
        <p id='three_highest_games_played_heroes_3_win_rate'>Win Rate 3</p>
        <p id='three_highest_games_played_heroes_3_games_played'>Games Played 3</p>

      </div>

      <div class="card">
        HIGHEST WIN RATE HEROES
        <p id='three_highest_win_rate_heroes_1'>Hero 1</p>
        <p id='three_highest_win_rate_heroes_1_win_rate'>Win Rate 1</p>
        <p id='three_highest_win_rate_heroes_1_games_played'>Games Played 1</p>

        <p id='three_highest_win_rate_heroes_2'>Hero 2</p>
        <p id='three_highest_win_rate_heroes_2_win_rate'>Win Rate 2</p>
        <p id='three_highest_win_rate_heroes_2_games_played'>Games Played 2</p>

        <p id='three_highest_win_rate_heroes_3'>Hero 3</p>
        <p id='three_highest_win_rate_heroes_3_win_rate'>Win Rate 3</p>
        <p id='three_highest_win_rate_heroes_3_games_played'>Games Played 3</p>

      </div>

      <div class="card">
        LATEST PLAYED HEROES
        <p id='three_latest_heroes_1'>Hero 1</p>
        <p id='three_latest_heroes_1_win_rate'>Win Rate 1</p>
        <p id='three_latest_1_games_played'>Games Played 1</p>

        <p id='three_latest_heroes_2'>Hero 2</p>
        <p id='three_latest_heroes_2_win_rate'>Win Rate 2</p>
        <p id='three_latest_heroes_2_games_played'>Games Played 2</p>

        <p id='three_latest_heroes_3'>Hero 3</p>
        <p id='three_latest_heroes_3_win_rate'>Win Rate 3</p>
        <p id='three_latest_heroes_3_games_played'>Games Played 3</p>

      </div>
    </div>


    <p id='data'>Data</p>


@endsection

@section('scripts')
  <script src="{{ asset('js/bootbox.min.js') }}"></script><!--http://bootboxjs.com/-->
  <script src="{{ asset('js/popup.js') }}"></script>
  <script>
  $(document).ready(function() {

    //Update Later to better code/process
    $('#profile-nav-link').addClass('active');
    $('#profile-friendsAndFoes-nav-link').removeClass('active');
    $('#profile-heroes-nav-link').removeClass('active');
    $('#profile-maps-nav-link').removeClass('active');
    $('#profile-match-history-nav-link').removeClass('active');
    $('#profile-matchups-nav-link').removeClass('active');
    $('#profile-mmr-nav-link').removeClass('active');
    $('#profile-roles-nav-link').removeClass('active');
    $('#profile-talents-nav-link').removeClass('active');

    var formData = $('#basic_search').serializeArray();
    var dialog = showPop();


    parameters =
    {
      'page' : @json($page),
      'data' : formData,
      'blizz_id' : 67280,
      'region' : 1,
      'game_type' : "",
      'season' : "",
    }

    $.ajax({
      url: @json($inputUrl),
      data: parameters,
      //type: "POST",
      success: function(results){
        $('#wins').text(results.wins);
        $('#losses').text(results.losses);
        $('#first_to_ten_win_rate').text(results.first_to_ten_win_rate);
        $('#second_to_ten_win_rate').text(results.second_to_ten_win_rate);
        $('#kdr').text(results.kdr);
        $('#kda').text(results.kda);
        $('#account_level').text(results.account_level);
        $('#win_rate').text(results.win_rate);
        $('#bruiser_win_rate').text(results.bruiser_win_rate);
        $('#support_win_rate').text(results.support_win_rate);
        $('#ranged_assassin_win_rate').text(results.ranged_assassin_win_rate);
        $('#melee_assassin_win_rate').text(results.melee_assassin_win_rate);
        $('#healer_win_rate').text(results.healer_win_rate);
        $('#tank_win_rate').text(results.tank_win_rate);
        $('#game_length_total').text(results.game_length_total);


        var counter = 1;
        for (var key in results.three_highest_games_played_heroes) {
            $('#three_highest_games_played_heroes_' + counter).text(key);
            $('#three_highest_games_played_heroes_' + counter + '_win_rate').text((results.three_highest_games_played_heroes[key].wins / results.three_highest_games_played_heroes[key].games_played) * 100);
            $('#three_highest_games_played_heroes_' + counter +'_games_played').text(results.three_highest_games_played_heroes[key].games_played);
            counter++;
        }

        var counter = 1;
        for (var key in results.three_highest_win_rate_heroes) {
            $('#three_highest_win_rate_heroes_' + counter).text(key);
            $('#three_highest_win_rate_heroes_' + counter + '_win_rate').text(results.three_highest_win_rate_heroes[key].win_rate);
            $('#three_highest_win_rate_heroes_' + counter +'_games_played').text(results.three_highest_win_rate_heroes[key].wins + results.three_highest_win_rate_heroes[key].losses);
            counter++;
        }

        var counter = 1;
        for (var key in results.three_latest_heroes) {
            $('#three_latest_heroes_' + counter).text(key);
            $('#three_latest_heroes_' + counter + '_win_rate').text((results.three_latest_heroes[key].wins / (results.three_latest_heroes[key].wins + results.three_latest_heroes[key].losses)) * 100);
            $('#three_latest_heroes_' + counter +'_games_played').text(results.three_latest_heroes[key].games_played);
            counter++;
        }

        $('#data').text(JSON.stringify(results))
        dialog.modal('hide');
        console.log(results);
      }
    });


  });
  </script>


@endsection
