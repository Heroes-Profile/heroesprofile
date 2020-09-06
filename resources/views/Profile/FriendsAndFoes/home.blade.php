@extends('layouts.app')
@section('title', 'Profile')

@section('content')
  <div class="container center">
    <h1 class="display-1">Zemill</h1>
  </div>
  @include('nav.profile')


  @include('filters.profile')

@endsection

@section('scripts')
  <script>
  $(document).ready(function() {
    $('#profile-nav-link').removeClass('active');
    $('#friendsAndFoes-nav-link').addClass('active');

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
        //console.log(results);
      }
    });


  });
  </script>


@endsection
