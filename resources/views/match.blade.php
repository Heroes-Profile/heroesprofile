@extends('layouts.app')
@section('title', 'Match Single Page')

@section('content')
  @include('nav.profile')
  <div class="container">
    <p id='data'>Data</p>
  </div>
@endsection

@section('scripts')
  <script src="{{ asset('js/bootbox.min.js') }}"></script><!--http://bootboxjs.com/-->
  <script src="{{ asset('js/popup.js') }}"></script>
  <script>
  $(document).ready(function() {
    var dialog = showPop();
    parameters =
    {
      'replayID' : 31579385,
    }

    $.ajax({
      url: '/getSingeMatchData',
      data: parameters,
      //type: "POST",
      success: function(results){
        $('#data').text(JSON.stringify(results))
        dialog.modal('hide');
        console.log(results);
      }
    });


  });
  </script>


@endsection
