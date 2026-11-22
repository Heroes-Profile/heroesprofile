@extends('layouts.app', $bladeGlobals)
@section('no_ads', '1')

@section('title', __('Battlenet Authentication Failed'))
@section('meta_keywords', 'Battlenet authentication Failed')
@section('meta_description', 'Battlenet authentication Failed.')

@section('content')
  <battlenet-authenticate-failed></battlenet-authenticate-failed>
@endsection
