@extends('layouts.app', $bladeGlobals)    
@section('title', __('Contact Heroes Profile'))
@section('meta_keywords', 'contact heroes profile, heroes profile support, heroes profile bug report, heroes profile feature request')
@section('meta_description', 'Contact Heroes Profile, the Heroes of the Storm stats site: send a message, email, report a bug or request a feature on GitHub.')
@section('content')
  <contact-form :recaptcha-site-key="'{{ $recaptchaSiteKey }}'"></contact-form>
@endsection
