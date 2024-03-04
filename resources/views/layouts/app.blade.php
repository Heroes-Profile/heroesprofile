<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" >
  <head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XTN5LVP358"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-XTN5LVP358');
    </script>
    <script src="https://hb.vntsm.com/v3/live/ad-manager.min.js" type="text/javascript" data-site-id="60f587eddd63d722e7e57bc1" data-mode="scan" async onerror="handleAdBlocker()"></script>

    <script>
      function handleAdBlocker() {
        setCookie('ad-blocker', 'true', 1); // Set the cookie to expire after 1 day
      }

      function setCookie(name, value) {
        var expires = '';
        var minutes = 5; // Set the desired expiration time in minutes

        if (minutes) {
          var date = new Date();
          date.setTime(date.getTime() + (minutes * 60 * 1000)); // Convert minutes to milliseconds
          expires = '; expires=' + date.toUTCString();
        }

        document.cookie = name + '=' + value + expires + '; path=/; SameSite=None; Secure';
      }

    </script>


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,  user-scalable=yes">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile</title>
    <meta name = "keywords" content = "@yield('meta_keywords')" />
    <meta name = "description" content = "@yield('meta_description')" />
    <meta property="og:image" content="">


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>
<body class="bg-black text-white {{ $bladeGlobals ? $bladeGlobals['darkmode'] ? 'dark-mode' : 'light-mode' : 'light-mode' }}">
  <div id="app" class="flex flex-col align-stretch " style="min-height:100vh; ">
  <div class="max-md:h-[75px]"></div>
    <horizontal-banner-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" ></horizontal-banner-ad>


    @if($headeralert !== null)
      <div class="bg-red text-sm text-center p-1">
          {{ $headeralert }}
      </div>
    @endif



    @include('nav', [
    'isAuthenticated' => Auth::check(),
    'mainSearchAccount' => $main_search_account,
    'altSearchAccounts' => [$alt_search_account1, $alt_search_account2, $alt_search_account3],
    'regions' => $bladeGlobals["regions"],
    ])
    

    <rich-media-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></rich-media-ad>

    @yield('content')

    <dynamic-banner-ad  :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" :index="{{ json_encode(1) }}" :mobile-override="{{ json_encode(false) }}"></dynamic-banner-ad>

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
              
              @if(isset($maxReplayID))
                  <div>{{ $maxReplayID }} replays | Patch {{ $latestPatch }} | Up to date as of: <format-date :input="'{{ $latestGameDate }}'"></format-date></div>
              @endif

              
              <p><a href="/Privacy/Policy" class="underline text-xs">Privacy Policy</a></p>
              <div class="copyright">Skill Tree Development, LLC | <a href="https://heroesprofile.com">Heroes Profile</a></div>
            </div>
          </div>
          <div class="disclaimer">
            <footer-popup-disclaimer></footer-popup-disclaimer>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>