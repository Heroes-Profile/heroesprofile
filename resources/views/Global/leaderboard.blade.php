@extends('layouts.app')
@section('title', 'Global Leaderboard')


@section('content')
    <div class="container">
        <table id="table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
        <thead>
            <tr>
              <th data-field="rank">Rank</th>
              <th data-field="split_battletag">Battletag</th>
              <th data-field="region">Region</th>
              <th data-field="win_rate">Win Rate</th>
              <th data-field="rating">Heroes Profile Rating</th>
              <th data-field="conservative_rating">MMR</th>
              <!--<th data-field="rank">Rank</th>-->
              <th data-field="games_played">Games Played</th>
              <th data-field="most_played_hero">Most Played Hero</th>
              <th data-field="hero_build_games_played">Games Played With Hero</th>
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
  inputUrl = '/getGlobalLeaderboardData';
  inputColumns = [
      { data: "rank" },
      { data: "split_battletag",
        "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
             $(nTd).html("<a href='/Profile/?blizz_id=" + oData.blizz_id + "&battletag=" + oData.split_battletag + "&region=" + oData.region+"'>" + oData.split_battletag + "</a>");
         }
      },
      { data: "region" },
      { data: "win_rate" },
      { data: "rating" },
      { data: "conservative_rating" },
      { data: "games_played" },
      { data: "most_played_hero" },
      { data: "hero_build_games_played" },
  ];
  inputPaging = true;
  inputSearching = true;
  inputColReorder = true;
  inputFixedHeader = true;
  inputBInfo = true;
  inputSortOrder = [[ 4, "desc" ]];

  //function createTableAjax(tableID, inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, stat_page) {

  createTableAjax('#table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, 'leaderboard');
    $('.dataTables_length').addClass('bs-select');
});
</script>
@endsection
