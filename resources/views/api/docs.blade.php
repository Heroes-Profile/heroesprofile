@extends('layouts.api')

@section('title', __('API Docs: Endpoints, Parameters & Responses'))
@section('meta_description', __('Every endpoint in the Heroes Profile API for Heroes of the Storm data: its parameters, response shape and quota key.'))

@section('content')
  <api-docs
    :spec="{{ json_encode($spec) }}"
    :authenticated="{{ json_encode($authenticated) }}"
    :account="{{ json_encode($account) }}"
    :variables="{{ json_encode($variables) }}"
  ></api-docs>
@endsection
