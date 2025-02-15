@extends('layouts.app', $bladeGlobals)    
@section('title', ' Prematch ' . $prematchid)
@section('meta_keywords', 'Prematch')
@section('meta_description', 'Prematch data for id' . $prematchid)
@section('content')
  <prematch 
    :prematchid="{{ $prematchid }}"
    :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"
    :user="{{ json_encode(Auth::user()) }}"
  >
  </prematch>
@endsection
