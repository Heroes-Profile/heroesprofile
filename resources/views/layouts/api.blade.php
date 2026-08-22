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

  <body class="bg-black text-white font-sans">
    <div id="app" class="flex flex-col align-stretch" style="min-height:100vh;">

      @include('api.partials.nav')

      <main class="flex-grow w-full">
        @yield('content')
      </main>

      <div class="mt-auto">
        <div class="text-center mx-auto bg-lighten border-t-4 border-teal mt-[2em] w-full px-4">
          <div class="container-boxed py-8 mx-auto">
            <a class="flex justify-center items-center font-logo text-2xl py-3 mx-auto text-center" href="/">
              Heroes
              <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
              Profile
            </a>

            {{-- The main site keeps these behind a hover dropdown, which has no
                 touch fallback. Flat links here work everywhere. --}}
            <p class="text-xs space-x-3 mb-2">
              <a href="https://github.com/Heroes-Profile/heroesprofile/issues/new?assignees=&labels=&projects=&template=bug_report.md" target="_blank" rel="noopener" class="underline">Report a Bug</a>
              <a href="https://github.com/Heroes-Profile/heroesprofile/discussions/new?category=ideas" target="_blank" rel="noopener" class="underline">Request a Feature</a>
              <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" rel="noopener" class="underline">Discussions</a>
              <a href="/Github/Change/Log" class="underline">Change Log</a>
              <a href="/Contact" class="underline">Contact</a>
            </p>

            <p class="text-xs space-x-3">
              <a href="/Api/Privacy" class="underline">Privacy Policy</a>
              <a href="/Api/Terms" class="underline">Terms of Service</a>
              <a href="/" class="underline">Main Site</a>
            </p>

            <div class="text-xs mt-2 text-gray-medium">
              Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    @stack('scripts')
  </body>
</html>
