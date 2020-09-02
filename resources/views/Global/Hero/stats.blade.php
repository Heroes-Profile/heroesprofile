@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('content')
@include('filters.globals')

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
  <script src="{{ asset('js/createTableJS.js') }}"></script>



<script>
$(document).ready(function() {
  var formData = $('#basic_search').serializeArray();

  inputPaging = false;
  inputSearching = false;
  inputColReorder = true;
  inputFixedHeader = false;
  inputBInfo = false;
  inputSortOrder = [[ 1, "desc" ]];
  columnDefinition = [];

  talent_details_parameters =
  {
    'page' : 'talent-details',
    'data' : formData
  }

  talent_details_inputSearching = false;
  talent_details_inputSortOrder = [[ 0, "asc" ], [1, "asc"]];
  talent_details_columnDefinition = [{ "visible": false, "targets": 0 }];

  $.ajax({
    url: @json($talentsInputUrl),
    data: talent_details_parameters,
    type: "POST",  //turn back on after testing

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
    url: @json($talentBuildsInputUrl),
    data: talent_builds_param,
    type: "POST",  //turn back on after testing

    success: function(talent_builds_results){
      createTableJS('#talent-builds-table', talent_builds_results, talent_builds_inputColumns, columnDefinition, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, talent_builds_inputSortOrder);
    }
  });


  //$('.dataTables_length').addClass('bs-select');

});
</script>
@endsection
