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
  <script src="{{ asset('js/bootbox.min.js') }}"></script><!--http://bootboxjs.com/-->
  <script src="{{ asset('js/popup.js') }}"></script>

  <script>
  $(document).ready(function() {
    $('#profile-nav-link').removeClass('active');
    $('#profile-friendsAndFoes-nav-link').removeClass('active');
    $('#profile-heroes-nav-link').removeClass('active');
    $('#profile-maps-nav-link').removeClass('active');
    $('#profile-match-history-nav-link').addClass('active');
    $('#profile-matchups-nav-link').removeClass('active');
    $('#profile-mmr-nav-link').removeClass('active');
    $('#profile-roles-nav-link').removeClass('active');
    $('#profile-talents-nav-link').removeClass('active');

    var formData = $('#basic_search').serializeArray();
    var dialog = showPop();


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
        dialog.modal('hide');
        //console.log(results);
      }
    });


  });
  </script>


@endsection
