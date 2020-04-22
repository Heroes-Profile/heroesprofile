@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('content')
  <h1>Maps</h1>
    <div class="container">
        <table id="map-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
        <thead>
            <tr>
              <th data-field="game_map">Map</th>
              <th data-field="win_rate">Win Rate</th>
              <th data-field="pick_rate">Pick Rate</th>
              <th data-field="popularity">Popularity</th>
              <th data-field="ban_rate">Ban Rate</th>
              <th data-field="games_played">Games Played</th>
              <th data-field="wins">Wins</th>
              <th data-field="losses">Losses</th>
              <th data-field="bans">Bans</th>
            </tr>
        </thead>
    </table>
    <div id="echodata">
    </div>
    </div>


    <h1>Matchups</h1>
      <div class="container">
          <table id="matchups-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
          <thead>
              <tr>
                <th data-field="hero">Hero</th>
                <th data-field="win_rate_as_ally">Win Rate As Ally</th>
                <th data-field="win_rate_against">Win Rate Against "INSERT HERO NAME"</th>
                <th data-field="games_played_as_ally">Games Played As Ally</th>
                <th data-field="games_played_against">Games Played Against "INSERT HERO NAME"</th>
              </tr>
          </thead>
      </table>
      <div id="echodata">
      </div>
      </div>


      <h1>Talent Details</h1>
        <div class="container">
            <table id="talent-details-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <thead>
                <tr>
                  <th data-field="level">Level</th>
                  <th data-field="sort">Sort</th>
                  <th data-field="title">Talent</th>
                  <th data-field="win_rate">Win Rate</th>
                  <th data-field="popularity">Popularity</th>
                  <th data-field="games_played">Games Played</th>
                  <th data-field="wins">Wins</th>
                  <th data-field="losses">Losses</th>
                </tr>
            </thead>
        </table>
        <div id="echodata">
        </div>
        </div>

@endsection

@section('scripts')



<script>
$(document).ready(function() {
  inputUrl = '/getGlobalHeroStatData';
  inputColumns = [
      { data: "game_map"},
      { data: "win_rate" },
      { data: "pick_rate" },
      { data: "popularity" },
      { data: "ban_rate" },
      { data: "games_played" },
      { data: "wins" },
      { data: "losses" },
      { data: "bans" },
  ];
  inputPaging = false;
  inputSearching = false;
  inputColReorder = true;
  inputFixedHeader = true;
  inputBInfo = false;
  inputSortOrder = [[ 1, "desc" ]];
  param = 'map';
  createTable('#map-table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
    $('.dataTables_length').addClass('bs-select');


    inputColumns = [
        { data: "level"},
        { data: "sort"},
        { data: "title" },
        { data: "win_rate" },
        { data: "popularity" },
        { data: "games_played" },
        { data: "wins" },
        { data: "losses" },
    ];
  param = 'talent-details';
  inputSortOrder = [[ 0, "asc" ], [1, "asc"]];

  createTable('#talent-details-table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);

});
</script>
@endsection
