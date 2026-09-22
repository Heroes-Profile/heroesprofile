@extends('layouts.api')

@section('title', 'Account')

@section('content')
  <api-account
    :account="{{ json_encode($account) }}"
    :initialkeys="{{ json_encode($keys) }}"
    :usage="{{ json_encode($usage) }}"
    :standing="{{ json_encode($standing) }}"
    {{-- The Patreon link flow is a redirect, not an XHR, so its outcome can only
         reach the page this way. --}}
    :notice="{{ json_encode(session('status')) }}"
    :linkerror="{{ json_encode($errors->first('patreon')) }}"
    :twitch="{{ json_encode($twitch) }}"
    {{-- The Twitch and Battle.net connect flows return here too, with their own keys
         so their messages show in the Twitch section rather than the Patreon one. --}}
    :twitchnotice="{{ json_encode(session('twitch_status')) }}"
    :twitcherror="{{ json_encode($errors->first('twitch') ?: $errors->first('battlenet')) }}"
  >
  </api-account>
@endsection
