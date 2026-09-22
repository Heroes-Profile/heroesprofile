@extends('layouts.app', $bladeGlobals)
@section('title', 'Streamer Code of Conduct')
@section('meta_keywords', 'Heroes Profile, Twitch, streamer, code of conduct')
@section('meta_description', 'The rules streamers agree to when listing their channel on Heroes Profile.')
@section('content')
  <twitch-guidelines
    :version="{{ json_encode($termsVersion) }}"
  >
  </twitch-guidelines>
@endsection
