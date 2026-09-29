@extends('layouts.app', $bladeGlobals)    

@section('title', 'Match Prediction')
@section('meta_keywords', '')
@section('meta_description', '')

@section('content')
  <match-prediction-game 
    :filters="{{ json_encode($filters) }}" 
    :gametypes="{{ json_encode($gametypes) }}" 
    :season="{{ json_encode($season) }}" 
    :user="{{ json_encode(Auth::user()) }}" 
    :predictionstatspractice=" {{ json_encode($predictionstatspractice) }} "
    :predictionstatsall=" {{ json_encode($predictionstatsall) }} "
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"

  >
  </match-prediction-game>
@endsection
