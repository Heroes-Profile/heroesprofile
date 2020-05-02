@extends('layouts.app')
@section('title', 'Global Leaderboard')


@section('content')
@include('filters.leaderboard')

<div class="container-fluid">
  <table id="{{ $tableid }}" class="table table-striped table-bordered table-sm " cellspacing="0" width="100%">
    <thead class="thead-dark">
      <tr>
        @foreach ($columns as $column)
          <th data-field="{{$column['key'] }}">{{ $column['text'] }}</th>
        @endforeach
      </tr>
    </thead>
  </table>
  <div id="echodata">
  </div>
</div>

@endsection

@section('scripts')

  <script src="{{ asset('js/createTableAjax.js') }}"></script>
  <script src="{{ asset('js/createTableJS.js') }}"></script>

<script>
var tableID = "#"+@json($tableid);
$(document).ready(function() {
  $("#hero-picker").prop("disabled", true);
  $("#role-picker").prop("disabled", true);
  $('.roles-selectpicker').selectpicker('refresh');
  $('.heroes-selectpicker').selectpicker('refresh');

  inputUrl = @json($inputUrl);

  inputColumns =
  inputColumns = [
    @foreach($columndata as $column)
    { data : @json($column)
      @if ($column == "split_battletag")
      ,
      "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
           $(nTd).html("<a href='/Profile/?blizz_id=" + oData.blizz_id + "&battletag=" + oData.split_battletag + "&region=" + oData.region+"'>" + oData.split_battletag + "</a>");
       }
       @elseif ($column == "most_played_build")
         ,
           "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(oData.level_one + "|" + oData.level_four+ "|" + oData.level_seven + "|" + oData.level_ten + "|" + oData.level_thirteen + "|" + oData.level_sixteen + "|" + oData.level_twenty);
            }
      @endif
    },
    @endforeach
  ];
  inputPaging = true;
  inputSearching = true;
  inputColReorder = true;
  inputFixedHeader = true;
  inputBInfo = true;
  inputSortOrder = [[ 4, "desc" ]];
  columnDefinition = [
    { "name": "rank",   "targets": 0 },
    { "name": "split_battletag",  "targets": 1 },
    { "name": "region", "targets": 2 },
    { "name": "win_rate",  "targets": 3 },
    { "name": "rating",    "targets": 4 },
    { "name": "mmr",    "targets": 5 },
    { "name": "tier",    "targets": 6 },
    { "name": "games_played",    "targets": 7 },
    { "name": "most_played_hero",    "targets": 8 },
    { "name": "most_played_build",    "targets": 9 },
    { "name": "hero_build_games_played",    "targets": 10 },
    { "visible": false, "targets": 9 }
  ]

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
    type: "POST",
    success: function(results){
      createTableJS(tableID, results, inputColumns, columnDefinition, inputPaging, inputSearching, inputColReorder, inputFixedHeader, inputBInfo, inputSortOrder);
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
      type: "POST",

      success: function(results){
        var table = $(tableID).DataTable();
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
        $('.roles-selectpicker').selectpicker('val', '');
        $("#role-picker").prop("disabled", true);




            // Get the column API object
            var most_played_hero_column = $(tableID).DataTable().column('most_played_hero:name');
            var most_played_build_column = $(tableID).DataTable().column('most_played_build:name');
            //column.visible( ! column.visible() );
            most_played_hero_column.visible(false);
            most_played_build_column.visible(true);




        break;
      case "role":
        $("#hero-picker").prop("disabled", true);
        $('.heroes-selectpicker').selectpicker('val', '');
        $("#role-picker").removeAttr("disabled");

        var most_played_hero_column = $(tableID).DataTable().column('most_played_hero:name');
        var most_played_build_column = $(tableID).DataTable().column('most_played_build:name');

        most_played_hero_column.visible(true);
        most_played_build_column.visible(false);

        break;
      default:
        $('.heroes-selectpicker').selectpicker('val', '');
        $("#hero-picker").prop("disabled", true);

        $('.roles-selectpicker').selectpicker('val', '');
        $("#role-picker").prop("disabled", true);

        var most_played_hero_column = $(tableID).DataTable().column('most_played_hero:name');
        var most_played_build_column = $(tableID).DataTable().column('most_played_build:name');

        most_played_hero_column.visible(true);
        most_played_build_column.visible(false);

        break;
    }
    $('.roles-selectpicker').selectpicker('refresh');
    $('.heroes-selectpicker').selectpicker('refresh');
  });
});


</script>
@endsection
