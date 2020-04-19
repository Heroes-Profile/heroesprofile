@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('table')


    <div class="container">
        <table id="table" class="table table-sm" data-height="460">
        <thead>
            <tr>
                <th data-field="hero">Hero</th>
                <th data-field="wins">Wins</th>
                <th data-field="losses">Losses</th>
                <th data-field="games_banned">Games Banned</th>
                <th data-field="games_played">Games Played</th>
                <th data-field="win_rate">Win Rate</th>
                <th data-field="pick_rate">Pick Rate</th>
                <th data-field="change">Change</th>
                <th data-field="popularity">Popularity</th>
                <th data-field="influence">Influence</th>
            </tr>
        </thead>
    </table>
    <div id="echodata">
    </div>
    </div>


<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>

<script>


var mydata = "";
$(function () {
  var $table = $('#table');

   $.ajax({
      type: "POST",
      url: '/getGlobalHeroStatsData'
  }).done(function( response ) {

    mydata = response;
    $('#echodata').html(response);
    console.log(response);
    /*$('#table').bootstrapTable({
        data: mydata
    });*/
    $.each(response, function(i, item) {
      var $tr = $('<tr>');
      $.each(item, function(j, itemdata){
        $tr.append($('<td>').text(itemdata));
      })
      $tr.appendTo('#table');


    });
    $('#table').dataTables();
  });


});

</script>
@endsection
