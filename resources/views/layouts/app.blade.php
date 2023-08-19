<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title') | Heroes Profile</title>
  <meta name = "keywords" content = "@yield('meta_keywords')" />
  <meta name = "description" content = "@yield('meta_description')" />


  <meta property="og:image" content="">
  <!-- Scripts -->
  <script src="{{ asset('js/app.js') }}" defer></script>

  <!-- Fonts -->

  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="#">Navbar</a>
      <a class="navbar-brand" href="#">Navbar</a>
      <a class="navbar-brand" href="#">Navbar</a>
    </nav>

  <main class="py-4">
    @yield('content')
  </main>

<div class="footer-wrapper">
    <div class="footer container-boxed">
      <div class="container container-flex">

      <div class="logo">
        <img alt="Heroes Profile Logo" src=""/>
      </div>

      <div class="content ">
        <div class="footer-nav">
          <b-nav>
            <b-nav-item href="Privacy-Policy">Privacy Policy</b-nav-item>
            <b-nav-item href="Terms-of-Service">Terms of Service</b-nav-item>
            <b-nav-item href="mailto:zemill@heroesprofile.com">Contact Us</b-nav-item>
        </div>
        <div class="copyright">Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a></div>
        </div>

      </div>

      <div class="disclaimer">
      </div>
    </div>
  </div>
</div>
</body>
</html>
