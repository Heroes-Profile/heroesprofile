<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  {{-- Framed on other sites: no nav, ads, footer or analytics. --}}
  <body class="bg-black text-white dark-mode">
    <div id="app">
      @yield('content')
    </div>
  </body>
</html>
