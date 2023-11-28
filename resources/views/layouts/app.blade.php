<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
  <head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2T71M0W00N"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-2T71M0W00N');
    </script>
    <script src="https://hb.vntsm.com/v3/live/ad-manager.min.js" type="text/javascript" data-site-id="60f587eddd63d722e7e57bc1" data-mode="scan" async></script>



    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,  user-scalable=yes">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile</title>
    <meta name = "keywords" content = "@yield('meta_keywords')" />
    <meta name = "description" content = "@yield('meta_description')" />
    <meta property="og:image" content="">


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="bg-black text-white">
  <div id="app" class="flex flex-col align-stretch" style="min-height:100vh;">
   <div class="bg-red text-sm text-center p-1">
      Site has not been styled for mobile yet.<br/>
      Patreon subscribers please log in and link your Patreon account as we migrate to new site flair and ad-free infrastructure.
      {{ session('headeralert') }}
    </div>


    @include('nav', [
    'isAuthenticated' => Auth::check(),
    'mainSearchAccount' => $main_search_account,
    'altSearchAccounts' => [$alt_search_account1, $alt_search_account2, $alt_search_account3],
    'regions' => session('regions'),
    ])
    
    <horizontal-banner-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></horizontal-banner-ad>

    <rich-media-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></rich-media-ad>

    <!-- Not sure if working -->
    <takeover-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></takeover-ad>
    @yield('content')

    <dynamic-banner-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" :index="100"></dynamic-banner-ad>

    <div class="mt-auto">
      <div class="footer-wrapper text-center mx-auto bg-lighten border-t-4 border-teal mt-[2em] w-full px-4">
        <div class="footer container-boxed py-10">
          <div class="container container-flex mx-auto">
            <div class="logo ">
              <a class="text-blue-600 hover:text-blue-800 flex justify-center items-center font-logo text-2xl py-5 mx-auto text-center" href="/">
                Heroes
                <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
              </a>
            </div>
            <div class="content ">
              <div class="footer-nav">
              </div>

              <div>{{ session('maxReplayID') }} replays | Patch {{ session('latestPatch') }} | Up to date as of: <format-date :input="'{{ session('latestGameDate') }}'"></format-date></div>
              <div class="copyright">Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a></div>
            </div>
          </div>
          <div class="disclaimer">
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>