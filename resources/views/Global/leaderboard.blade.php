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
              <th data-field="games_played">Games Played</th>
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
      { data: "split_battletag" },
      { data: "region" },
      { data: "win_rate" },
      { data: "rating" },
      { data: "conservative_rating" },
      { data: "games_played" },
  ];
  inputPaging = true;
  inputSearching = true;
  inputColReorder = true;
  inputFixedHeader = true;
  inputBInfo = true;
  inputSortOrder = [[ 4, "desc" ]];
  createTable(inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder);
    $('.dataTables_length').addClass('bs-select');
});
</script>
@endsection
