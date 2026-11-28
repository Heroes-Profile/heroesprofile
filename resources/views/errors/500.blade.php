@extends('errors.layout')

@section('title', __('Server Error'))
@section('meta_keywords', '500 error, server error, website error, error page')
@section('meta_description', 'Something went wrong loading this page. Please try again.')

@section('error_badge', __('Error 500'))
@section('error_heading', __('Something broke in the Nexus'))
@section('error_text', __('Our server ran into a problem loading this page. Try again in a moment. If it keeps happening, report the page and filters you were using.'))

@section('error_actions')
    <custom-button :href="''" :text="'Try again'" :alt="'Try again'" :color="'teal'" class="font-semibold"></custom-button>
    <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" class="font-semibold"></custom-button>
    <a href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-semibold text-lteal link-more">{{ __('Report a bug') }}</a>
@endsection
