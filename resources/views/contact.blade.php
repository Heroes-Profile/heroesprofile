@extends('layouts.app', $bladeGlobals)    
@section('title', __('Contact'))
@section('content')
  <contact-form :recaptcha-site-key="'{{ $recaptchaSiteKey }}'"></contact-form>
@endsection
