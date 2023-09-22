<nav class="bg-gray-dark text-white p-2 z-50 relative">
    <div class="flex items-center justify-between flex-wrap md:flex-nowrap">
        <a class="text-blue-600 hover:text-blue-800 flex items-center font-logo text-2xl" href="/">
            Heroes
            <img class="w-10 mx-2" src="/images/logo/heroesprofilelogo.png" alt="Heroes Profile Logo" />
            Profile
        </a>
        <div class="flex items-center space-x-5 justify-end flex-wrap md:flex-nowrap">
            
            <div class="relative group inline-block space-x-5">
                <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Global Hero Stats</a>
                <div class="absolute left-0 hidden group-hover:block z-50 pt-5">
                    <div class="bg-blue border border-gray-300 rounded-md">
                        <!-- ... (Global Hero Stats dropdown items) -->
                        <a href="/Global/Hero" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Hero Stats</a>
                        <a href="/Global/Talents" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Talent Stats</a>
                        <a href="/Global/Hero/Maps" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Map Stats</a>
                        <a href="/Global/Matchups" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchup Stats</a>
                        <a href="/Global/Matchups/Talents" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchup Talent Stats</a>
                        <a href="/Global/Compositions" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Compositional Stats</a>
                        <a href="/Global/Draft/General" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Draft Stats</a>
                        <a href="/Global/Party" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Party Stats</a>
                        <a href="/Global/Extra" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Extra Stats</a>
                    </div>
                </div>
            </div>

            <a class="text-blue-600 hover:text-blue-800" href="/Global/Leaderboard">Leaderboards</a>

            <div class="relative group inline-block">
                <a class="text-blue-600 hover:text-blue-800 cursor-pointer">Tools</a>
                <div class="absolute left-0 hidden group-hover:block z-50 pt-5">
                    <div class="bg-blue border border-gray-300 rounded-md">
                        <!-- ... (Tools dropdown items) -->
                        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Talent Builder</a>
                        <a href="/Compare" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Compare</a>
                        <a href="https://drafter.heroesprofile.com/Drafter" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Drafter</a>
                        <a href="https://dev.heroesprofile.com/" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Game Data</a>
                        <a href="https://api.heroesprofile.com/upload" target="_blank" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Replay Uploader</a>
                        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Activity Graphs</a>
                        <a href="/" class="block px-4 py-2 text-gray-400 hover:bg-gray-200 cursor-not-allowed pointer-events-none">Auto Battler</a>
                        <a href="/" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Find a Player</a>


                    </div>
                </div>
            </div>

            <div class="relative group inline-block">
                @if(isset($mainSearchAccount))
                    <a class="text-blue-600 hover:text-blue-800 cursor-pointer">
                    {{ $mainSearchAccount['battletag'] }} ({{ $regions[$mainSearchAccount['region']] }})
                    </a>
                    <div class="absolute left-0 hidden group-hover:block z-50 pt-5 bg-blue border border-gray-300 rounded-md">
                        <!-- ... (mainSearchAccount dropdown items) -->
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Profile</a>
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/FriendFoe" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Friends and Foes</a>
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Hero" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Heroes</a>
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Role" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Roles</a>
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Map" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Maps</a>
                        <a href="/Player/{{ $mainSearchAccount['battletag'] }}/{{ $mainSearchAccount['blizz_id'] }}/{{ $mainSearchAccount['region'] }}/Matchups" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchups</a>
                    </div>
                @endif
            </div>

            <!-- Distinct white or grey line show the seperation here  -->
            <div class="flex space-x-5">
                <custom-button :href="'https://api.heroesprofile.com/Api'" :targetblank="true" :text="'API'" :alt="'API'"  :size="'small'" :color="'teal'"></custom-button>
                <custom-button :href="'https://api.heroesprofile.com/upload'" :targetblank="true" :text="'Replay Uploader'" :alt="'Replay Uploader'"  :size="'small'" :color="'blue'"></custom-button>
                <custom-button :href="'https://www.patreon.com/heroesprofile'" :targetblank="true" :text="'Patreon'" :alt="'Patreon'"  :size="'small'" :color="'red'"></custom-button>
            </div>


            @if($isAuthenticated)
                <div class="relative group inline-block">
                    <div class="flex items-center cursor-pointer mr-5">
                        <img 
                        class="card-img-top relative hover:opacity-75 w-12 h-12 rounded-full" 
                        src="/images/heroes/auto_select.jpg" 
                        alt="Settings">
                    </div>
                    <div class="absolute right-0 mt-2 hidden group-hover:block z-50 bg-blue border border-gray-300 rounded-md transition duration-150">
                        <a href="/Profile/Settings" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Settings</a>
                        <a href="/Battlenet/Logout" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Logout</a>
                    </div>
                </div>
            @else
                <custom-button :href="'/Authenticate/Battlenet'" :text="'Login'" :alt="'Login'" :size="'small'"></custom-button>
            @endif
        </div>
    </div>
</nav>
<nav class="flex justify-end">
    @foreach($altSearchAccounts as $index => $account)
        @if($account)
            <div class="relative group inline-block ml-4 pr-2">
                <a class="text-blue-600 hover:text-blue-800 cursor-pointer">{{ $account['battletag'] }} ({{ $regions[$account['region']] }})</a>
                <div class="absolute right-0 mt-2 hidden group-hover:block z-50 bg-blue border border-gray-300 rounded-md">
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Profile</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/FriendFoe" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Friends and Foes</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Hero" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Heroes</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Role" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Roles</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Map" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Maps</a>
                    <a href="/Player/{{ $account['battletag'] }}/{{ $account['blizz_id'] }}/{{ $account['region'] }}/Matchups" class="block px-4 py-2 text-blue-600 hover:bg-gray-200">Matchups</a>
                </div>
            </div>
        @endif
    @endforeach
</nav>