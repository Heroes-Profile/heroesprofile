@extends('layouts.app', $bladeGlobals)    
@section('title', '')
@section('meta_keywords', '')
@section('meta_description', '')
@section('content')
  <js-exception
    :user="{{ json_encode(Auth::user()) }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
  >
</js-exception>
@endsection
