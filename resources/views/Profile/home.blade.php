@extends('layouts.app')
@section('title', 'Profile')

@section('content')
  @include('filters.profile')

Hello - This is the Profile Page
@endsection

@section('scripts')
  <script>
  $(document).ready(function() {
    var formData = $('#basic_search').serializeArray();


    parameters =
    {
      'page' : @json($page),
      'data' : formData
    }

    $.ajax({
      url: @json($inputUrl),
      data: parameters,
      //type: "POST",
      success: function(results){
        
      }
    });


  });
  </script>


@endsection
