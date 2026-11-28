@php
    $apiUser = Auth::guard('api_web')->user();
@endphp

@if($navImpersonating)
  {{-- Above the nav and on every page: anything done here is genuinely this
       customer's, so it must never be possible to forget. --}}
  <div class="w-full bg-yellow text-black px-4 py-2 text-sm flex flex-wrap items-center justify-center gap-3">
    <span>
      <strong>{{ __('Viewing as :email.', ['email' => $apiUser->email]) }}</strong>
      {{ __('Anything you do here happens on their account.') }}
    </span>
    <form method="POST" action="/Api/Admin/Impersonate/Stop" class="inline">
      @csrf
      <button type="submit" class="underline">{{ __('Stop') }}</button>
    </form>
  </div>
@endif

{{-- Phones, as the main site's nav: a bar with the logo and a menu button; the menu opens full screen
     (the same ids and main-navigation-wrapper class, so MobileNavHack opens and closes it) --}}
<div id="mobile-header" class="md:hidden max-md:bg-surface max-md:sticky max-md:top-0 max-md:z-40 max-md:w-full border-b-2 border-teal">
  <div class="relative flex items-center py-2 px-4 justify-center">
    <a href="/Api" class="flex shrink-0 items-center gap-3 whitespace-nowrap h-11">
      <img class="w-9 shrink-0" src="/images/logo/heroesprofilelogo.png" width="486" height="432" alt="{{ __('Heroes Profile Logo') }}" />
      <span class="text-xl font-bold">Heroes Profile</span>
      <span class="text-sm font-bold tracking-[0.06em] text-teal bg-teal/[0.18] rounded-[0.375rem] px-2 py-0.5">API</span>
    </a>
    <button id="mobile-toggle" class="absolute right-4 top-1/2 -translate-y-1/2 bg-blue rounded w-11 h-11 flex items-center justify-center text-2xl leading-none" aria-label="{{ __('Open menu') }}">=</button>
  </div>
</div>

{{-- Laid out like the main site's nav (nav.blade.php): logo left, links centred, buttons right --}}
<div id="main-menu" class="main-navigation-wrapper">
<nav class="w-full bg-surface md:border-b-2 border-teal text-sm relative">
  <button id="mobile-nav-close" class="hidden absolute right-4 top-2 z-10 bg-blue rounded w-11 h-11 items-center justify-center text-lg leading-none" aria-label="{{ __('Close menu') }}"><basil-icon name="cross" aria-hidden="true"></basil-icon></button>
  <div class="max-w-[120rem] mx-auto md:px-8 md:py-3 flex flex-wrap items-center justify-between gap-x-8 md:gap-y-3 max-md:flex-col max-md:items-stretch max-md:pt-2">

    {{-- The logo, "Heroes Profile" and an API tag, as in the design --}}
    <a href="/Api" class="flex shrink-0 items-center gap-[0.875rem] whitespace-nowrap max-md:self-center max-md:h-11 max-md:mb-2">
      <img class="w-10 shrink-0" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
      <span class="text-[1.375rem] font-bold">Heroes Profile</span>
      <span class="text-base font-bold tracking-[0.06em] text-teal bg-teal/[0.18] rounded-[0.375rem] px-2.5 py-1 leading-[1.1875rem]">API</span>
    </a>

    {{-- Phones: full-width rows with a divider, as the main menu's --}}
    <div class="flex flex-wrap items-center justify-center gap-1 md:mx-auto md:[&>a]:px-6 md:[&>a]:py-3.5 md:[&>a]:rounded [&>a:hover]:bg-surface-2 max-md:flex-col max-md:items-stretch max-md:gap-0 max-md:[&>a]:justify-center max-md:[&>a]:text-center max-md:[&>a]:py-[0.625rem] max-md:[&>a]:border-b max-md:[&>a]:border-white/20 max-md:border-t max-md:border-white/20">
      <a href="/Api" class="hover:text-lteal">{{ __('Overview') }}</a>
      <a href="/Api/Docs" class="hover:text-lteal">{{ __('Documentation') }}</a>
      <a href="/Api/Migrating" class="hover:text-lteal">{{ __('Migrating') }}</a>
      <a href="https://github.com/Heroes-Profile/heroesprofile" target="_blank" rel="noopener"
         class="hover:text-lteal flex items-center gap-1.5" title="{{ __('Heroes Profile on GitHub') }}">
        <i class="fab fa-github"></i> {{ __('GitHub') }}
      </a>

      {{-- Plans and per-endpoint limits are reached from the landing page, which is
           where someone deciding on a tier already is. --}}

      @if($apiUser)
        <a href="/Api/Account" class="hover:text-lteal">{{ __('Account') }}</a>
        <a href="/Api/Account/Billing" class="hover:text-lteal">{{ __('Billing') }}</a>

        {{-- The grant, not admin mode: an admin viewing the site as a customer keeps
             the way back. --}}
        @if($apiUser->isAdmin())
          <a href="/Api/Admin" class="hover:text-lteal">{{ __('Admin') }}</a>
        @endif
      @endif
    </div>

    {{-- Phones: the buttons as full-width bars, then the rest centred underneath, as on the main menu --}}
    <div class="flex flex-wrap items-center justify-center gap-4 max-md:gap-0 max-md:pb-4 max-md:[&>a.btn]:w-full max-md:[&>a.btn]:!rounded-none max-md:[&>form]:w-full max-md:[&>form>button]:w-full max-md:[&>form>button]:!rounded-none max-md:[&>*:last-child]:mt-4 max-md:[&>*:last-child]:mx-auto">
      {{-- The portal has its own shell, so nothing else here leads back out. --}}
      <custom-button class="font-semibold min-w-[10.3125rem]" :href="'/'" :alt="'Main Site'" :color="'red'"><basil-icon name="arrow-small" class="text-xs mr-2 rotate-180"></basil-icon>{{ __('Main Site') }}</custom-button>

      @if($apiUser)
        {{-- Keyed on what the API will actually answer with, not on `migrated`
             alone — an account with no plan is on fixtures too. --}}
        @if($navServesFixtures)
          <a href="/Api/Account" class="px-2 py-1 rounded-lg bg-yellow text-black text-xs" title="{{ __('Your API calls are returning example data, not live data') }}">
            {{ __('Test data') }}
          </a>
        @endif

        @if($apiUser->headerLabel() !== null)
          <span class="text-subtext hidden md:inline">{{ $apiUser->headerLabel() }}</span>
        @endif

        <form method="POST" action="/Api/Logout" class="inline">
          @csrf
          <button type="submit" class="btn bg-blue hover:bg-lblue font-semibold">{{ __('Logout') }}</button>
        </form>
      @else
        <custom-button class="font-semibold" :href="'/Api/Login'" :text="'Login'" :alt="'Login'" :color="'teal'"></custom-button>
        <custom-button class="font-semibold min-w-[10.3125rem]" :href="'/Api/Register'" :text="'Register'" :alt="'Register'"></custom-button>
      @endif

      {{-- The site's language, the same picker and setting as the main site's nav --}}
      <language-picker :locale="'{{ app()->getLocale() }}'" :locales="{{ json_encode(config('app.supported_locales')) }}" :authenticated="{{ json_encode($isAuthenticated) }}"></language-picker>
    </div>
  </div>
</nav>
</div>
<mobile-nav-hack></mobile-nav-hack>
