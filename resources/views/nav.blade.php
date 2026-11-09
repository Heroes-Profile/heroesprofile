<div id="mobile-header" class="md:hidden max-md:bg-surface-1 max-md:sticky max-md:top-0 max-md:order-first max-md:z-40 max-md:w-full">
    <div class="relative flex p-2 px-4 justify-center">
<a id="mobile-logo" class="flex shrink-0 items-center whitespace-nowrap font-logo text-lg sm:text-2xl md:hidden" href="/">
                Heroes
                <img class="w-8 sm:w-10 shrink-0 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
        </a>
        <button  id="mobile-toggle" class="md:hidden absolute right-4 top-1/2 -translate-y-1/2 bg-blue rounded-control px-2 min-w-[2.25rem] min-h-[2rem] text-lg leading-none" aria-label="Open menu">=</button>
         
        
    </div>
</div>

<div id="main-menu" class="  main-navigation-wrapper"> <!-- max-md:hidden  - This needs to hide/show on click of the button but only on mobile - either detect mobile with vue, or have a class that is added/taken away that only applies to mobile -->
    {{-- Desktop: above the battletag row (z-[51]), which is above page dropdowns like filters (z-50). Mobile keeps z-50 so a battletag menu can open full screen over it. --}}
    <nav id="main-nav" class="bg-surface-1 text-white z-50 md:z-[52] relative md:px-8 md:py-3 text-sm max-md:pt-2">
      <button  id="mobile-nav-close" class="hidden bg-blue rounded-control px-2 py-1 min-w-[2.25rem] min-h-[2rem] text-base leading-none ml-auto  mr-2" aria-label="Close menu"><i class="fas fa-xmark" aria-hidden="true"></i></button>
       
        <div class="flex items-start justify-center flex-wrap  mx-auto max-md:flex-col max-md:items-stretch max-w-[120rem]" >
             <div class="flex justify-start items-start flex-wrap max-md:flex-col max-md:items-stretch grow mr-auto max-md:mr-0 max-md:w-full max-md:justify-center">
            <a id="nav-logo" class="flex shrink-0 items-center whitespace-nowrap font-logo text-2xl md:mt-[0.375rem] max-md:self-center max-md:mb-2" href="/">
                Heroes
                <img class="w-10 shrink-0 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
            </a>
            <div id="nav-content" class="ml-auto flex justify-between flex-wrap  text-sm md:flex-1 md:ml-8 max-md:flex-col max-md:ml-0 max-md:w-full">
                <div id="nav-links" class=" grow-1 flex items-left md:space-x-5 md:ml-auto flex-wrap md:flex-nowrap max-md:flex-col max-md:w-full ">
                    
                    <div id="nav-global-stats" class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Global Hero Stats<i class="fas fa-chevron-down text-xs ml-2 md:hidden" aria-hidden="true"></i></a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                {{-- ... (Global Hero Stats dropdown items) --}}
                                <a href="/Global/Hero" >Hero Stats</a>
                                <nav-search-flyout
                                    list="heroes"
                                    :label="'Talent Stats'"
                                    :all-label="'All Heroes'"
                                    :base-url-override="'/Global/Talents'"
                                ></nav-search-flyout>
                                <nav-search-flyout
                                    list="heroes"
                                    :label="'Map Stats'"
                                    :all-label="'All Heroes'"
                                    :base-url-override="'/Global/Hero/Maps'"
                                ></nav-search-flyout>
                                <nav-search-flyout
                                    list="heroes"
                                    :label="'Matchup Stats'"
                                    :all-label="'All Heroes'"
                                    :base-url-override="'/Global/Matchups'"
                                ></nav-search-flyout>
                                <a href="/Global/Matchups/Talents" >Matchup Talent Stats</a>
                                <a href="/Global/Compositions" >Compositional Stats</a>
                                <nav-search-flyout
                                    list="heroes"
                                    :label="'Draft Stats'"
                                    :all-label="'All Heroes'"
                                    :base-url-override="'/Global/Draft'"
                                ></nav-search-flyout>
                                <a href="/Global/Party" >Party Stats</a>
                                {{--<a href="/Global/Extra" >Extra Stats</a>--}}
                            </div>
                        </div>
                    </div>

                    <a id="nav-leaderboards" class="  nav-item" href="/Global/Leaderboard"><span>Leaderboards</span></a>

                    <div id="nav-tools" class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Tools<i class="fas fa-chevron-down text-xs ml-2 md:hidden" aria-hidden="true"></i></a>
                        <div class="nav-dropdown ">
                            <div class=" nav-dropdown-inner-wrapper ">
                                <a href="/Global/Talents/Builder" >Talent Builder</a>
                                <a href="/Drafter" >Drafter</a>
                                <a href="/Upload" >Replay Uploader</a>
                                {{--<a href="/" class="block px-4 py-2 border-b border-darken hover:bg-lighten cursor-not-allowed pointer-events-none">Activity Graphs</a>--}}
                                <a href="/Tools/AutoBattler" >Auto Battler</a>
                                <a href="/Match/Prediction/Game" >Match Prediction Game</a>
                                <a href="/Match/Search" >Replay Search</a>
                                <a href="/Tools/RandomizeMe" >Randomize Me</a>
                                <a href="/Tools/Activity" >Activity Graphs</a>
                                <a href="/Analytics" >Analytics - Pre Alpha</a>
                                @if (! empty($bladeGlobals['xalatathRecapAvailable']))
                                <a href="/Event/Xalatath" >Xal'atath Recap</a>
                                @endif
                                @if (\App\Services\Twitch\TwitchAccess::visible())
                                <a href="/Twitch" >Twitch Streamers (Beta)</a>
                                @endif
                                <a href="/" >Find a Player</a>


                            </div>
                        </div>
                    </div>

                    <a id="nav-esports" class=" nav-item" href="/Esports"><span>Community Esports</span></a>

                    <div id="nav-help" class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Help<i class="fas fa-chevron-down text-xs ml-2 md:hidden" aria-hidden="true"></i></a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/issues/new?assignees=&labels=&projects=&template=bug_report.md" target="_blank" >Submit an Issue</a>
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/discussions/new?category=ideas" target="_blank" >Submit a Request</a>
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" >Discussions</a>
                                 <a href="/Github/Change/Log">Change Log</a>
                                <a href="/FAQ">FAQ</a>
                                <a href="#" onclick="event.preventDefault(); window.dispatchEvent(new Event('open-help-chat'))">Help Assistant</a>
                                <a href="/Contact" >Contact Us</a>
                            </div>

             
                        </div>
                    </div>

                    

                    

                    <div id="nav-donate" class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Donate<i class="fas fa-chevron-down text-xs ml-2 md:hidden" aria-hidden="true"></i></a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                 <a href="https://www.patreon.com/c/heroesprofile" target="_blank" >Patreon</a>
                                 <a href="https://opencollective.com/heroes-profile" target="_blank" >Open Collective</a>
                            </div>

             
                        </div>
                    </div>

                    

                        @if(isset($mainSearchAccount))
                        <div id="nav-main-account" class="relative group inline-block nav-item ">
                            <a class="cursor-pointer">
                            {{ $mainSearchAccount['battletag'] }} 
                            @if(isset($regions[$mainSearchAccount['region']]))
                                ({{ $regions[$mainSearchAccount['region']] }})
                            @else
                                (Region not found, log out and back in)
                            @endif
                            <i class="fas fa-chevron-down text-xs ml-2 md:hidden" aria-hidden="true"></i>
                            </a>
                            <div class="nav-dropdown  ">
                                <div class="nav-dropdown-inner-wrapper ">
                                    {{-- ... (mainSearchAccount dropdown items) --}}
                                    @if (Auth::check() && app(\App\Services\AnalyticsService::class)->isAllowed(Auth::user()))
                                    <div class="nav-flyout-wrapper">
                                        <a href="/Analytics" class="flex justify-between items-center bg-teal font-bold"><span><i class="fas fa-chart-line mr-2"></i>Analytics</span> <span class="nav-flyout-arrow ml-2">›</span></a>
                                        <div class="nav-flyout">
                                            <div class="nav-dropdown-inner-wrapper rounded-b rounded-r">
                                                <a href="/Analytics">Summary</a>
                                                <a href="/Analytics/Deaths">Deaths</a>
                                                <a href="/Analytics/Map">Map &amp; Positions</a>
                                                <a href="/Analytics/Positioning">Positioning</a>
                                                <a href="/Analytics/Timelines">Timelines &amp; Objectives</a>
                                                <a href="/Analytics/Activity">Activity</a>
                                                <a href="/Analytics/Sessions">Sessions</a>
                                                <a href="/Analytics/Coaching">Coaching</a>
                                                <a href="/Analytics/Stacks">Stacks</a>
                                                <a href="/Analytics/Match/History">Match History</a>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <a href="/Analytics" class="bg-teal font-bold"><i class="fas fa-chart-line mr-2"></i>Analytics</a>
                                    @endif
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}" >Profile</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/FriendFoe" >Friends and Foes</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Party" >Party</a>
                                    <nav-search-flyout
                                        :battletag="'{{ $mainSearchAccount['battletag'] }}'"
                                        :blizz-id="{{ $mainSearchAccount['blizz_id'] }}"
                                        :region="{{ $mainSearchAccount['region'] }}"
                                        list="heroes"
                                        :label="'Heroes'"
                                        :subpath="'Hero'"
                                        :all-label="'All Heroes'"
                                    ></nav-search-flyout>
                                    <div class="nav-flyout-wrapper">
                                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role" class="flex justify-between items-center">Roles <span class="nav-flyout-arrow ml-2">›</span></a>
                                        <div class="nav-flyout">
                                            <div class="nav-dropdown-inner-wrapper rounded-b rounded-r">
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role">All Roles</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Bruiser">Bruiser</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Healer">Healer</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Melee%20Assassin">Melee Assassin</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Ranged%20Assassin">Ranged Assassin</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Support">Support</a>
                                                <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role/Tank">Tank</a>
                                            </div>
                                        </div>
                                    </div>
                                    <nav-search-flyout
                                        :battletag="'{{ $mainSearchAccount['battletag'] }}'"
                                        :blizz-id="{{ $mainSearchAccount['blizz_id'] }}"
                                        :region="{{ $mainSearchAccount['region'] }}"
                                        list="maps"
                                        :label="'Maps'"
                                        :subpath="'Map'"
                                        :all-label="'All Maps'"
                                    ></nav-search-flyout>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Matchups" >Matchups</a>
                                    <nav-search-flyout
                                        :battletag="'{{ $mainSearchAccount['battletag'] }}'"
                                        :blizz-id="{{ $mainSearchAccount['blizz_id'] }}"
                                        :region="{{ $mainSearchAccount['region'] }}"
                                        list="heroes"
                                        :label="'Talents'"
                                        :subpath="'Talents'"
                                        :all-label="'All Talents'"
                                    ></nav-search-flyout>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/MMR" >HP MMR Breakdown</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Awards" >Awards</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Match/History" >Match History</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Match/Latest" >Latest Match</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    
                </div>
             
              
            </div>
