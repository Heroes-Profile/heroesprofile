<!doctype html>
<html lang="en">
    <head>
      @include('scripts.header')
    </head>
    <body >
      <div id="app">
        @include('nav.primary')
        <div class="container-fluid" >
            <h2>{{$title}}</h2>
            <p>{{$paragraph}}</p>
            @yield('content')
            @yield('datatable')
        </div>
      </div>
      @include('scripts.footer')
    
    </body>
</html>
