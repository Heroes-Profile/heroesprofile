@extends('layouts.api')

@section('title', 'Terms of Service')

@section('meta_description', 'Terms of Service for the Heroes Profile API and developer portal.')

@section('content')
  <api-terms-of-service
    :csrf="{{ json_encode(csrf_token()) }}"
    :reviewrequired="{{ json_encode($reviewRequired) }}"
  >
  </api-terms-of-service>
@endsection
