@extends('layouts.app', $bladeGlobals)
@section('title', __('Heroes of the Storm Replay Uploader – Upload HotS Replays'))
@section('meta_keywords', 'heroes of the storm replay upload, hots replay uploader, upload replays, heroes profile uploader')
@section('meta_description', 'Upload your Heroes of the Storm replays to Heroes Profile from your browser, or install the free signed uploader for Windows, macOS and Linux and it uploads every game as you finish it, with optional Storm League rank reading.')
{{-- The desktop uploader as a free app, for search results --}}
@php
  $uploaderApp = [
      '@context' => 'https://schema.org',
      '@type' => 'SoftwareApplication',
      'name' => 'Heroes Profile Replay Uploader',
      'applicationCategory' => 'GameApplication',
      'operatingSystem' => 'Windows, macOS, Linux',
      'description' => 'Uploads your Heroes of the Storm replays to Heroes Profile automatically after every game, with optional Storm League rank reading.',
      'url' => url('/Upload'),
      'downloadUrl' => 'https://github.com/Heroes-Profile/HeroesProfile.Uploader/releases/latest',
      'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
      'publisher' => ['@type' => 'Organization', 'name' => 'Skill Tree Development LLC'],
  ];
@endphp
@push('structured_data')
  <script type="application/ld+json">{!! json_encode($uploaderApp, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
  <replay-uploader
    :upload-url="'{{ $uploadUrl }}'"
    :max-bytes="{{ $maxBytes }}"
  ></replay-uploader>
@endsection
