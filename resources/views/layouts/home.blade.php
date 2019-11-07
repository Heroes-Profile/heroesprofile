<!doctype html>
<html lang="en">
    <head>
      @include('scripts.header')
    </head>
    <body >
      <div id="app">

        <div class="container-fluid" >

          <h1>Heroes Profile</h1>
            <h2>{{$title}}</h2>
            <p>{{$paragraph}}</p>
            @yield('content')
            @yield('datatable')
        </div>
      </div>
      @include('scripts.footer')

    </body>
</html>
