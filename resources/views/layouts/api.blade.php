<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile API</title>
    <meta name="description" content="@yield('meta_description', 'The Heroes Profile API — Heroes of the Storm statistics, replay data, and player profiles.')">

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <meta name="theme-color" content="#000000">

    <link rel="canonical" href="{{ url()->current() }}">

    {{-- No ads or $bladeGlobals here — none of it applies to the API section. --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="bg-page bg-[radial-gradient(ellipse_137.5rem_193.75rem_at_50%_9.6875rem,rgba(33,61,122,0.6),rgba(33,61,122,0.57)_12.5%,rgba(33,61,122,0.48)_25%,rgba(33,61,122,0.37)_37.5%,rgba(33,61,122,0.25)_50%,rgba(33,61,122,0.14)_62.5%,rgba(33,61,122,0.07)_75%,rgba(33,61,122,0.03)_87.5%,transparent)] bg-no-repeat text-white font-sans {{ $darkmode ? 'dark-mode' : 'light-mode' }}">
    <div id="app" class="flex flex-col align-stretch" style="min-height:100vh;">

      @include('api.partials.nav')

      <main class="flex-grow w-full">
        @yield('content')
      </main>

      <footer class="mt-auto pt-[2em]">
        <div class="bg-surface text-center text-sm px-4 py-12 flex flex-col items-center gap-4">
          <a class="flex justify-center items-center gap-[0.875rem] whitespace-nowrap" href="/Api">
            <img class="w-10 shrink-0" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
            <span class="text-[1.375rem] font-bold">Heroes Profile</span>
            <span class="text-base font-bold tracking-[0.06em] text-teal bg-teal/[0.18] rounded-[0.375rem] px-2.5 py-1 leading-[1.1875rem]">API</span>
          </a>

          {{-- The main site keeps these behind a hover dropdown, which has no
               touch fallback. Flat links here work everywhere. --}}
          <p class="flex flex-wrap justify-center gap-x-6 gap-y-1">
            <a href="https://github.com/Heroes-Profile/heroesprofile/issues/new?assignees=&labels=&projects=&template=bug_report.md" target="_blank" rel="noopener" class="underline">Report a Bug</a>
            <a href="https://github.com/Heroes-Profile/heroesprofile/discussions/new?category=ideas" target="_blank" rel="noopener" class="underline">Request a Feature</a>
            <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" rel="noopener" class="underline">Discussions</a>
            <a href="/Github/Change/Log" class="underline">Change Log</a>
            <a href="/Contact" class="underline">Contact</a>
          </p>

          <p class="flex flex-wrap justify-center gap-x-6 gap-y-1">
            <a href="/Api/Privacy" class="underline">Privacy Policy</a>
            <a href="/Api/Terms" class="underline">Terms of Service</a>
            <a href="/" class="underline">Main Site</a>
          </p>

          <div class="text-xs text-subtext">
            Skill Tree Development, LLC<span class="mx-2">·</span><a href="https://heroesprofile.com">Heroes Profile</a>
          </div>
        </div>
      </footer>
    </div>

    @stack('scripts')
  </body>
</html>
