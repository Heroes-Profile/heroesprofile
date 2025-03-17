<div class="max-md:bg-gray-dark max-md:fixed max-md:z-40 max-md:w-full">
    <div class="flex p-2 px-4 justify-between">
<a class=" flex items-center font-logo text-2xl md:hidden" href="/">
                Heroes
                <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
        </a>
        <button  id="mobile-toggle" class="md:hidden bg-blue rounded-lg px-2 ">=</button>
         
        
    </div>
</div>

<div id="main-menu" class="  main-navigation-wrapper"> <!-- max-md:hidden  - This needs to hide/show on click of the button but only on mobile - either detect mobile with vue, or have a class that is added/taken away that only applies to mobile -->
    <nav class="bg-gray-dark text-white z-40 relative md:px-4 text-sm max-md:pt-2">
      <button  id="mobile-nav-close" class="hidden bg-blue rounded-lg px-2 py-1 ml-auto  mr-2">x</button>
       
        <div class="flex items-center justify-between flex-wrap  max-md:flex-col" >
             
            <a class=" flex items-center font-logo text-2xl" href="/">
                Heroes
                <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
                Profile
            </a>
            <div class="flex items-center justify-between flex-wrap  text-sm md:ml-auto max-md:flex-col">
                <div class="flex items-center md:space-x-5 md:justify-end flex-wrap md:flex-nowrap md:pr-5 max-md:flex-col max-md:w-full ">
                    
                    <div class="relative group inline-block nav-item">
                        <a class=" cursor-pointer">Global Hero Stats</a>
                        <div class="nav-dropdown ">
                            <div class="nav-dropdown-inner-wrapper ">
                                {{-- ... (Global Hero Stats dropdown items) --}}
                                <a href="/Global/Hero" >Hero Stats</a>
                                <a href="/Global/Talents" >Talent Stats</a>
                                <a href="/Global/Hero/Maps" >Map Stats</a>
                                <a href="/Global/Matchups" >Matchup Stats</a>
                                <a href="/Global/Matchups/Talents" >Matchup Talent Stats</a>
                                <a href="/Global/Role/Compositions" >Role Compositional Stats</a>
                                <a href="/Global/Hero/Compositions" >Hero Compositional Stats</a>
                                <a href="/Global/Draft" >Draft Stats</a>
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
                                <!--<a href="/Compare" >Compare</a>-->
                                <a href="https://drafter.heroesprofile.com/Drafter" target="_blank" >Drafter</a>
                                <a href="https://api.heroesprofile.com/upload" target="_blank" >Replay Uploader</a>
                                {{--<a href="/" class="block px-4 py-2 border-b border-darken hover:bg-lighten cursor-not-allowed pointer-events-none">Activity Graphs</a>--}}
                                <a href="https://autobattler.setup.heroesprofile.com/" target="_blank" >Auto Battler</a>
                                <a href="/Match/Prediction/Game" >Match Prediction Game</a>
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
                                <a href="/Contact" >Contact Us</a>
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
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}" >Profile</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/FriendFoe" >Friends and Foes</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Hero" >Heroes</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role" >Roles</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Map" >Maps</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Matchups" >Matchups</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Talents" >Talents</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/MMR" >MMR Breakdown</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Match/History" >Match History</a>
                                    <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Match/Latest" >Latest Match</a>
                                </div>
                            </div>
                        </div>
                        @endif
                    
                </div>
                <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="mt-auto md:hidden"></search-component>
                {{-- Distinct white or grey line show the seperation here  --}}
                <div class="flex space-x-5 bg-lighten p-3 mx-2 max-md:order-1 max-md:mt-auto">
                    <custom-button :href="'https://api.heroesprofile.com/Api'" :targetblank="true" :text="'API'" :alt="'API'"  :size="'small'" :color="'teal'"></custom-button>
                    <custom-button :href="'https://api.heroesprofile.com/upload'" :targetblank="true" :text="'Replay Uploader'" :alt="'Replay Uploader'"  :size="'small'" :color="'blue'"></custom-button>
                    <custom-button :href="'https://www.patreon.com/heroesprofile'" :targetblank="true" :text="'Remove Ads / Patreon'" :alt="'Patreon'"  :size="'small'" :color="'red'"></custom-button>
                </div>


                @if($isAuthenticated)
                    <div class="relative group inline-block  md:ml-5 max-md:my-4 max-md:flex items-center">
                            <div class="flex items-center cursor-pointer md:mr-5">
                                <img 
                                class="card-img-top relative hover:opacity-75 w-12 h-12 rounded-full" 
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
                    <custom-button class="md:ml-4 max-md:my-4" :href="'/Authenticate/Battlenet'" :text="'Login'" :alt="'Login'" :size="'small'"></custom-button>
                @endif
             
            </div>
        </div>
    </nav>
    
</div>
<nav class="flex justify-end md:mr-8 alt-acct-nav max-md:flex-wrap  ">
  @foreach($altSearchAccounts as $index => $account)
    @if($account)
      <div class="relative group inline-block  md:p-4 md:mx-4 text-sm  ">
        <a data-battletag="{{ $account['battletag'] }}" class="mobile-secondary-nav-open cursor-pointer">{{ $account['battletag'] }} ({{ $regions[$account['region']] }})</a>
        <div data-battletag="{{ $account['battletag'] }}" class="nav-dropdown absolute  hidden z-50 md:pt-3 absolute md:right-0 md:min-w-[200px] max-md:top-0 max-md:fixed max-md:w-full nav-dropdown-secondary-nav max-md:bg-gray-dark max-md:h-full">
            <div class="nav-dropdown-inner-wrapper rounded-none">
              <div  class="mobile-secondary-nav-name flex justify-between md:hidden max-md:text-xs md:p-3 bg-teal">{{ $account['battletag'] }} ({{ $regions[$account['region']] }}) <button class="close-secondary-nav">x</button></div>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}" >Profile</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/FriendFoe" >Friends and Foes</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Hero" >Heroes</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role">Roles</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Map" >Maps</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Matchups">Matchups</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Talents" >Talents</a>
              <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/MMR">MMR Breakdown</a>
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
  <search-component :type="'alt'" :buttonText="'Find Player'" :labelText="'Enter a battletag'" class="max-md:hidden mt-auto"></search-component>
</nav>
<mobile-nav-hack></mobile-nav-hack>