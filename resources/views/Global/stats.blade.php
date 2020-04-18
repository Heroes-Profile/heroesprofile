@extends('layouts.app')
@section('title', 'Global Hero Stats')


@section('table')

<body>
    <div class="container">
        <table id="table" data-height="460">
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
  }).done(function( msg ) {
    console.log(msg);
    mydata = msg;
    $('#table').bootstrapTable({
        data: mydata
    });
  });


});

</script>
@endsection
