<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title') | Heroes Profile</title>
  <meta name = "keywords" content = "@yield('meta_keywords')" />
  <meta name = "description" content = "@yield('meta_description')" />
  <meta property="og:image" content="">


  @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="bg-black text-white">
  <div id="app">

      @include('nav', [
          'isAuthenticated' => Auth::check(),
          'mainSearchAccount' => $main_search_account,
          'altSearchAccounts' => [$alt_search_account1, $alt_search_account2, $alt_search_account3],
          'regions' => session('regions'),
      ])
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

            </div>

            <div>{{ session('maxReplayID') }} replays | Patch {{ session('latestPatch') }} | Up to date as of: {{ session('latestGameDate') }}</div>
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