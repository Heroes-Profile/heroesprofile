@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div class="container center">
    <h1 class="display-1">Zemill</h1>
</div>

@include('nav.profile')

@include('filters.profile')

  <div class="container">
    <div class="card">
      <label id='wins'>Wins </label>
      <label id='losses'>Losses </label>
    </div>
  </div>


@endsection

@section('scripts')
  <script>
  $(document).ready(function() {

    var formData = $('#basic_search').serializeArray();


    parameters =
    {
      'page' : @json($page),
      'data' : formData,
      'blizz_id' : 67280,
      'region' : 1,
      'game_type' : "",
      'season' : "",
    }

    $.ajax({
      url: @json($inputUrl),
      data: parameters,
      //type: "POST",
      success: function(results){
        $("#wins").append(results[0].wins);
        $("#losses").append(results[0].losses);
        console.log(results);
      }
    });


  });
  </script>


@endsection
