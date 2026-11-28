@extends('layouts.app', $bladeGlobals)    
@section('title', __('Privacy Policy'))
@section('meta_keywords', 'Privacy Policy')
@section('meta_description', 'Privacy Policy')
@section('content')
  <privacy-policy
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
  </privacy-policy>
@endsection
