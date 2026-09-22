@extends('layouts.app', $bladeGlobals)
@section('title', 'Streamers Using Heroes Profile on Twitch')
@section('meta_keywords', 'Heroes of the Storm, Twitch, streamers, extension, live')
@section('meta_description', 'Heroes of the Storm streamers using the Heroes Profile Twitch extension, live now.')
@section('content')
  <twitch-streamers
    :directory="{{ json_encode($directory) }}"
  >
  </twitch-streamers>
@endsection