</div>
              <div id="right-nav-wrapper" class="flex max-md:flex-wrap max-md:items-center justify-center flex-none mx-auto">

                <div id="nav-buttons" class="flex gap-3 md:pl-4 max-md:gap-2 max-md:w-full max-md:px-3 max-md:pt-4 max-md:flex-wrap max-md:justify-center">
                    <custom-button id="nav-api-button" class="font-semibold max-md:text-xs max-md:px-3 max-md:py-1.5" :href="'/Api'" :text="'API'" :alt="'API'" :color="'teal'"></custom-button>
                    <custom-button id="nav-uploader-button" class="font-semibold max-md:text-xs max-md:px-3 max-md:py-1.5" :href="'/Upload'" :text="'Replay Uploader'" :alt="'Replay Uploader'" :color="'teal'"></custom-button>
                    <custom-button id="nav-patreon-button" class="font-semibold max-md:text-xs max-md:px-3 max-md:py-1.5" :href="'https://www.patreon.com/heroesprofile'" :targetblank="true" :text="'Remove Ads / Patreon'" :alt="'Patreon'" :color="'red'"></custom-button>
                </div>


                <dark-mode-toggle id="nav-dark-mode-toggle" class="!flex items-center md:ml-4 max-md:my-3 max-md:mr-2" :darkmode="{{ json_encode(! empty($bladeGlobals['darkmode'])) }}" :authenticated="{{ json_encode($isAuthenticated) }}"></dark-mode-toggle>

                @if($isAuthenticated)
                    <div id="nav-user-menu" class="relative group inline-block md:ml-4 max-md:my-3 max-md:flex max-md:items-center">
                            {{-- On mobile a tap opens the panel full screen, like the alt-player buttons (MobileNavHack) --}}
                            <div data-battletag="nav-user-menu" class="mobile-secondary-nav-open flex items-center cursor-pointer">
                                <img
                                class="card-img-top relative hover:opacity-75 w-10 h-10 rounded"
                                src="/images/heroes/auto_select.jpg"
                                alt="Settings">
                            </div>
                            {{-- Same panel as the primary menus, anchored to the right edge --}}
                            <div data-battletag="nav-user-menu" class="nav-dropdown nav-dropdown-secondary-nav md:!left-auto md:!right-0 max-md:fixed max-md:top-0 max-md:w-full max-md:h-full max-md:bg-gray-dark">
                                <div class="nav-dropdown-inner-wrapper">
                                    <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal">{{ $mainSearchAccount['battletag_full'] }} <button class="close-secondary-nav bg-blue rounded px-3 py-1 min-w-[2.75rem] min-h-[2.5rem] text-lg" aria-label="Close menu">x</button></div>
                                    <span class="block px-4 py-2 whitespace-nowrap border-b-2 border-[#333] max-md:hidden">{{ $mainSearchAccount['battletag_full'] }}</span>
                                    <a href="/Profile/Settings">Settings</a>
                                    <a href="/Battlenet/Logout">Logout</a>
                                </div>
                            </div>
                        </div>





                @else
                    <custom-button id="nav-login-button" class="md:ml-4 max-md:my-3 font-semibold max-md:text-xs max-md:px-3 max-md:py-1.5" :href="'/Authenticate/Battlenet'" :text="'Login'" :alt="'Login'" :color="'teal'"></custom-button>
                @endif
                </div>
                {{-- Mobile only: under the buttons --}}
                <search-component id="nav-search" :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a BattleTag'" class="!mt-0 mx-4 md:hidden"></search-component>
        </div>
    </nav>
    
