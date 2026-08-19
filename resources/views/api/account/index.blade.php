@extends('layouts.api')

@section('title', 'Account')

@section('content')
  <api-account
    :account="{{ json_encode($account) }}"
    :initialkeys="{{ json_encode($keys) }}"
  >
  </api-account>
@endsection
