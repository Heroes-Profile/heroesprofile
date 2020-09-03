@extends('layouts.app')
@section('title', $title)


@section('content')

  @if ($filtertype == "leaderboard")
    @include('filters.leaderboard')
  @else
    @include('filters.globals')
  @endif


<div class="container-fluid">
  <table id="{{ $tableid }}" class="table table-striped table-bordered table-sm " cellspacing="0" width="100%">
    <thead class="thead-dark">
      @if(isset($column_headers))
        <tr>
          @foreach ($column_headers as $column)
            <th data-field="{{$column['key'] }}" id="{{$column['key'] }}">{{ $column['text'] }}</th>
          @endforeach
        </tr>
      @endif
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
  <script src="{{ asset('js/popup.js') }}"></script>
  <script src="{{ asset('js/bootbox.min.js') }}"></script><!--http://bootboxjs.com/-->

<script>
var tableID = "#"+@json($tableid);
$(document).ready(function() {
  $("#hero-picker").prop("disabled", true);
  $("#role-picker").prop("disabled", true);
  $('.roles-selectpicker').selectpicker('refresh');
  $('.heroes-selectpicker').selectpicker('refresh');

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

        @elseif ($column == "win_rate_confidence")
          ,
          "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
               $(nTd).html("&#177; " + oData.win_rate_confidence);
           }
      @endif
    },
    @endforeach
  ];

  inputSortOrder = [
    @foreach($inputSortOrder as $column_pos => $order)
      [@json($column_pos), @json($order)],
    @endforeach
  ];
  columnDefinition = [
    <?php $counter = 0; ?>
    @foreach ($columndata as $column)
      { "name" : @json($column), "targets": {{ $counter }}},

      @if ($column == "most_played_build")
        { "visible": false, "targets": {{ $counter }} },
      @endif
    <?php $counter++;?>
    @endforeach
  ]

  //Filters/Parameters
  var formData = $('#basic_search').serializeArray();

  parameters =
  {
    'page' : @json($page),
    'data' : formData
  }
  $.ajax({
    url: @json($inputUrl),
    data: parameters,
    //type: "POST", //Turn Back on after testing
    success: function(results){
      createTableJS(
        tableID,
        results,
        inputColumns,
        columnDefinition,
        @json($inputPaging),
        @json($inputSearching),
        @json($inputColReorder),
        @json($inputFixedHeader),
        @json($inputBInfo),
        inputSortOrder
      );

      @if(isset($column_headers))

        {{--Average Win Rate Header --}}
        var count = 0;
        var average_win_rate = results.reduce(function (s, a) {
            count++;
            return s + parseFloat(a.win_rate);
        }, 0);
        $('#avg_win_rate').text(function() {
            return (average_win_rate / count).toFixed(2);
        });

        {{--Average Win Rate Confidence Header --}}
        var average_win_rate_confidence = results.reduce(function (s, a) {
            return s + parseFloat(a.win_rate_confidence);
        }, 0);
        $('#avg_win_rate_confidence').text(function() {
            return (average_win_rate_confidence / count).toFixed(2);
        });

        {{--Average Win Rate Change Header --}}
        var count_positive = 0;
        var count_negative = 0;
        var average_change_positive = results.reduce(function (s, a) {
          if(parseFloat(a.change) >= 0){
            count_positive++;
            return s + parseFloat(a.change);
          }else{
            return s;
          }
        }, 0);
        var average_change_negative = results.reduce(function (s, a) {
          if(parseFloat(a.change) < 0){
            count_negative++;
            return s + parseFloat(a.change);
          }else{
            return s;
          }
        }, 0);
        $('#avg_change').text(function() {
            return (average_change_positive / count_positive).toFixed(2) + " | " + (average_change_negative / count_negative).toFixed(2);
        });

        {{--Average Popularity Header --}}
        var average_popularity = results.reduce(function (s, a) {
            return s + parseFloat(a.popularity);
        }, 0);
        $('#avg_popularity').text(function() {
            return (average_popularity / count).toFixed(2);
        });


        {{--Average Pick Rate Header --}}
        var average_pick_rate = results.reduce(function (s, a) {
            return s + parseFloat(a.pick_rate);
        }, 0);
        $('#avg_pick_rate').text(function() {
            return (average_pick_rate / count).toFixed(2);
        });

        {{--Average Ban Rate Header --}}
        var average_ban_rate = results.reduce(function (s, a) {
            return s + parseFloat(a.ban_rate);
        }, 0);
        $('#avg_ban_rate').text(function() {
            return (average_ban_rate / count).toFixed(2);
        });

        {{--Average Influence Header --}}
        var count_positive = 0;
        var count_negative = 0;
        var average_influence_positive = results.reduce(function (s, a) {
          if(parseFloat(a.influence) >= 0){
            count_positive++;
            return s + parseFloat(a.influence);
          }else{
            return s;
          }
        }, 0);
        var average_influence_negative = results.reduce(function (s, a) {
          if(parseFloat(a.influence) < 0){
            count_negative++;
            return s + parseFloat(a.influence);
          }else{
            return s;
          }
        }, 0);
        $('#avg_influence').text(function() {
            return (average_influence_positive / count_positive).toFixed(0) + " | " + (average_influence_negative / count_negative).toFixed(0);
        });

        {{--Average Games Played Header --}}
        var average_games_played = results.reduce(function (s, a) {
            return s + parseFloat(a.games_played);
        }, 0);
        $('#avg_games_played').text(function() {
            return (average_games_played / count).toFixed(0);
        });

        {{--Average Wins Header --}}
        var average_wins = results.reduce(function (s, a) {
            return s + parseFloat(a.wins);
        }, 0);
        $('#avg_wins').text(function() {
            return (average_wins / count).toFixed(0);
        });

        {{--Average Losses Header --}}
        var average_losses = results.reduce(function (s, a) {
            return s + parseFloat(a.losses);
        }, 0);
        $('#avg_losses').text(function() {
            return (average_losses / count).toFixed(0);
        });

        {{--Average Games Banned Header --}}
        var average_games_banned = results.reduce(function (s, a) {
            return s + parseFloat(a.games_banned);
        }, 0);
        $('#avg_games_banned').text(function() {
            return (average_games_banned / count).toFixed(0);
        });
      @endif
    }
  });


  $('#basic_search').on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) { // Change this so that it also triggers on on radio buttons, etc.
    var dialog = showPop();
    var formData = $('#basic_search').serializeArray();

    parameters =
    {
      'page' : @json($page),
      'data' : formData
    }

    $.ajax({
      url: @json($inputUrl),
      data: parameters,
      //type: "POST",  //turn back on after testing

      success: function(results){
        dialog.modal('hide');
        var table = $(tableID).DataTable();
        table
            .clear()
            .rows.add(results)
            .draw();
      }
    });

  });
  $('.dataTables_length').addClass('bs-select');

@if ($filtertype == "leaderboard")
  $('#type-picker').change(function(){
    showPop();

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
  @endif
});


</script>
@endsection
