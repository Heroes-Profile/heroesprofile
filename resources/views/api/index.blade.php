@extends('layouts.api')

@section('title', 'API')
@section('meta_keywords', 'heroes of the storm api, hots api, heroes profile api, hero statistics api, replay data api')
@section('meta_description', 'Heroes of the Storm statistics, replay data, talent builds and player profiles from the Heroes Profile API.')

@section('content')
  <api-home
    :authenticated="{{ json_encode($authenticated) }}"
    :plans="{{ json_encode($plans) }}"
    :pricing-data="{{ json_encode($pricingData) }}"
  >
  </api-home>
@endsection
