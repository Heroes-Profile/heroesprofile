@extends('layouts.app', $bladeGlobals)    

@section('title', '')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <match-prediction-game 
    :filters="{{ json_encode($filters) }}" 
    :gametypes="{{ json_encode($gametypes) }}" 
    :user="{{ json_encode(Auth::user()) }}" 

  >
  </match-prediction-game>
@endsection
