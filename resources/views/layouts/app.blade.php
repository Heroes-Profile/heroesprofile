<!doctype html>
<html lang="en">
    <head>
      @include('scripts.header')
    </head>
    <body class="darkMode">
      <div id="app">
        @include('nav.primary')

            <div class="intro">
              <h2>{{$title}}</h2>
              <div><p>{{$paragraph}}</p></div>
            </div>

          <div class="container-fluid">
            @yield('content')
            @yield('datatable')
          </div>
        </div>
      </div>
      @include('scripts.footer')

    </body>
</html>
