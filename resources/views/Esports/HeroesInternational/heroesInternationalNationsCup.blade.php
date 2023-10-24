@extends('layouts.app')

@section('title', 'Heroes International Nations Cup Esports')
@section('meta_keywords', 'Heroes International Nations Cup league, Heroes International Nations Cup, Heroes of the Storm, Heroes International Nations Cup esports, competitive gaming, Heroes of the Storm league')
@section('meta_description', 'Stay up to date with the latest news and updates from the Heroes International Nations Cup league. Review competitive Heroes of the Storm matches and follow your favorite teams.')

@section('content')
  <heroes-international-nations-cup
    :heroes="{{ json_encode(session('heroes')) }}" 
    :defaultseason="{{ json_encode($defaultseason) }}" 
    :filters="{{ json_encode($filters) }}"
    :talentimages="{{ json_encode($talentimages) }}" 
  ></heroes-international-nations-cup>
@endsection
