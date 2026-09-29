<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="background: transparent">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  {{-- Framed on other sites: no nav, ads, footer or analytics. Transparent, so
       any frame height left over shows the host page rather than ours. --}}
  <body class="text-white dark-mode {{ ($voidStage ?? 0) > 0 ? 'void-stage-'.$voidStage : '' }}" style="background: transparent">
    <div id="app">
      @yield('content')
    </div>
  </body>
</html>
