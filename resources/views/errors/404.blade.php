@extends('errors.layout')

@section('title', __('Error'))
@section('meta_keywords', '404 error, page not found, not found error, website error, error page, site not found')
@section('meta_description', 'The page you were looking for could not be found.')

@section('error_badge', __('Error 404'))
@section('error_heading', __('You got lost in the Nexus'))
@section('error_text', __('This page doesn\'t exist or was moved. Take the portal home, or find what you were looking for below.'))

@section('error_actions')
    <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" :color="'teal'" class="font-semibold"></custom-button>
    <search-component :type="'alt'" class="!mt-0 !py-0 flex-1 min-w-[16rem]"></search-component>
@endsection
