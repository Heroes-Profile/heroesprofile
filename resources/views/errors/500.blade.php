@php
    $bladeGlobals = [
        'regions' => [
            1 => 'NA',
            2 => 'EU',
            3 => 'KR',
            /* 4 => 'UNK', */
            5 => 'CN',
        ],
        'darkmode' => 0,
        'heroes' => [],
        'maps' => [],
    ];
@endphp

@extends('layouts.app', $bladeGlobals)

@section('content')
    <div class="error-page">
        <div class="text-center">
            <h1>{{ __('Server or Website error') }}</h1>
            <p>{{ __('Please submit a bug report with the specific page and filters you were accessing:') }} <a class="link" href="https://github.com/Heroes-Profile/heroesprofile/issues" target="_blank">https://github.com/Heroes-Profile/heroesprofile/issues</a></p>
        </div>

        <div class="flex justify-center items-center text-center  bg-gray-100">
          <a href="/"><img class="" src="/images/miscellaneous/500.png" alt="Burning"></a>
        </div>

    </div>
@endsection