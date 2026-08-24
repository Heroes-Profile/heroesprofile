@extends('layouts.api')

@section('title', 'Endpoint Limits')

@section('meta_description', 'Weekly call allowances for every Heroes Profile API endpoint, by subscription tier.')

@section('content')
  <api-endpoint-limits
    :plans="{{ json_encode($plans) }}"
    :groups="{{ json_encode($groups) }}"
  >
  </api-endpoint-limits>
@endsection
