@extends('layouts.api')

@section('title', __('Heroes of the Storm API'))
@section('meta_keywords', 'heroes of the storm api, hots api, heroes profile api, hero statistics api, replay data api')
@section('meta_description', __('The Heroes Profile API: Heroes of the Storm hero, talent and map stats, player profiles, match history and replay data as JSON for your site, app or bot.'))

{{-- The API as a web API, with where its docs and terms live --}}
@php
  $webApi = [
      '@context' => 'https://schema.org',
      '@type' => 'WebAPI',
      'name' => 'Heroes Profile API',
      'description' => 'Heroes of the Storm hero, talent and map stats, player profiles, match history and replay data as JSON.',
      'url' => url('/Api'),
      'documentation' => url('/Api/Docs'),
      'termsOfService' => url('/Api/Terms'),
      'provider' => ['@type' => 'Organization', 'name' => 'Heroes Profile', 'url' => 'https://www.heroesprofile.com'],
  ];
@endphp
@push('structured_data')
  <script type="application/ld+json">{!! json_encode($webApi, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
  <api-home
    :authenticated="{{ json_encode($authenticated) }}"
    :plans="{{ json_encode($plans) }}"
    :pricing-data="{{ json_encode($pricingData) }}"
  >
  </api-home>
@endsection
