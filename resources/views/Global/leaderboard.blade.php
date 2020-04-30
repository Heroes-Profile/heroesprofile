@extends('layouts.app')
@section('title', 'Global Leaderboard')


@section('content')
@include('filters.leaderboard')

    <div class="container">
        <table id="leaderboard-table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
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
  $("#hero-picker").prop("disabled", true);
  $("#role-picker").prop("disabled", true);

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
  hiddenColumn = [];

  //Filters/Parameters
  var formData = $('#basic_search').serializeArray();

  parameters =
  {
    'page' : 'leaderboard',
    'data' : formData
  }

  $.ajax({
    url: inputUrl,
    data: parameters,
    success: function(results){
      createTableJS('#leaderboard-table', results, inputColumns, hiddenColumn, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder);
    }
  });


  $('#basic_search').on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) {
    var formData = $('#basic_search').serializeArray();
    parameters =
    {
      'page' : 'leaderboard',
      'data' : formData
    }

    $.ajax({
      url: inputUrl,
      data: parameters,
      success: function(results){
        var table = $('#leaderboard-table').DataTable();
        table
            .clear()
            .rows.add(results)
            .draw();
      }
    });

  });
  $('.dataTables_length').addClass('bs-select');


  $('#type-picker').change(function(){
    switch($('#type-picker').find(":selected").val()){
      case "hero":
        $("#hero-picker").removeAttr("disabled");
        $("#role-picker").prop("disabled", true);
        break;
      case "role":
        $("#hero-picker").prop("disabled", true);
        $("#role-picker").removeAttr("disabled");
        break;
      default:
        $("#hero-picker").prop("disabled", true);
        $("#role-picker").prop("disabled", true);
        break;
    }
    $('.selectpicker').selectpicker('refresh');
  });
});


</script>
@endsection
