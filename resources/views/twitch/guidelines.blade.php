@extends('layouts.app', $bladeGlobals)
@section('title', __('Streamer Code of Conduct'))
@section('meta_keywords', 'Heroes Profile, Twitch, streamer, code of conduct')
@section('meta_description', 'The rules Heroes of the Storm streamers agree to when listing their Twitch channel on Heroes Profile.')
@section('content')
  <twitch-guidelines
    :version="{{ json_encode($termsVersion) }}"
  >
  </twitch-guidelines>
@endsection
