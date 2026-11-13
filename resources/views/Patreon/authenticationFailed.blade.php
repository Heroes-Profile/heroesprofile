@extends('layouts.app', $bladeGlobals)    

@section('title', __('Patreon Authentication Failed'))
@section('meta_keywords', 'Patreon authentication Failed')
@section('meta_description', 'Patreon authentication Failed.')

@section('content')
  <patreon-authenticate-failed></patreon-authenticate-failed>
@endsection
