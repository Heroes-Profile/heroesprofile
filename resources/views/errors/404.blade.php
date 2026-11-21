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

    $popularPages = [
        ['title' => 'Hero Tier List', 'subtitle' => 'Win rates for every hero', 'href' => '/Global/Hero'],
        ['title' => 'Hero Builds', 'subtitle' => 'Popular talent builds', 'href' => '/Global/Talents'],
        ['title' => 'Leaderboards', 'subtitle' => 'Top players by season', 'href' => '/Global/Leaderboard'],
        ['title' => 'Drafter', 'subtitle' => 'Draft helper', 'href' => '/Drafter'],
    ];
@endphp

@extends('layouts.app', $bladeGlobals)

@section('title', __('Error'))
@section('meta_keywords', '404 error, page not found, not found error, website error, error page, site not found')
@section('meta_description', 'The page you were looking for could not be found.')

@section('content')
    <div class="max-w-[60rem] w-full mx-auto px-4 md:px-8 py-10 md:py-20">
        <div class="bg-surface rounded overflow-hidden grid md:grid-cols-[2fr_3fr]">
            <div class="relative max-md:h-[18rem]">
                <a href="/" aria-label="{{ __('Take me home') }}">
                    <img class="w-full h-full object-cover" src="/images/miscellaneous/404.png" alt="Town Portal">
                </a>
                <round-image class="!absolute bottom-3 right-3" size="small" icon="fas fa-info" title="info" popupsize="large">
                    <p class="font-normal">{{ __('Image from the Heroes of the Storm comic Secrets of the Storm.') }}</p>
                    <a href="https://static.heroesofthestorm.com/comics/secrets-of-the-storm/en-us-09ab7fb85c.pdf" target="_blank" rel="noopener noreferrer" class="group/bt inline-flex items-center mt-2 font-normal"><span class="group-hover/bt:underline">{{ __('View the comic') }}</span><basil-icon name="arrow-small" class="text-xs ml-2"></basil-icon></a>
                </round-image>
            </div>

            <div class="p-6 md:p-10 flex flex-col gap-5 min-w-0">
                <span class="self-start px-1.5 py-0.5 rounded-[0.25rem] bg-teal/20 text-lteal text-[0.625rem] font-bold uppercase tracking-wider">{{ __('Error 404') }}</span>
                <div class="flex flex-col gap-2">
                    <h1 class="text-3xl md:text-4xl font-bold">{{ __('You got lost in the Nexus') }}</h1>
                    <p class="text-subtext">{{ __('This page doesn\'t exist or was moved. Take the portal home, or find what you were looking for below.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <custom-button :href="'/'" :text="'Take me home'" :alt="'Take me home'" :color="'teal'" class="font-semibold"></custom-button>
                    <search-component :type="'alt'" class="!mt-0 !py-0 flex-1 min-w-[16rem]"></search-component>
                </div>

                <div class="border-t border-surface-3 pt-5 flex flex-col gap-3">
                    <span class="text-[0.625rem] font-bold uppercase tracking-wider text-subtext">{{ __('Popular pages') }}</span>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach ($popularPages as $page)
                            <a href="{{ $page['href'] }}" class="flex items-center gap-4 rounded-control bg-surface-1 hover:bg-surface-3 transition-colors p-3">
                                <span class="flex flex-col min-w-0">
                                    <span class="text-sm font-semibold">{{ __($page['title']) }}</span>
                                    <span class="text-xs text-subtext truncate">{{ __($page['subtitle']) }}</span>
                                </span>
                                <basil-icon name="arrow-small" class="ml-auto text-sm"></basil-icon>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
