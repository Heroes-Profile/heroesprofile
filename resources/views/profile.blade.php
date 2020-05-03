@extends('layouts.app')
@section('title', 'Profile')

@section('content')

@endsection

@section('scripts')
<script>
inputUrl = '/getProfileData';

parameters =
{
  'page' : 'profile',
  'blizz_id' : '67280',
  'region' : '1',
}


$.ajax({
  url: inputUrl,
  data: parameters,
  type: "POST",
  success: function(results){
    console.log(results);
  }
});


</script>

@endsection