<nav id="alt-account-nav" class="relative md:z-[51] flex justify-end items-center md:gap-4 max-md:justify-center max-md:gap-2 max-md:px-3 max-md:pb-6 max-md:mt-4 w-full max-w-[120rem] mx-auto md:px-8 md:py-4 alt-acct-nav max-md:flex-wrap">
  {{-- League row colours, from each league's logo --}}
  @php($esportRowColours = ['NGS' => 'bg-[#f26b30]', 'CCL' => 'bg-gradient-to-r from-[#d42a0e] via-[#f07c12] to-[#f7c51e]', 'MastersClash' => 'bg-[#5a5ad6]', 'hi' => 'bg-[#6b6b6b]', 'hi_nc' => 'bg-[#6b6b6b]', 'Other' => 'bg-lblue'])
  {{-- Other series, each from its own logo; any series not listed keeps Other's colour --}}
  @php($esportSeriesColours = ['Agon League' => 'bg-[#c4161c]', 'All Hallows Cup' => 'bg-[#e5531a]', 'Bloodlust' => 'bg-[#a3121b]', 'Brazilian Championship' => 'bg-[#009c3b]', 'DreamHack' => 'bg-[#e4572e]', 'Gold Series' => 'bg-[#a8790f]', 'HeroesHype' => 'bg-[#6a4fb3]', 'HGC' => 'bg-[#1f5fae]', 'King of the Storm' => 'bg-[#6b2fa3]', 'Machines of War Showdown' => 'bg-[#1e6fc4]', 'Nexus Contest' => 'bg-[#1d3f8f]', 'Nexus Games' => 'bg-[#1565c0]', 'Toasty and Gladman' => 'bg-[#2a8fb8]', 'ZOTAC Cup' => 'bg-[#f39a1e]'])
  @foreach($altSearchAccounts as $index => $account)
    @if($account && isset($account['esport_nav']))
      @php($esportNav = $account['esport_nav'])
      @php($esportPlayerUrl = $esportNav['url'])
      @php($esportQuery = $esportNav['query'])
      {{-- The row colour as a plain background, for the menu's hover --}}
      @php($esportRowClass = $esportNav['key'] === 'Other' ? ($esportSeriesColours[$esportNav['name']] ?? $esportRowColours['Other']) : $esportRowColours[$esportNav['key']])
      @php($esportHover = $esportNav['key'] === 'CCL' ? 'linear-gradient(to right, #d42a0e, #f07c12, #f7c51e)' : (preg_match('/#[0-9a-f]{6}/i', $esportRowClass, $hex) ? $hex[0] : '#315bb6'))
      <div id="alt-account-{{ $index }}" class="relative group inline-block text-sm">
        <a data-battletag="{{ $account['battletag'] }} {{ $esportNav['name'] }}" class="mobile-secondary-nav-open btn bg-teal hover:bg-lteal inline-flex items-center">{{ $account['battletag'] }}<img src="{{ $esportNav['logo'] }}" alt="{{ $esportNav['name'] }}" class="max-md:hidden h-[1.4em] w-auto ml-2 {{ $esportNav['key'] === 'Other' ? 'rounded-sm' : 'brightness-0 invert' }}"></a>
        <div data-battletag="{{ $account['battletag'] }} {{ $esportNav['name'] }}" style="--esport-hover: {{ $esportHover }};" class="nav-dropdown esport-nav-dropdown absolute  hidden z-50 md:pt-3 absolute md:right-0 md:min-w-[12.5rem] max-md:top-0 max-md:fixed max-md:w-full nav-dropdown-secondary-nav max-md:bg-gray-dark max-md:h-full">
            <div class="nav-dropdown-inner-wrapper">
              <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal"><span class="inline-flex items-center">{{ $account['battletag'] }}<img src="{{ $esportNav['logo'] }}" alt="{{ $esportNav['name'] }}" class="h-[1.4em] w-auto ml-2 {{ $esportNav['key'] === 'Other' ? 'rounded-sm' : 'brightness-0 invert' }}"></span> <button class="close-secondary-nav bg-blue rounded px-3 py-1 min-w-[2.75rem] min-h-[2.5rem] text-lg" aria-label="Close menu">x</button></div>
              <!-- The league's own pages, where a player's menu has Analytics -->
              <div class="nav-flyout-wrapper">
                <a href="{{ $esportNav['main'] }}" class="flex justify-between items-center {{ $esportNav['key'] === 'Other' ? ($esportSeriesColours[$esportNav['name']] ?? $esportRowColours['Other']) : $esportRowColours[$esportNav['key']] }} font-bold"><span class="whitespace-nowrap"><img src="{{ $esportNav['logo'] }}" alt="{{ $esportNav['name'] }}" class="inline-block align-middle h-[1.1em] w-auto {{ $esportNav['key'] === 'Other' ? 'rounded-sm' : 'brightness-0 invert' }}">@if($esportNav['label'])<span class="ml-2 inline-block max-w-[9rem] max-md:max-w-none truncate align-middle" title="{{ $esportNav['name'] }}">{{ $esportNav['name'] }}</span>@endif</span> <span class="nav-flyout-arrow ml-2">›</span></a>
                <div class="nav-flyout">
                  <div class="nav-dropdown-inner-wrapper rounded-b rounded-r">
                    {{-- The full name, which the row cuts short --}}
                    @if($esportNav['label'])<a href="{{ $esportNav['main'] }}" class="font-bold">{{ $esportNav['name'] }}</a>@endif
                    @foreach($esportNav['tabs'] as $tabUrl => $tabLabel)
                    <a href="{{ $tabUrl }}">{{ $tabLabel }}</a>
                    @endforeach
                  </div>
                </div>
              </div>
              <a href="{{ $esportPlayerUrl }}{{ $esportQuery }}">Profile</a>
              <a href="{{ $esportPlayerUrl }}/FriendFoe{{ $esportQuery }}">Friends and Foes</a>
              <nav-search-flyout
                list="heroes"
                :label="'Heroes'"
                :base-url-override="'{{ $esportPlayerUrl }}/Hero'"
                query="{{ $esportQuery }}"
                :all-label="'All Heroes'"
              ></nav-search-flyout>
              <div class="nav-flyout-wrapper">
                <a href="{{ $esportPlayerUrl }}/Role{{ $esportQuery }}" class="flex justify-between items-center">Roles <span class="nav-flyout-arrow ml-2">›</span></a>
                <div class="nav-flyout">
                  <div class="nav-dropdown-inner-wrapper rounded">
                    <a href="{{ $esportPlayerUrl }}/Role{{ $esportQuery }}">All Roles</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Bruiser{{ $esportQuery }}">Bruiser</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Healer{{ $esportQuery }}">Healer</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Melee%20Assassin{{ $esportQuery }}">Melee Assassin</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Ranged%20Assassin{{ $esportQuery }}">Ranged Assassin</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Support{{ $esportQuery }}">Support</a>
                    <a href="{{ $esportPlayerUrl }}/Role/Tank{{ $esportQuery }}">Tank</a>
                  </div>
                </div>
              </div>
              <nav-search-flyout
                list="maps"
                :label="'Maps'"
                :base-url-override="'{{ $esportPlayerUrl }}/Map'"
                query="{{ $esportQuery }}"
                :all-label="'All Maps'"
              ></nav-search-flyout>
              <a href="{{ $esportPlayerUrl }}/Matchups{{ $esportQuery }}">Matchups</a>
              <nav-search-flyout
                list="heroes"
                :label="'Talents'"
                :base-url-override="'{{ $esportPlayerUrl }}/Talents'"
                query="{{ $esportQuery }}"
                :all-label="'All Talents'"
              ></nav-search-flyout>
              <a href="{{ $esportPlayerUrl }}/Match/History{{ $esportQuery }}">Match History</a>
              <a href="{{ $esportPlayerUrl }}/Match/Latest{{ $esportQuery }}">Latest Match</a>
              <remove-battletag-nav :index="{{ $index }}" class="remove-account text-right bg-surface-3"></remove-battletag-nav>
            </div>
        </div>
      </div>
    @elseif($account)
      <div id="alt-account-{{ $index }}" class="relative group inline-block text-sm">
        <a data-battletag="{{ $account['battletag'] }}" class="mobile-secondary-nav-open btn bg-teal hover:bg-lteal ">{{ $account['battletag'] }}@if(isset($regions[$account['region']]))<span class="text-[0.7em] ml-2">{{ $regions[$account['region']] }}</span>@endif</a>
        <div data-battletag="{{ $account['battletag'] }}" class="nav-dropdown absolute  hidden z-50 md:pt-3 absolute md:right-0 md:min-w-[12.5rem] max-md:top-0 max-md:fixed max-md:w-full nav-dropdown-secondary-nav max-md:bg-gray-dark max-md:h-full">
            <div class="nav-dropdown-inner-wrapper">
              <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal">{{ $account['battletag'] }}@if(isset($regions[$account['region']])) ({{ $regions[$account['region']] }})@endif <button class="close-secondary-nav bg-blue rounded px-3 py-1 min-w-[2.75rem] min-h-[2.5rem] text-lg" aria-label="Close menu">x</button></div>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}" >Profile</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/FriendFoe" >Friends and Foes</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Party" >Party</a>
              <nav-search-flyout
                :battletag="'{{ $account['battletag'] }}'"
                :blizz-id="{{ $account['blizz_id'] }}"
                :region="{{ $account['region'] }}"
                list="heroes"
                :label="'Heroes'"
                :subpath="'Hero'"
                :all-label="'All Heroes'"
              ></nav-search-flyout>
              <div class="nav-flyout-wrapper">
                <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role" class="flex justify-between items-center">Roles <span class="nav-flyout-arrow ml-2">›</span></a>
                <div class="nav-flyout">
                  <div class="nav-dropdown-inner-wrapper rounded">
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role">All Roles</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Bruiser">Bruiser</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Healer">Healer</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Melee%20Assassin">Melee Assassin</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Ranged%20Assassin">Ranged Assassin</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Support">Support</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role/Tank">Tank</a>
                  </div>
                </div>
              </div>
              <nav-search-flyout
                :battletag="'{{ $account['battletag'] }}'"
                :blizz-id="{{ $account['blizz_id'] }}"
                :region="{{ $account['region'] }}"
                list="maps"
                :label="'Maps'"
                :subpath="'Map'"
                :all-label="'All Maps'"
              ></nav-search-flyout>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Matchups">Matchups</a>
              <nav-search-flyout
                :battletag="'{{ $account['battletag'] }}'"
                :blizz-id="{{ $account['blizz_id'] }}"
                :region="{{ $account['region'] }}"
                list="heroes"
                :label="'Talents'"
                :subpath="'Talents'"
                :all-label="'All Talents'"
              ></nav-search-flyout>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/MMR">HP MMR Breakdown</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Awards">Awards</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Match/History">Match History</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Match/Latest">Latest Match</a>
              <remove-battletag-nav :index="{{ $index }}" class="remove-account text-right bg-surface-3"></remove-battletag-nav>
            </div>
        </div>
      </div>
    @endif
  @endforeach
  <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a BattleTag'" class="max-md:hidden"></search-component>
</nav>
</div>
<mobile-nav-hack></mobile-nav-hack>