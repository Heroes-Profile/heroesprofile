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
          <table id="talent-details-table-level-one" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 1</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-four" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 4</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-seven" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 7</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-ten" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 10</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-thirteen" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 13</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-sixteen" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 16</caption>

            <thead>
              <tr>
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

          <table id="talent-details-table-level-twenty" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
            <caption style='caption-side: top'>Level 20</caption>

            <thead>
              <tr>
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


        <h1>Talent Builds</h1>
          <div class="container">
              <table id="talent-builds-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
              <thead>
                  <tr>
                    <th data-field="talents">Talents</th>
                    <th data-field="copy_build_to_game">Copy Build to Game</th>
                    <th data-field="games_played">Total</th>
                    <th data-field="wins">Wins</th>
                    <th data-field="losses">Losses</th>
                    <th data-field="win_rate">Win Chance</th>
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
  inputFixedHeader = false;
  inputBInfo = false;
  inputSortOrder = [[ 1, "desc" ]];
  param = 'map';
  createTableAjax('#map-table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);

  inputColumns = [
      { data: "hero"},
      { data: "win_rate_as_ally" },
      { data: "win_rate_against" },
      { data: "games_played_as_ally" },
      { data: "games_played_against" },
  ];
  inputSearching = true;
  inputSortOrder = [[ 0, "desc" ]];
  param = 'matchups';
  //createTableAjax('#matchups-table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);


  stat_page = 'talent-details';
  inputSearching = false;
  $.ajax({
    url: inputUrl,
    data: {
      'page' : stat_page
    },
    success: function(results){
      inputColumns = [
          { data: "sort"},
          { data: "title" },
          { data: "win_rate" },
          { data: "popularity" },
          { data: "games_played" },
          { data: "wins" },
          { data: "losses" },
      ];
      inputSortOrder = [[ 0, "asc" ], [1, "asc"]];

      hiddenColumn = [{ "visible": false, "targets": 0 }];


      createTableJS('#talent-details-table-level-one', results[1], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-four', results[4], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-seven', results[7], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-ten', results[10], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-thirteen', results[13], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-sixteen', results[16], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
      createTableJS('#talent-details-table-level-twenty', results[20], inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);
    }
  });

  inputColumns = [
    { data: "talents"},
    { data: "copy_build_to_game" },
    { data: "games_played" },
    { data: "wins" },
    { data: "losses" },
    { data: "win_rate" },
  ];
  inputSortOrder = [[ 5, "desc" ]];
  param = 'talent-builds';
  createTableAjax('#talent-builds-table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, param);


  //$('.dataTables_length').addClass('bs-select');

});
</script>
@endsection
