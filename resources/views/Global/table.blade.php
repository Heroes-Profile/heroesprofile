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
  <script src="{{ asset('js/extraHeader.js') }}"></script>

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

        @elseif ($column == "talent_builds")
        ,
        "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
          $(nTd).html("<a id='hero-talent-button" + oData.hero + "' class='btn btn-info btn-sm hero-talent-button' data-hero='" + oData.hero + "' href='javascript:void(0)'>View Talent Builds</a>");
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
        extraHeader(results);
        @endif
      }
    });


    $('#basic_search').on('hide.bs.select', function (e, clickedIndex, isSelected, previousValue) { // Change this so that it also triggers on on radio buttons, etc.
    //  var dialog = showPop();
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
          @if(isset($column_headers))
          extraHeader(results);
          @endif
        //  dialog.modal('hide');
          var table = $(tableID).DataTable();
          table
          .clear()
          .rows.add(results)
          .draw();
        }
      });

    });
    $('.dataTables_length').addClass('bs-select');

    $('#stats-table').on('click', '.hero-talent-button', function(){
      var selectedTalentRow = $(this).parent().parent('tr').next('tr');
      var hero = $(this).data('hero');
      console.log("Clicked " + hero);


    });


    @if ($filtertype == "leaderboard")
    $('#type-picker').change(function(){
      //showPop();

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
