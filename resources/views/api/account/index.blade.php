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
  >
  </api-account>
@endsection
