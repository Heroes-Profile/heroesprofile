@extends('layouts.app', $bladeGlobals)
@section('title', __('The 2026 Redesign'))
@section('meta_keywords', 'Heroes Profile redesign, Heroes Profile 2026, Heroes of the Storm stats')
@section('meta_description', 'What changed in the 2026 Heroes Profile redesign: the new look, every page, phones and translations, with before and after examples.')
@section('content')
  @php($redesigner = collect(config('team.contributors'))->firstWhere('name', 'Bluray'))
  <redesign-page :locale="'{{ app()->getLocale() }}'" :discord="{{ json_encode($redesigner['discord'] ?? null) }}"></redesign-page>
@endsection
