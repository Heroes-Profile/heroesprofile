@extends('layouts.api')

@section('title', __('Endpoint Limits by Tier'))

@section('meta_description', __('Weekly call allowances for every Heroes Profile API endpoint, by subscription tier.'))

@section('content')
  <api-endpoint-limits
    :plans="{{ json_encode($plans) }}"
    :groups="{{ json_encode($groups) }}"
  >
  </api-endpoint-limits>
@endsection
