@extends('layouts.app', $bladeGlobals)
@section('title', __('The 2026 Redesign'))
@section('meta_keywords', 'Heroes Profile redesign, Heroes Profile 2026, Heroes of the Storm stats')
@section('meta_description', 'What changed in the 2026 Heroes Profile redesign: the new look, every page, phones and translations, with before and after examples.')
@section('content')
  <redesign-page :languages="{{ json_encode($languages) }}" :locale="'{{ app()->getLocale() }}'"></redesign-page>
@endsection
