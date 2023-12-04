@extends('layouts.app', $regions)  
@section('title', 'Profile Settings')
@section('meta_keywords', 'profile settings, Patreon, site settings, user preferences')
@section('meta_description', 'Customize your profile settings, including Patreon authentication, site preferences, and user options.')
@section('content')
  <profile-settings 
    :user="{{ json_encode(Auth::user()) }}" 
    :filters="{{ json_encode($filters) }}"
  >
  </profile-settings>
@endsection
