@php
    $apiUser = Auth::guard('api_web')->user();
@endphp

<nav class="w-full bg-lighten border-b-4 border-teal">
  <div class="container-boxed mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">

    <a href="/Api" class="flex items-center font-logo text-xl">
      Heroes
      <img class="w-8 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
      Profile
      <span class="ml-2 text-sm font-sans uppercase tracking-widest text-lteal">API</span>
    </a>

    <div class="flex flex-wrap items-center gap-4 text-sm">
      {{-- Docs / Test Client / Pricing links go here once those pages exist. --}}

      @if($apiUser)
        <a href="/Api/Account" class="hover:text-lteal">Account</a>

        @unless($apiUser->hasMigrated())
          <a href="/Api/Account" class="px-2 py-1 rounded bg-yellow text-black text-xs" title="You are receiving test data until you activate live data">
            Test data
          </a>
        @endunless

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
