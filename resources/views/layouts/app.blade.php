<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    @include('scripts.header')
    @include('scripts.footer')
  </head>
  <body>
      <div id="app">
        @include('nav.primary')
          <main class="container-fluid darkTheme">
              @yield('content')
          </main>
      </div>
  </body>
  @yield('scripts')
</html>
