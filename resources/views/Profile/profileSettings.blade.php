@extends('layouts.app', $bladeGlobals)    
@section('title', __('Profile Settings'))
@section('meta_keywords', 'profile settings, Patreon, site settings, user preferences')
@section('meta_description', 'Customize your profile settings, including Patreon authentication, site preferences, and user options.')
@section('content')
  <profile-settings 
    :user="{{ json_encode(Auth::user()) }}" 
    :filters="{{ json_encode($filters) }}"
    :locale="'{{ app()->getLocale() }}'"
    :locales="{{ json_encode(config('app.supported_locales')) }}"
    :available-flair="{{ json_encode($availableFlair) }}"
  >
  </profile-settings>
@endsection
