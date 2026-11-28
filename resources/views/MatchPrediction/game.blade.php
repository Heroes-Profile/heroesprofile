@extends('layouts.app', $bladeGlobals)    

@section('title', __('Heroes of the Storm Match Prediction Game'))
@section('meta_keywords', 'heroes of the storm prediction game, hots match prediction, guess the winner, hots draft quiz, match prediction leaderboard')
@section('meta_description', 'Guess which team won a real Heroes of the Storm game from the draft alone. Build a streak and climb the Match Prediction leaderboard.')

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
