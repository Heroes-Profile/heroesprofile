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

{{-- Laid out like the main site's nav (nav.blade.php): logo left, links centred, buttons right --}}
<nav class="w-full bg-surface border-b-2 border-teal text-sm">
  <div class="max-w-[120rem] mx-auto px-4 md:px-8 py-3 flex flex-wrap items-center justify-between gap-x-8 gap-y-3">

    {{-- The logo, "Heroes Profile" and an API tag, as in the design --}}
    <a href="/Api" class="flex shrink-0 items-center gap-[0.875rem] whitespace-nowrap">
      <img class="w-10 shrink-0" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
      <span class="text-[1.375rem] font-bold">Heroes Profile</span>
      <span class="text-base font-bold tracking-[0.06em] text-teal bg-teal/[0.18] rounded-[0.375rem] px-2.5 py-1 leading-[1.1875rem]">API</span>
    </a>

    <div class="flex flex-wrap items-center justify-center gap-1 md:mx-auto [&>a]:px-6 [&>a]:py-3.5 [&>a]:rounded [&>a:hover]:bg-surface-2">
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

    <div class="flex flex-wrap items-center justify-center gap-4">
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
