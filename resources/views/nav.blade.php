<div class="md:hidden max-md:bg-[#23252f] max-md:fixed max-md:z-40 max-md:w-full">
    <div class="flex p-2 px-4 justify-between">
<a class=" flex items-center font-logo text-2xl md:hidden" href="/">
                Heroes
                <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
        </a>
        <button  id="mobile-toggle" class="md:hidden bg-blue rounded-lg px-3 min-w-[44px] min-h-[40px] text-xl" aria-label="Open menu">=</button>
         
        
    </div>
</div>

<div id="main-menu" class="  main-navigation-wrapper"> <!-- max-md:hidden  - This needs to hide/show on click of the button but only on mobile - either detect mobile with vue, or have a class that is added/taken away that only applies to mobile -->
    <nav class="bg-[#23252f] text-white z-50 relative md:px-8 md:py-3 text-sm max-md:pt-2">
      <button  id="mobile-nav-close" class="hidden bg-blue rounded-lg px-3 py-1 min-w-[44px] min-h-[40px] text-lg ml-auto  mr-2" aria-label="Close menu">x</button>
       
        <div class="flex items-center justify-between flex-wrap max-w-[1920px] mx-auto max-md:flex-col" >
             
            <a class=" flex items-center font-logo text-2xl" href="/">
                Heroes
                <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
            </a>
            <div class="flex items-center justify-between flex-wrap  text-sm md:flex-1 md:ml-12 max-md:flex-col">
                <div class="flex items-center md:space-x-6 flex-wrap md:flex-nowrap md:pr-5 max-md:flex-col max-md:w-full ">
                    
                    <div class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Global Hero Stats</a>
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

                    <a class="  nav-item" href="/Global/Leaderboard">Leaderboards</a>

                    <div class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Tools</a>
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
                                @if (Auth::check() && app(\App\Services\AnalyticsService::class)->isAllowed(Auth::user()))
                                <a href="/Analytics" >Analytics - Pre Alpha</a>
                                @endif
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

                    <a class=" nav-item" href="/Esports">Community Esports</a>

                    <div class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Help</a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/issues/new?assignees=&labels=&projects=&template=bug_report.md" target="_blank" >Submit an Issue</a>
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/discussions/new?category=ideas" target="_blank" >Submit a Request</a>
                                 <a href="https://github.com/Heroes-Profile/heroesprofile/discussions" target="_blank" >Discussions</a>
                                 <a href="/Github/Change/Log">Change Log</a>
                                <a href="/FAQ">FAQ</a>
                                <a href="/Contact" >Contact Us</a>
                            </div>

             
                        </div>
                    </div>

                    

                    

                    <div class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Donate</a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                 <a href="https://www.patreon.com/c/heroesprofile" target="_blank" >Patreon</a>
                                 <a href="https://opencollective.com/heroes-profile" target="_blank" >Open Collective</a>
                            </div>

             
                        </div>
                    </div>

                    

                        @if(isset($mainSearchAccount))
                        <div class="relative group inline-block nav-item ">
                            <a class="cursor-pointer">
                            {{ $mainSearchAccount['battletag'] }} 
                            @if(isset($regions[$mainSearchAccount['region']]))
                                ({{ $regions[$mainSearchAccount['region']] }})
                            @else
                                (Region not found, log out and back in)
                            @endif

                            </a>
                            <div class="nav-dropdown  ">
                                <div class="nav-dropdown-inner-wrapper ">
                                    {{-- ... (mainSearchAccount dropdown items) --}}
                                    @if (Auth::check() && app(\App\Services\AnalyticsService::class)->isAllowed(Auth::user()))
                                    <div class="nav-flyout-wrapper">
                                        <a href="/Analytics" class="flex justify-between items-center bg-teal font-bold"><span><i class="fas fa-chart-line mr-2"></i>Analytics</span> <span class="nav-flyout-arrow ml-4">›</span></a>
                                        <div class="nav-flyout">
                                            <div class="nav-dropdown-inner-wrapper rounded-b-lg rounded-r-lg">
                                                <a href="/Analytics?tab=deaths">Deaths</a>
                                                <a href="/Analytics?tab=map">Map &amp; Positions</a>
                                                <a href="/Analytics?tab=timelines">Timelines &amp; Objectives</a>
                                                <a href="/Analytics?tab=activity">Activity</a>
                                                <a href="/Analytics?tab=sessions">Sessions</a>
                                                <a href="/Analytics?tab=coaching">Coaching</a>
                                                <a href="/Analytics?tab=stacks">Stacks</a>
                                                <a href="/Analytics?tab=games">Games</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}" >Profile</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/FriendFoe" >Friends and Foes</a>
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
                                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role" class="flex justify-between items-center">Roles <span class="nav-flyout-arrow ml-4">›</span></a>
                                        <div class="nav-flyout">
                                            <div class="nav-dropdown-inner-wrapper rounded-b-lg rounded-r-lg">
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
                <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="mt-auto md:hidden"></search-component>
                {{-- Distinct white or grey line show the seperation here  --}}
                <div class="flex gap-3 md:ml-auto max-md:order-1 max-md:mt-auto max-md:p-3">
                    <custom-button class="font-semibold" :href="'/Api'" :text="'API'" :alt="'API'" :color="'teal'"></custom-button>
                    <custom-button class="font-semibold" :href="'/Upload'" :text="'Replay Uploader'" :alt="'Replay Uploader'" :color="'teal'"></custom-button>
                    <custom-button class="font-semibold" :href="'https://www.patreon.com/heroesprofile'" :targetblank="true" :text="'Remove Ads / Patreon'" :alt="'Patreon'" :color="'red'"></custom-button>
                </div>


                <dark-mode-toggle class="md:ml-4 max-md:my-4" :darkmode="{{ json_encode(! empty($bladeGlobals['darkmode'])) }}" :authenticated="{{ json_encode($isAuthenticated) }}"></dark-mode-toggle>

                @if($isAuthenticated)
                    <div class="relative group inline-block md:ml-4 max-md:my-4 max-md:flex items-center">
                            <div class="flex items-center cursor-pointer">
                                <img 
                                class="card-img-top relative hover:opacity-75 w-10 h-10 rounded" 
                                src="/images/heroes/auto_select.jpg" 
                                alt="Settings">
                            </div>
                            <div class="md:absolute right-0 md:hidden md:group-hover:block z-50  ">
                                <div class="md:bg-blue  rounded-b-lg rounded-tl-lg text-sm drop-shadow max-md:flex">
                                  
                                <span class="block px-4 py-2 border-b border-darken bg-gray-dark">
                                {{ $mainSearchAccount['battletag_full'] }}

                                </span>

                                    <a href="/Profile/Settings" class="block px-4 py-2 border-b border-darken hover:bg-lighten">Settings</a>
                                    <a href="/Battlenet/Logout" class="block px-4 py-2 border-b border-darken hover:bg-lighten">Logout</a>
                                </div>
                            </div>
                        </div>





                @else
                    <custom-button class="md:ml-4 max-md:my-4 font-semibold" :href="'/Authenticate/Battlenet'" :text="'Login'" :alt="'Login'" :color="'teal'"></custom-button>
                @endif
             
            </div>
        </div>
    </nav>
    
