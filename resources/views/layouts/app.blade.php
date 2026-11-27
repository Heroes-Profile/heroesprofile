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
    {{-- Read by resources/js/utils/adBlocker.js --}}
    <script>
      function setAdManagerState(state) {
        window.adManagerState = state;
        window.dispatchEvent(new Event('ad-manager-state'));
      }
    </script>

    {{-- Pages with @section('no_ads') (the error pages) load no ads at all --}}
    @sectionMissing('no_ads')
    <script src="https://hb.vntsm.com/v3/live/ad-manager.min.js" type="text/javascript" data-site-id="60f587eddd63d722e7e57bc1" data-mode="scan" async onload="setAdManagerState('loaded')" onerror="setAdManagerState('blocked')"></script>
    @endif

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
    <meta name="global-debug-banner" content="{{ config('global.debug_banner') ? 1 : 0 }}">

    <title>@yield('title') | Heroes Profile</title>
    <meta name = "keywords" content = "@yield('meta_keywords')" />
    <meta name = "description" content = "@yield('meta_description')" />
    {{-- Pages that shouldn't be in search results (signed-in pages) set this --}}
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('canonical', url()->current())">
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
    {{-- The path without its query, unless the page names its own (one that is a query, like a Heroes International tournament) --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Korean and Chinese pages: their own Noto Sans, which the font stack in app.css puts after Roboto.
         Google splits it into small pieces by character, so a page only downloads the characters it uses. -->
    @php
      $languageFont = ['ko' => 'Noto+Sans+KR', 'zh-CN' => 'Noto+Sans+SC', 'zh-TW' => 'Noto+Sans+TC'][str_replace('_', '-', app()->getLocale())] ?? null;
    @endphp
    @if($languageFont)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ $languageFont }}:wght@400;500;700&display=swap">
    @endif

    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://www.google-analytics.com">
    <link rel="preconnect" href="https://hb.vntsm.com">

    <!-- Organization structured data for Google search branding -->{{-- Its "context" key is written with @@: Blade reads a single @ before it as its own context directive --}}
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
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

    {{-- What this page is, from its own title and description, so search engines get the page's summary without it
         being on screen. Section content comes back HTML-escaped, so it's decoded before going into JSON. --}}
    @php
      $seoText = fn ($section) => trim(html_entity_decode($__env->yieldContent($section), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
      $webPage = array_filter([
          '@context' => 'https://schema.org',
          '@type' => 'WebPage',
          'name' => $seoText('title'),
          'description' => $seoText('meta_description'),
          'url' => trim($__env->yieldContent('canonical', url()->current())),
          'inLanguage' => str_replace('_', '-', app()->getLocale()),
          'isPartOf' => ['@type' => 'WebSite', 'name' => 'Heroes Profile', 'url' => 'https://www.heroesprofile.com'],
      ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($webPage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    {{-- A page's own structured data, e.g. a player's profile and breadcrumbs --}}
    @stack('structured_data')

    {{-- The nav flyouts' lists, once per page rather than inline in every flyout. --}}
    @php
      $navLists = [
        'heroes' => collect($bladeGlobals['heroes'] ?? [])->map(fn ($hero) => ['id' => $hero->id, 'name' => $hero->name, 'short_name' => $hero->short_name])->values(),
        'maps' => collect($bladeGlobals['maps'] ?? [])->map(fn ($map) => ['id' => $map->map_id, 'name' => $map->name])->values(),
      ];
    @endphp
    {{-- Who has the void eye, and whether the owner crown is hidden. Read by voidEye.js. --}}
    @php
      $globalDataService = app(\App\Services\GlobalDataService::class);
      $flairState = [
        'void_eye' => $globalDataService->getVoidEyeHolders(),
        'owner_hidden' => $globalDataService->isFlairHidden('owner', 67280, 1),
      ];
    @endphp
    <script>
      window.navLists = {!! json_encode($navLists, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
      window.flairState = {!! json_encode($flairState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
      @if (app()->getLocale() !== 'en')
      {{-- English hero/map name => name in the chosen language, read by $heroName / $mapName --}}
      window.gameStrings = {!! json_encode(app(\App\Services\LocalizedGameStrings::class)->names(app()->getLocale()), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_FORCE_OBJECT) !!};
      {{-- lang/{locale}.json, read by $t --}}
      window.translations = {!! json_encode(app('translator')->getLoader()->load(app()->getLocale(), '*', '*'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_FORCE_OBJECT) !!};
      @endif
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.void-eye-img{ background-image: url('{{ asset('images/event/xalatath/void-eye.svg') }}'); }</style>

</head>
<body class="bg-page bg-[radial-gradient(ellipse_137.5rem_193.75rem_at_50%_9.6875rem,rgba(33,61,122,0.6),rgba(33,61,122,0.57)_12.5%,rgba(33,61,122,0.48)_25%,rgba(33,61,122,0.37)_37.5%,rgba(33,61,122,0.25)_50%,rgba(33,61,122,0.14)_62.5%,rgba(33,61,122,0.07)_75%,rgba(33,61,122,0.03)_87.5%,transparent)] bg-no-repeat text-white dark-mode">
  <div id="app" class="relative flex flex-col" style="min-height:100vh; ">
    @sectionMissing('no_ads')
    <horizontal-banner-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" ></horizontal-banner-ad>
    @endif


    @include('nav', [
    'isAuthenticated' => Auth::check(),
    'mainSearchAccount' => $main_search_account,
    'altSearchAccounts' => [$alt_search_account1, $alt_search_account2, $alt_search_account3],
    'regions' => $bladeGlobals["regions"],
    ])

    {{-- In the page flow under the nav; dismissing it removes the space too --}}
    @if(isset($headeralert) && $headeralert !== null && request()->cookie('header_alert_dismissed') !== md5($headeralert))
      <header-alert :alert="{{ json_encode($headeralert) }}" :dismiss-key="{{ json_encode(md5($headeralert)) }}"></header-alert>
    @endif

    

    @sectionMissing('no_ads')
    <rich-media-ad :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}"></rich-media-ad>
    @endif

    @yield('content')

    {{-- Pages with their own bottom section place this ad themselves --}}
    @if (! $__env->hasSection('own_bottom_ad') && ! $__env->hasSection('no_ads'))
    <dynamic-banner-ad  :patreon-user="{{ json_encode(session('patreonSubscriberAdFree')) }}" :index="{{ json_encode(0) }}" :mobile-override="{{ json_encode(false) }}"></dynamic-banner-ad>
    @endif

    {{-- Phones: room above the footer for the help button, which sits there once the page reaches the bottom, so it doesn't cover the last of the content --}}
    <footer class="mt-auto @yield('footer_gap', 'pt-[10rem]') max-md:!pt-24">
      @if(isset($isBackendOff) && $isBackendOff)
        <div class="bg-red text-sm text-center p-1">
          {{ __('The backend is currently offline. Some features may not be available. Please contact :email', ['email' => 'zemill@heroesprofile.com']) }}
        </div>
      @endif
      {{-- The redesign's credit, for everyone until March 2027 --}}
      @if(now()->lt('2027-03-01'))
        @php($redesigner = collect(config('team.contributors'))->firstWhere('name', 'Bluray'))
        <redesign-credit :name="'Bluray'" :discord="{{ json_encode($redesigner['discord'] ?? null) }}"></redesign-credit>
      @endif
      {{-- data-footer-bar: the help button (help-chat) rises to sit just above this once it scrolls into view --}}
      <div data-footer-bar class="bg-surface text-sm px-4 md:px-8 py-6">
        {{-- Desktop: three columns, the outer two sharing what's left, so the links sit at the true centre whatever the sides' widths --}}
        <div class="max-w-[116rem] mx-auto flex flex-col items-center gap-4 text-center md:grid md:grid-cols-[1fr_auto_1fr] md:text-left">
          <div>
            @if(isset($maxReplayID) && ! (isset($isBackendOff) && $isBackendOff))
              {{ __('Patch :patch', ['patch' => $latestPatch]) }}<span class="mx-3">|</span>{{ __(':count replays', ['count' => is_numeric($maxReplayID) ? number_format($maxReplayID) : $maxReplayID]) }}
            @endif
          </div>
          <div class="space-x-3">
            <a href="/FAQ" class="link">{{ __('FAQ') }}</a>
            <a href="/Privacy/Policy" class="link">{{ __('Privacy Policy') }}</a>
            <a href="/Terms/Of/Service" class="link">{{ __('Terms of Service') }}</a>
          </div>
          <div class="md:justify-self-end">Skill Tree Development, LLC<span class="mx-3">|</span><a href="https://heroesprofile.com">Heroes Profile</a><span class="mx-3">|</span><a href="/Team" class="link">{{ __('Team') }}</a></div>
        </div>
      </div>
      <footer-popup-disclaimer></footer-popup-disclaimer>
      @if (app()->getLocale() !== 'en')
      <translation-notice :locale="'{{ app()->getLocale() }}'"></translation-notice>
      @endif
      <help-chat :logged-in="{{ json_encode(Auth::check()) }}"></help-chat>
      @guest
      <login-popup></login-popup>
      @endguest
    </footer>
  </div>

  @stack('scripts')
</body>
</html>