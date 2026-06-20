@extends('layouts.app', $bladeGlobals)
@section('title', 'FAQ')
@section('meta_keywords', 'Heroes Profile FAQ, frequently asked questions, Heroes of the Storm stats')
@section('meta_description', 'Frequently asked questions about Heroes Profile — uploading replays, MMR, global stats, site features, and more.')
@section('content')
  <faq :recaptcha-site-key="'{{ $recaptchaSiteKey }}'"></faq>
@endsection
