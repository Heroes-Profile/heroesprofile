@extends('errors.layout')

@section('title', __('Too Many Requests'))
@section('meta_keywords', '429 error, too many requests, rate limited, throttled request')
@section('meta_description', 'Too many requests were made in a short amount of time. Please wait and try again.')

@section('error_badge', __('Error 429'))
@section('error_heading', __('Slow down, hero'))
@section('error_text', __('You\'re sending requests too quickly. Wait a moment, then try again.'))

@section('error_actions')
    <custom-button :href="''" :text="'Try again'" :alt="'Try again'" :color="'teal'" class="font-semibold"></custom-button>
    <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" class="font-semibold"></custom-button>
    <a href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-semibold text-lteal link-more">{{ __('Keeps happening? Report it') }}</a>
@endsection
