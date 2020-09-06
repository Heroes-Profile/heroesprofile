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
        <p id='data'>Data</p>
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
        $('#data').text(JSON.stringify(results))
        //console.log(results);
      }
    });


  });
  </script>


@endsection