</div>
<nav class="relative flex justify-end items-center md:gap-4 max-w-[1920px] mx-auto md:px-8 md:py-4 alt-acct-nav max-md:flex-wrap">
  @foreach($altSearchAccounts as $index => $account)
    @if($account)
      <div class="relative group inline-block text-sm">
        <a data-battletag="{{ $account['battletag'] }}" class="mobile-secondary-nav-open cursor-pointer md:block md:bg-teal md:hover:bg-lteal md:rounded md:px-5 md:py-2 transition-colors">{{ $account['battletag'] }}@if(isset($regions[$account['region']]))<span class="text-[0.7em] ml-2">{{ $regions[$account['region']] }}</span>@endif</a>
        <div data-battletag="{{ $account['battletag'] }}" class="nav-dropdown absolute  hidden z-50 md:pt-3 absolute md:right-0 md:min-w-[200px] max-md:top-0 max-md:fixed max-md:w-full nav-dropdown-secondary-nav max-md:bg-gray-dark max-md:h-full">
            <div class="nav-dropdown-inner-wrapper rounded-none">
              <div class="mobile-secondary-nav-name flex justify-between items-center md:hidden p-3 pl-4 bg-teal">{{ $account['battletag'] }}@if(isset($regions[$account['region']])) ({{ $regions[$account['region']] }})@endif <button class="close-secondary-nav bg-blue rounded-lg px-3 py-1 min-w-[44px] min-h-[40px] text-lg" aria-label="Close menu">x</button></div>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}" >Profile</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/FriendFoe" >Friends and Foes</a>
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
                <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role" class="flex justify-between items-center">Roles <span class="nav-flyout-arrow ml-4">›</span></a>
                <div class="nav-flyout">
                  <div class="nav-dropdown-inner-wrapper rounded-b-lg rounded-r-lg">
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
              <a href="#" class="remove-account text-right bg-gray-dark hover:bg-gray-md">
                <remove-battletag-nav     
                  :index="{{ $index }}"
                >
                </remove-battletag-nav>
              </a>
            </div>
        </div>
      </div>
    @endif
  @endforeach
  <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="max-md:hidden md:w-96"></search-component>
</nav>
<mobile-nav-hack></mobile-nav-hack>