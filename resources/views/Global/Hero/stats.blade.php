@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('content')
@include('filters.globals')

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
              <th data-field="win_rate_as_enemy">Win Rate Against "INSERT HERO NAME"</th>
              <th data-field="games_played_as_ally">Games Played As Ally</th>
              <th data-field="games_played_as_enemy">Games Played Against "INSERT HERO NAME"</th>
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
  var formData = $('#basic_search').serializeArray();


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
  columnDefinition = [];
  maps_parameters =
  {
    'page' : 'map',
    'data' : formData
  }

  $.ajax({
    url: inputUrl,
    data: maps_parameters,
    success: function(map_results){
      createTableJS('#map-table', map_results, inputColumns, columnDefinition, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder);
    }
  });



  matchup_inputColumns = [
      { data: "hero"},
      { data: "win_rate_as_ally" },
      { data: "win_rate_as_enemy" },
      { data: "games_played_as_ally" },
      { data: "games_played_as_enemy" },
  ];
  matchup_inputSearching = true;
  matchup_inputSortOrder = [[ 0, "asc" ]];
  matchup_parameters =
  {
    'page' : 'matchups',
    'data' : formData
  }

  $.ajax({
    url: inputUrl,
    data: matchup_parameters,
    success: function(matchup_results){
      createTableJS('#matchups-table', matchup_results, matchup_inputColumns, columnDefinition, inputPaging, matchup_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, matchup_inputSortOrder);
    }
  });

  talent_details_parameters =
  {
    'page' : 'talent-details',
    'data' : formData
  }

  talent_details_inputSearching = false;
  talent_details_inputSortOrder = [[ 0, "asc" ], [1, "asc"]];
  talent_details_columnDefinition = [{ "visible": false, "targets": 0 }];

  $.ajax({
    url: inputUrl,
    data: talent_details_parameters,
    success: function(talent_details_results){
      inputColumns = [
          { data: "sort"},
          { data: "title" },
          { data: "win_rate" },
          { data: "popularity" },
          { data: "games_played" },
          { data: "wins" },
          { data: "losses" },
      ];



      createTableJS('#talent-details-table-level-one', talent_details_results[1], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-four', talent_details_results[4], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-seven', talent_details_results[7], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-ten', talent_details_results[10], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-thirteen', talent_details_results[13], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-sixteen', talent_details_results[16], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
      createTableJS('#talent-details-table-level-twenty', talent_details_results[20], inputColumns, talent_details_columnDefinition, inputPaging, talent_details_inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_details_inputSortOrder);
    }
  });


  talent_builds_inputColumns = [
    { data: "talents",
    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
         $(nTd).html(oData.level_one.title +
           "|" + oData.level_four.title +
           "|" + oData.level_seven.title +
           "|" + oData.level_ten.title +
           "|" + oData.level_thirteen.title +
           "|" + oData.level_sixteen.title +
           "|" + oData.level_twenty.title
         );
     }
    },
    { data: "copy_build_to_game" },
    { data: "games_played" },
    { data: "wins" },
    { data: "losses" },
    { data: "win_rate" },
  ];
  talent_builds_inputSortOrder = [[ 5, "desc" ]];
  talent_builds_param = {
    'page' : 'talent-builds',
    'data' : formData
  }


  $.ajax({
    url: inputUrl,
    data: talent_builds_param,
    success: function(talent_builds_results){
      createTableJS('#talent-builds-table', talent_builds_results, talent_builds_inputColumns, columnDefinition, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_builds_inputSortOrder);
    }
  });


  //$('.dataTables_length').addClass('bs-select');

});
</script>
@endsection
