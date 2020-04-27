@extends('layouts.app')
@section('title', 'Global Stats')






@section('content')
@include('filters.filters')


    <div class="container">
        <table id="table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th data-field="hero">Hero</th>
                <th data-field="win_rate">Win Rate</th>
                <th data-field="change">Change</th>
                <th data-field="popularity">Popularity</th>
                <th data-field="pick_rate">Pick Rate</th>
                <th data-field="ban_rate">Ban Rate</th>
                <th data-field="influence">Influence</th>
                <th data-field="games_played">Games Played</th>
                <th data-field="wins">Wins</th>
                <th data-field="losses">Losses</th>
                <th data-field="games_banned">Games Banned</th>
            </tr>
        </thead>
    </table>
    <div id="echodata">
    </div>
    </div>

@endsection

@section('scripts')

<!--
Child Row example for show talent builds
https://datatables.net/examples/api/row_details.html
-->

<?php
$timesframes = array("2.49.2.77981");
$game_type = array("5");
$region = array();
$game_map = array();
$hero_level = array();
$hero = "";
$role = "";
$stat_type = array();
$player_league_tier = array();
$hero_league_tier = array();
$role_league_tier = array();
$mirror = array(0);
 ?>

<script>
$(document).ready(function() {
  inputUrl = '/getGlobalStatData';
  inputColumns = [
      { data: "hero"},
      { data: "win_rate" },
      { data: "change" },
      { data: "popularity" },
      { data: "pick_rate" },
      { data: "ban_rate" },
      { data: "influence" },
      { data: "games_played" },
      { data: "wins" },
      { data: "losses" },
      { data: "games_banned" },
  ];
  inputPaging = false;
  inputSearching = false;
  inputColReorder = true;
  inputFixedHeader = true;
  inputBInfo = false;
  inputSortOrder = [[ 1, "desc" ]];

  //Filters/Parameters
  stat_page = 'stat';
  timeframe_type = "minor";
  timeframe = {!! json_encode($timesframes) !!};
  region = {!! json_encode($region) !!};
  stat_type = {!! json_encode($stat_type) !!};
  hero_level = {!! json_encode($hero_level) !!};
  role = {!! json_encode($role) !!};
  hero = {!! json_encode($hero) !!};

  createTableAjax('#table', inputUrl, inputColumns, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder, stat_page);
    $('.dataTables_length').addClass('bs-select');
});
</script>
@endsection
