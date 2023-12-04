@extends('layouts.app', $regions)  

@section('title', 'Battlenet Authentication')
@section('meta_keywords', 'Battlenet authentication, profile login, profile settings, Battlenet account, user authentication, login settings, game profiles, Battlenet login')
@section('meta_description', 'Authenticate your Battlenet account for profile login and settings on our platform.')

@section('content')
  <battlenet-authenticate :filters="{{ json_encode($filters) }}"></battlenet-authenticate>
@endsection
