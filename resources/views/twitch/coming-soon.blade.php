@extends('layouts.app', $bladeGlobals)
@section('title', 'Heroes Profile on Twitch')
@section('meta_keywords', 'Heroes of the Storm, Twitch, extension')
@section('meta_description', 'The Heroes Profile Twitch extension is in review and not open to streamers yet.')
@section('content')
  <div class="mx-auto max-w-[700px] px-4 py-16 text-center">
    <h1 class="text-2xl mb-4">Heroes Profile Live is on its way</h1>
    <p class="mb-4">
      Our Twitch extension shows your viewers the lobby, heroes and talent picks of the game
      you are playing. It is with Twitch for review, so it cannot be added to a channel yet.
    </p>
    <p>
      We will announce it here and on <a href="/Api/Account" class="link">the API portal</a> as soon
      as it is approved.
    </p>
  </div>
@endsection
