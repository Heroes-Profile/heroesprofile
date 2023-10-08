@extends('layouts.app')
@section('title', 'Party Stats & Stacks')
@section('meta_keywords', 'Party Stats, Hero Stacks, Stack Size, Stack Performance')
@section('meta_description', 'Explore party stats, including stack size and performance. Analyze how different stack sizes fare against others.')
@section('content')
  <global-party-stats :filters="{{ json_encode($filters) }}" :gametypedefault="{{ json_encode($gametypedefault) }}" :defaulttimeframetype="{{ json_encode($defaulttimeframetype) }}" :defaulttimeframe="{{
