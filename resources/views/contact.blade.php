@extends('layouts.app', $bladeGlobals)    
@section('title', 'Contact')
@section('content')
  <contact-form :recaptcha-site-key="'{{ $recaptchaSiteKey }}'"></contact-form>
@endsection
