{{-- The avatar and its Settings/Logout menu. The nav shows it on desktop and, on phones, beside the saved players;
     each copy has its own id, which MobileNavHack uses to pair the avatar with its panel. --}}
<div id="{{ $menuId }}" class="relative group inline-block {{ $menuClass }}">
        {{-- On mobile a tap opens the panel full screen, like the alt-player buttons (MobileNavHack) --}}
        {{-- Patreon subscribers can have a gold border and glow, the Patreon star's gold; Void eye holders a purple one (app.css). Ad-free alone gives neither --}}
        @php($navPatreon = session('patreonSubscriberSiteFlair'))
        {{-- Listed under the battletag in the menu, owner first; the account's own flair, shown even if hidden in Profile Settings --}}
        @php($navOwner = Auth::user()->blizz_id == 67280 && Auth::user()->region == 1)
        {{-- The avatar's border: the one chosen in Profile Settings if it's still earned, else Site Owner, Patreon, then the Void eye --}}
        @php($navBorders = array_keys(array_filter(['owner' => $navOwner, 'patreon' => $navPatreon, 'void_eye' => (bool) session('voidEyeFlair')])))
        @php($navBorderSetting = optional(Auth::user()->userSettings->firstWhere('setting', 'avatar_border'))->value)
        @php($navBorder = $navBorderSetting === 'none' || in_array($navBorderSetting, $navBorders, true) ? $navBorderSetting : ($navBorders[0] ?? 'none'))
        <div data-battletag="{{ $menuId }}" class="mobile-secondary-nav-open relative flex items-center cursor-pointer">
            <span class="group/avatar relative block w-11 h-11 rounded overflow-hidden transition-shadow {{ ['owner' => 'border-2 owner-glow', 'patreon' => 'border-2 border-gold patreon-glow', 'void_eye' => 'border-2 border-[#a77bf3] void-glow'][$navBorder] ?? '' }}">
                <img
                class="w-full h-full object-cover"
                src="/images/heroes/auto_select.jpg"
                alt="{{ __('Settings') }}">
                <span class="absolute inset-0 opacity-0 group-hover/avatar:opacity-100 transition-opacity {{ ['owner' => 'bg-[rgba(79,195,255,0.12)]', 'patreon' => 'bg-[rgba(255,215,0,0.1)]', 'void_eye' => 'bg-[rgba(167,123,243,0.12)]'][$navBorder] ?? 'bg-[rgba(18,7,38,0.2)]' }}" aria-hidden="true"></span>
            </span>
            @if($navBorder === 'owner')<owner-sparks></owner-sparks>@endif
        </div>
        {{-- Same panel as the primary menus, anchored to the right edge --}}
        <div data-battletag="{{ $menuId }}" class="nav-dropdown nav-dropdown-secondary-nav md:!left-auto md:!right-0 max-md:fixed max-md:top-0 max-md:w-full max-md:h-full max-md:bg-gray-dark">
            <div class="nav-dropdown-inner-wrapper">
                <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal">{{ $mainSearchAccount['battletag_full'] }} <button class="close-secondary-nav bg-blue rounded px-3 py-1 min-w-[2.75rem] min-h-[2.5rem] text-lg" aria-label="{{ __('Close menu') }}">x</button></div>
                <span class="block px-4 py-2 whitespace-nowrap border-b-2 border-[#333] max-md:hidden">{{ $mainSearchAccount['battletag_full'] }}@if($navOwner)<span class="block text-xs text-[#4fc3ff]">{{ __('Site Owner') }}</span>@endif @if($navPatreon)<span class="block text-xs text-gold">{{ __('Patreon Subscriber') }}</span>@endif @if(session('voidEyeFlair'))<span class="block text-xs text-[#a77bf3]">{{ __('Marked by the Void') }}</span>@endif</span>
                <a href="/Profile/Settings">{{ __('Settings') }}</a>
                <a href="/Battlenet/Logout">{{ __('Logout') }}</a>
            </div>
        </div>
    </div>
