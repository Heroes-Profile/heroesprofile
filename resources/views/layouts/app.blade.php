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

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('images/logo/heroesprofilelogo.png') }}">
    <meta name="theme-color" content="#000000">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,  user-scalable=yes">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Heroes Profile</title>
    <meta name = "keywords" content = "@yield('meta_keywords')" />
    <meta name = "description" content = "@yield('meta_description')" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title') | Heroes Profile">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:image" content="@yield('og_image', asset('images/logo/heroesprofilelogo.png'))">
    <meta property="og:site_name" content="Heroes Profile">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title') | Heroes Profile">
    <meta name="twitter:description" content="@yield('meta_description')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logo/heroesprofilelogo.png'))">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://www.google-analytics.com">
    <link rel="preconnect" href="https://hb.vntsm.com">

    <!-- Organization structured data for Google search branding -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "Heroes Profile",
      "url": "https://www.heroesprofile.com",
      "logo": "https://www.heroesprofile.com/images/logo/heroesprofilelogo.png",
      "sameAs": [
        "https://x.com/HeroesProfile",
        "https://www.reddit.com/r/heroesprofile/",
        "https://www.patreon.com/c/heroesprofile",
        "https://opencollective.com/heroes-profile",
        "https://github.com/Heroes-Profile/heroesprofile"
      ]
    }
    </script>

    {{-- The nav flyouts' lists, once per page rather than inline in every flyout. --}}
    @php
      $navLists = [
        'heroes' => collect($bladeGlobals['heroes'] ?? [])->map(fn ($hero) => ['id' => $hero->id, 'name' => $hero->name])->values(),
        'maps' => collect($bladeGlobals['maps'] ?? [])->map(fn ($map) => ['id' => $map->map_id, 'name' => $map->name])->values(),
      ];
    @endphp
    <script>
      window.navLists = {!! json_encode($navLists, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.void-eye-img{ background-image: url('{{ asset('images/event/xalatath/void-eye.svg') }}'); }</style>

</head>
<body class="bg-[#14182a] bg-[radial-gradient(ellipse_1000px_900px_at_50%_150px,#1c2f5a,#14182a)] bg-no-repeat text-white {{ $bladeGlobals ? $bladeGlobals['darkmode'] ? 'dark-mode' : 'light-mode' : 'light-mode' }}">
  <div id="app" class="relative flex flex-col align-stretch " style="min-height:100vh; ">
  <div class="max-md:h-[75px]"></div>
    <horizontal-banner-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" ></horizontal-banner-ad>


    @include('nav', [
    'isAuthenticated' => Auth::check(),
    'mainSearchAccount' => $main_search_account,
    'altSearchAccounts' => [$alt_search_account1, $alt_search_account2, $alt_search_account3],
    'regions' => $bladeGlobals["regions"],
    ])

    @if(isset($headeralert) && $headeralert !== null && request()->cookie('header_alert_dismissed') !== md5($headeralert))
      <header-alert :alert="{{ json_encode($headeralert) }}" :dismiss-key="{{ json_encode(md5($headeralert)) }}"></header-alert>
    @endif
    

    <rich-media-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></rich-media-ad>

    @yield('content')

    <dynamic-banner-ad  :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" :index="{{ json_encode(1) }}" :mobile-override="{{ json_encode(false) }}"></dynamic-banner-ad>

    <footer class="mt-auto @yield('footer_gap', 'pt-[2em]')">
      @if(isset($isBackendOff) && $isBackendOff)
        <div class="bg-red text-sm text-center p-1">
          The backend is currently offline. Some features may not be available.  Please contact zemill@heroesprofile.com
        </div>
      @endif
      <div class="bg-[#1f2229] text-sm px-4 md:px-8 py-6 flex flex-wrap items-center justify-between gap-4 max-md:flex-col max-md:text-center">
        <div>
          @if(isset($maxReplayID) && ! (isset($isBackendOff) && $isBackendOff))
            Patch {{ $latestPatch }}<span class="mx-3">|</span>{{ is_numeric($maxReplayID) ? number_format($maxReplayID) : $maxReplayID }} replays
          @endif
        </div>
        <div class="space-x-3">
          <a href="/FAQ" class="underline">FAQ</a>
          <a href="/Privacy/Policy" class="underline">Privacy Policy</a>
          <a href="/Terms/Of/Service" class="underline">Terms of Service</a>
        </div>
        <div>Skill Tree Development, LLC<span class="mx-3">|</span><a href="https://heroesprofile.com">Heroes Profile</a></div>
      </div>
      <footer-popup-disclaimer></footer-popup-disclaimer>
    </footer>
  </div>

  @stack('scripts')
</body>
</html>