@php
    $apiUser = Auth::guard('api_web')->user();
@endphp

@if($navImpersonating)
  {{-- Above the nav and on every page: anything done here is genuinely this
       customer's, so it must never be possible to forget. --}}
  <div class="w-full bg-yellow text-black px-4 py-2 text-sm flex flex-wrap items-center justify-center gap-3">
    <span>
      <strong>Viewing as {{ $apiUser->email }}.</strong>
      Anything you do here happens on their account.
    </span>
    <form method="POST" action="/Api/Admin/Impersonate/Stop" class="inline">
      @csrf
      <button type="submit" class="underline">Stop</button>
    </form>
  </div>
@endif

<nav class="w-full bg-lighten border-b-4 border-teal">
  <div class="container-boxed mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">

    <a href="/Api" class="flex items-center font-logo text-xl">
      Heroes
      <img class="w-8 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
      Profile
      <span class="ml-2 text-sm font-sans uppercase tracking-widest text-lteal">API</span>
    </a>

    <div class="flex flex-wrap items-center gap-4 text-sm">
      {{-- The portal has its own shell, so nothing else here leads back out. --}}
      <a href="/" class="hover:text-lteal flex items-center gap-1">
        <i class="fas fa-arrow-left text-xs"></i> Main Site
      </a>

      <a href="/Api/Docs" class="hover:text-lteal">Docs</a>
      <a href="/Api/Migrating" class="hover:text-lteal">Migrating</a>
      <a href="https://github.com/Heroes-Profile/heroesprofile" target="_blank" rel="noopener"
         class="hover:text-lteal flex items-center gap-1" title="Heroes Profile on GitHub">
        <i class="fab fa-github"></i> GitHub
      </a>

      {{-- Plans and per-endpoint limits are reached from the landing page, which is
           where someone deciding on a tier already is. --}}

      @if($apiUser)
        <a href="/Api/Account" class="hover:text-lteal">Account</a>
        <a href="/Api/Account/Billing" class="hover:text-lteal">Billing</a>

        {{-- The grant, not admin mode: an admin viewing the site as a customer keeps
             the way back. --}}
        @if($apiUser->isAdmin())
          <a href="/Api/Admin" class="hover:text-lteal">Admin</a>
        @endif

        {{-- Keyed on what the API will actually answer with, not on `migrated`
             alone — an account with no plan is on fixtures too. --}}
        @if($navServesFixtures)
          <a href="/Api/Account" class="px-2 py-1 rounded bg-yellow text-black text-xs" title="Your API calls are returning example data, not live data">
            Test data
          </a>
        @endif

        <span class="text-gray-medium hidden md:inline">{{ $apiUser->email }}</span>

        <form method="POST" action="/Api/Logout" class="inline">
          @csrf
          <button type="submit" class="hover:text-lteal underline">Logout</button>
        </form>
      @else
        <a href="/Api/Login" class="hover:text-lteal">Login</a>
        <custom-button :href="'/Api/Register'" :text="'Register'" :alt="'Register'" :size="'small'"></custom-button>
      @endif
    </div>
  </div>
</nav>
