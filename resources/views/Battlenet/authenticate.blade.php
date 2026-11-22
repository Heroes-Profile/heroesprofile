@extends('layouts.app', $bladeGlobals)
@section('no_ads', '1')

@section('title', __('Battlenet Authentication'))
@section('meta_keywords', 'Battlenet authentication, profile login, profile settings, Battlenet account, user authentication, login settings, game profiles, Battlenet login')
@section('meta_description', 'Authenticate your Battlenet account for profile login and settings on our platform.')

@section('content')
  <battlenet-authenticate></battlenet-authenticate>
@endsection
