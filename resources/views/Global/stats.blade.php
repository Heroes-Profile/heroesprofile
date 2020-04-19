@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('content')
    <div class="container">
        <table id="table" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
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

@endsection

@section('scripts')


<script>
$(document).ready(function() {
  $('#table').DataTable( {
          paging: false,
          "searching": false,
          colReorder: true,
          fixedHeader: true,
          "bInfo": false,
          ajax: {
             url: '/getGlobalHeroStatsData',
             method: "POST"
          },
          columns: [
              { data: "hero" },
              { data: "wins" },
              { data: "losses" },
              { data: "games_banned" },
              { data: "games_played" },
              { data: "win_rate" },
              { data: "pick_rate" },
              { data: "change" },
              { data: "popularity" },
              { data: "influence" }
          ]
      } );
    $('.dataTables_length').addClass('bs-select');

});
/*
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
    $.each(response, function(i, item) {
      var $tr = $('<tr>');
      $.each(item, function(j, itemdata){
        $tr.append($('<td>').text(itemdata));
      })
      $tr.appendTo('#table');
    });

  });


});
*/
</script>
@endsection
